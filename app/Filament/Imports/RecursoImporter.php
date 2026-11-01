<?php

namespace App\Filament\Imports;

use App\Models\Coleccion;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\ValidationException;

class RecursoImporter extends Importer
{
    protected static ?string $model = Recursos::class;

    protected static ?string $userModel = \App\Models\Admin::class;

    /** acervo_id => esquema normalizado, o null si el acervo no existe */
    private static array $esquemas = [];

    /** coleccion_id => bool */
    private static array $colecciones = [];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('acervo_id')
                ->requiredMapping()
                ->numeric(),

            ImportColumn::make('coleccion_id')
                ->requiredMapping()
                ->numeric(),

            ImportColumn::make('tipo_media'),
        ];
    }

    public function resolveRecord(): Recursos
    {
        return new Recursos();
    }

    public function fillRecord(): void
    {
        $acervoId = (int) $this->data['acervo_id'];
        $coleccionId = (int) $this->data['coleccion_id'];

        $esquema = self::esquema($acervoId)
            ?? throw ValidationException::withMessages([
                'acervo_id' => 'Tipo de acervo no encontrado.',
            ]);

        if (! self::coleccionExiste($coleccionId)) {
            throw ValidationException::withMessages([
                'coleccion_id' => 'Colección no encontrada.',
            ]);
        }

        // Encabezados del CSV sin espacios sobrantes
        $raw = [];
        foreach ($this->originalData as $clave => $valor) {
            $raw[trim((string) $clave)] = $valor;
        }

        $metadata = [];

        foreach ($esquema as $campo) {
            $valor = $raw[$campo['variable']] ?? null;

            if (is_string($valor)) {
                $valor = trim($valor);
            }

            if ($valor === null || $valor === '') {
                if ($campo['required']) {
                    throw ValidationException::withMessages([
                        $campo['variable'] => "El campo dinámico '{$campo['label']}' es obligatorio para esta colección.",
                    ]);
                }
                continue;
            }

            $metadata[$campo['variable']] = ($campo['type'] === 'number' && is_numeric($valor))
                ? $valor + 0
                : $valor;
        }

        $this->record->fill([
            'acervo_id' => $acervoId,
            'coleccion_id' => $coleccionId,
            'tipo_media' => $this->data['tipo_media'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * El sync a Meilisearch ocurre al guardar, no al hacer fill().
     * Se desactiva aquí; la indexación se hace en bloque al terminar.
     */
    public function saveRecord(): void
    {
        Recursos::withoutSyncingToSearch(fn () => $this->record->save());
    }

    private static function esquema(int $acervoId): ?array
    {
        if (! array_key_exists($acervoId, self::$esquemas)) {
            $acervo = TipoAcervo::find($acervoId, ['id', 'esquema']);

            if (! $acervo) {
                // También se cachea el "no existe" para no repetir la consulta
                return self::$esquemas[$acervoId] = null;
            }

            $esquema = $acervo->esquema ?? [];

            if (is_string($esquema)) {
                $esquema = json_decode($esquema, true) ?? [];
            }

            self::$esquemas[$acervoId] = array_map(fn (array $campo) => [
                'variable' => $campo['variable'],
                'label' => $campo['label'] ?? $campo['variable'],
                'required' => (bool) ($campo['is_required'] ?? false),
                'type' => $campo['type'] ?? 'text',
            ], $esquema);
        }

        return self::$esquemas[$acervoId];
    }

    private static function coleccionExiste(int $coleccionId): bool
    {
        return self::$colecciones[$coleccionId]
            ??= Coleccion::whereKey($coleccionId)->exists();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de Recursos terminó correctamente.';

        if ($import->successful_rows) {
            $body .= " {$import->successful_rows} filas importadas.";
        }

        if ($failed = $import->getFailedRowsCount()) {
            $body .= " {$failed} filas fallaron.";
        }

        return $body;
    }
}