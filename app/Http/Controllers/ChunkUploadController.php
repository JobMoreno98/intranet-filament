<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChunkUploadController extends Controller
{
    /**
     * GET - Verifica si un chunk ya fue subido (para testChunks: true)
     */
    public function checkChunk(Request $request)
    {
        $chunkIndex  = $request->input('resumableChunkNumber');
        $identifier  = $request->input('resumableIdentifier');
        $filename    = $request->input('resumableFilename');
        $tempFolder  = 'chunks/' . $identifier;
        $chunkName   = $chunkIndex . '_' . $filename;

        if (Storage::disk('local')->exists($tempFolder . '/' . $chunkName)) {
            return response()->json(['status' => 'exists'], 200); // Resumable lo salta
        }

        return response()->json(['status' => 'not_found'], 204); // Resumable lo sube
    }

    /**
     * POST - Recibe y guarda cada chunk
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'                   => 'required|file',
            'resumableChunkNumber'   => 'required|integer|min:1',
            'resumableTotalChunks'   => 'required|integer|min:1',
            'resumableFilename'      => 'required|string',
            'resumableIdentifier'    => 'required|string',
            'resumableTotalSize'     => 'required|integer',
            'resumableType'          => 'required|string', 
        ]);

        $file        = $request->file('file');
        $chunkIndex  = (int) $request->input('resumableChunkNumber');
        $totalChunks = (int) $request->input('resumableTotalChunks');
        $filename    = $request->input('resumableFilename');
        $identifier  = $request->input('resumableIdentifier');
        $tempFolder  = 'chunks/' . $identifier;
        $chunkName   = $chunkIndex . '_' . $filename;

        // Evita reprocesar un chunk ya guardado
        if (!Storage::disk('local')->exists($tempFolder . '/' . $chunkName)) {
            Storage::disk('local')->putFileAs($tempFolder, $file, $chunkName);
        }

        // Verifica si TODOS los chunks están presentes antes de ensamblar
        if ($this->allChunksUploaded($tempFolder, $filename, $totalChunks)) {
            return $this->assembleFile($tempFolder, $filename, $totalChunks, $identifier);
        }

        return response()->json(['status' => 'chunk_uploaded']);
    }

    /**
     * Verifica que todos los chunks existen en disco
     */
    private function allChunksUploaded(string $tempFolder, string $filename, int $totalChunks): bool
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!Storage::disk('local')->exists($tempFolder . '/' . $i . '_' . $filename)) {
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
        $finalPath        = 'uploads/' . uniqid() . '_' . $filename;
        $localTempFolder  = Storage::disk('local')->path($tempFolder);

        Storage::disk('public')->makeDirectory('uploads');
        $absoluteFinalPath = Storage::disk('public')->path($finalPath);

        $destination = fopen($absoluteFinalPath, 'ab');

        if (!$destination) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo crear el archivo final'], 500);
        }

        try {
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkPath = $localTempFolder . '/' . $i . '_' . $filename;

                if (!file_exists($chunkPath)) {
                    throw new \RuntimeException("Chunk $i no encontrado");
                }

                //  Stream en lugar de file_get_contents — no carga todo en RAM
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

        //  Limpieza segura del directorio temporal
        $this->cleanupTempFolder($localTempFolder);

        return response()->json([
            'path'   => $finalPath,
            'status' => 'completed',
        ]);
    }

    /**
     * Elimina el directorio temporal de forma segura
     */
    private function cleanupTempFolder(string $folderPath): void
    {
        if (!is_dir($folderPath)) {
            return;
        }

        $files = glob($folderPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($folderPath);
    }
}
