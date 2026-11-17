<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ChunkUploadController extends Controller
{
    // Debe empezar con letra o número: evita "." y ".." (path traversal) en la carpeta temporal
    private const PATRON_ID = '/^[A-Za-z0-9][A-Za-z0-9._-]{0,199}$/';

    private const EXTENSIONES_BLOQUEADAS = [
        'php', 'phtml', 'phar', 'pht', 'phps', 'php3', 'php4', 'php5', 'php7', 'php8',
        'sh', 'bat', 'cmd', 'exe', 'cgi', 'pl', 'htaccess',
    ];

    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            // Consulta de resultado: el cliente la usa si la última respuesta de un archivo
            // de varios trozos no trajo la ruta final.
            if ($request->filled('result')) {
                return $this->resultado($request);
            }

            return $this->checkChunk($request);
        }

        return $this->upload($request);
    }

    /**
     * GET - Verifica si un chunk ya fue subido (para testChunks: true)
     */
    public function checkChunk(Request $request)
    {
        $identifier = (string) $request->input('resumableIdentifier');

        if (! preg_match(self::PATRON_ID, $identifier)) {
            return response()->json(['status' => 'error', 'message' => 'Identificador inválido'], 400);
        }

        $chunkIndex = (int) $request->input('resumableChunkNumber');
        $filename   = $this->limpiarNombre((string) $request->input('resumableFilename'));
        $tempFolder = 'chunks/' . $identifier;
        $chunkName  = $chunkIndex . '_' . $filename;

        if (Storage::disk('local')->exists($tempFolder . '/' . $chunkName)) {
            return response()->json(['status' => 'exists'], 200); // Resumable lo salta
        }

        return response()->json(['status' => 'not_found'], 404); // Resumable lo sube
    }

    /**
     * GET ?result=ID - Devuelve la ruta final si el archivo ya fue ensamblado
     */
    private function resultado(Request $request)
    {
        $identifier = (string) $request->input('result');

        if (! preg_match(self::PATRON_ID, $identifier)) {
            return response()->json(['status' => 'error'], 400);
        }

        $path = Cache::get('chunk-done:' . $identifier);

        return $path
            ? response()->json(['path' => $path, 'status' => 'completed'])
            : response()->json(['status' => 'pending'], 404);
    }

    /**
     * POST - Recibe y guarda cada chunk
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'                 => 'required|file',
            'resumableChunkNumber' => 'required|integer|min:1',
            'resumableTotalChunks' => 'required|integer|min:1',
            'resumableFilename'    => 'required|string',
            'resumableIdentifier'  => 'required|string',
            'resumableTotalSize'   => 'required|integer',
            // Archivos sin tipo MIME llegan como cadena vacía (-> null) y fallaban con "required"
            'resumableType'        => 'nullable|string',
        ]);

        $file        = $request->file('file');
        $chunkIndex  = (int) $request->input('resumableChunkNumber');
        $totalChunks = (int) $request->input('resumableTotalChunks');
        $identifier  = (string) $request->input('resumableIdentifier');

        if (! preg_match(self::PATRON_ID, $identifier) || $chunkIndex > $totalChunks) {
            return response()->json(['status' => 'error', 'message' => 'Parámetros inválidos'], 400);
        }

        // Nunca confiar en el nombre que manda el navegador: se usa para rutas en disco
        $filename  = $this->limpiarNombre((string) $request->input('resumableFilename'));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, self::EXTENSIONES_BLOQUEADAS, true)) {
            return response()->json(['status' => 'error', 'message' => 'Tipo de archivo no permitido'], 415);
        }

        $tempFolder = 'chunks/' . $identifier;
        $chunkName  = $chunkIndex . '_' . $filename;

        // Evita reprocesar un chunk ya guardado
        if (! Storage::disk('local')->exists($tempFolder . '/' . $chunkName)) {
            Storage::disk('local')->putFileAs($tempFolder, $file, $chunkName);
        }

        // Revisión barata y sin lock: la mayoría de los chunks no son el último
        if (! $this->allChunksUploaded($tempFolder, $filename, $totalChunks)) {
            return response()->json(['status' => 'chunk_uploaded']);
        }

        // Con varios trozos en paralelo, dos peticiones pueden ver "todos presentes" a la vez.
        // El lock garantiza que solo una ensamble; la otra devuelve la ruta ya calculada.
        try {
            return Cache::lock('chunk-assemble:' . $identifier, 120)->block(15, function () use ($tempFolder, $filename, $totalChunks, $identifier) {
                $hecho = Cache::get('chunk-done:' . $identifier);

                if ($hecho) {
                    return response()->json(['path' => $hecho, 'status' => 'completed']);
                }

                if (! $this->allChunksUploaded($tempFolder, $filename, $totalChunks)) {
                    return response()->json(['status' => 'chunk_uploaded']);
                }

                return $this->assembleFile($tempFolder, $filename, $totalChunks, $identifier);
            });
        } catch (LockTimeoutException $e) {
            // 503 no está en permanentErrors: Resumable reintenta el trozo
            return response()->json(['status' => 'busy'], 503);
        }
    }

    /**
     * Verifica que todos los chunks existen en disco
     */
    private function allChunksUploaded(string $tempFolder, string $filename, int $totalChunks): bool
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (! Storage::disk('local')->exists($tempFolder . '/' . $i . '_' . $filename)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensambla los chunks en el archivo final usando streams (sin cargar todo en RAM)
     */
    private function assembleFile(string $tempFolder, string $filename, int $totalChunks, string $identifier)
    {
        $finalPath       = 'uploads/' . uniqid() . '_' . $filename;
        $localTempFolder = Storage::disk('local')->path($tempFolder);

        Storage::disk('private')->makeDirectory('uploads');
        $absoluteFinalPath = Storage::disk('private')->path($finalPath);

        $destination = fopen($absoluteFinalPath, 'ab');

        if (! $destination) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo crear el archivo final'], 500);
        }

        try {
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkPath = $localTempFolder . '/' . $i . '_' . $filename;

                if (! file_exists($chunkPath)) {
                    throw new \RuntimeException("Chunk $i no encontrado");
                }

                $source = fopen($chunkPath, 'rb');
                stream_copy_to_stream($source, $destination);
                fclose($source);

                unlink($chunkPath); // Libera espacio inmediatamente
            }
        } catch (\Exception $e) {
            fclose($destination);

            // Limpia el archivo parcial si algo falló
            if (file_exists($absoluteFinalPath)) {
                unlink($absoluteFinalPath);
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        fclose($destination);

        $this->cleanupTempFolder($localTempFolder);

        // Guardamos el resultado para peticiones concurrentes y para la consulta ?result=
        Cache::put('chunk-done:' . $identifier, $finalPath, now()->addMinutes(30));

        return response()->json([
            'path'   => $finalPath,
            'status' => 'completed',
        ]);
    }

    /**
     * Deja solo el nombre del archivo (sin rutas) y caracteres seguros.
     * Es determinista: todos los trozos del mismo archivo producen el mismo resultado.
     */
    private function limpiarNombre(string $nombre): string
    {
        $nombre = basename(str_replace('\\', '/', $nombre));
        $nombre = preg_replace('/[^\p{L}\p{N}._ -]/u', '_', $nombre) ?? '';
        $nombre = ltrim($nombre, '.');
        $nombre = mb_substr($nombre, 0, 150);

        return $nombre !== '' ? $nombre : 'archivo';
    }

    /**
     * Elimina el directorio temporal de forma segura
     */
    private function cleanupTempFolder(string $folderPath): void
    {
        if (! is_dir($folderPath)) {
            return;
        }

        foreach (glob($folderPath . '/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($folderPath);
    }
}