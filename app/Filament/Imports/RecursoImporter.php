<?php

namespace App\Filament\Imports;

use App\Models\Admin;
use App\Models\Coleccion;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\Authenticatable;

class RecursoImporter extends Importer
{
    protected static ?string $model = Recursos::class;

    protected static ?string $userModel = \App\Models\Admin::class;

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

    /**
     * Campos "base" del recurso, que NO deben terminar dentro de metadata.
     */
    private const CAMPOS_BASE = [
        'acervo_id',
        'coleccion_id',
        'tipo_media',
    ];

    public function fillRecord(): void
    {
        // 1. $data tiene SOLO los campos mapeados en getColumns()
        $data = $this->data;

        // 2. $rawData tiene TODA la fila cruda del Excel (incluyendo campos dinámicos).
        // Normalizamos las claves (trim) para evitar fallos por espacios accidentales
        // en los encabezados del Excel.
        $rawData = collect($this->originalData)
            ->mapWithKeys(fn($value, $key) => [trim((string) $key) => $value])
            ->all();

        // El esquema de campos dinámicos vive en TipoAcervo, así que hay que
        // buscarlo por acervo_id (NO por coleccion_id, que es un FK distinto).
        $acervoIdLimpio = (int) $data['acervo_id'];
        $acervo = TipoAcervo::find($acervoIdLimpio);

        if (! $acervo) {
            throw ValidationException::withMessages([
                'acervo_id' => 'Tipo de acervo no encontrado.',
            ]);
        }

        // La colección también debe existir, ya que es el FK que se guarda en el recurso.
        $coleccionIdLimpio = (int) $data['coleccion_id'];
        if (! Coleccion::find($coleccionIdLimpio)) {
            throw ValidationException::withMessages([
                'coleccion_id' => 'Colección no encontrada.',
            ]);
        }

        // El esquema debe llegar como array (cast 'array' en el modelo TipoAcervo).
        // Si por alguna razón llega como string JSON, lo decodificamos para no tronar.
        $esquema = $acervo->esquema ?? [];
        if (is_string($esquema)) {
            $esquema = json_decode($esquema, true) ?? [];
        }

        // 3. VALIDACIÓN ESTRICTA + 4. CONSTRUCCIÓN DE METADATA, usando el esquema
        // como única fuente de verdad de qué campos dinámicos existen para esta colección.
        $metadata = [];

        foreach ($esquema as $campo) {
            $variable = $campo['variable']; // Nombre exacto de la cabecera en Excel
            $valorCrudo = $rawData[$variable] ?? null;
            $estaVacio = $this->valorEstaVacio($valorCrudo);

            if (($campo['is_required'] ?? false) && $estaVacio) {
                throw ValidationException::withMessages([
                    // Este error saldrá exactamente en el CSV de "Filas fallidas"
                    $variable => "El campo dinámico '{$campo['label']}' es obligatorio para esta colección.",
                ]);
            }

            if (! $estaVacio) {
                $metadata[$variable] = $this->castearValor($valorCrudo, $campo['type'] ?? 'text');
            }
        }

        // Cualquier columna del Excel que no sea campo base NI campo del esquema
        // se ignora silenciosamente (evita "basura" en metadata por errores de captura).
        // Si prefieres conservarla igual, descomenta el bloque de abajo:
        //
        // $variablesEsquema = array_column($esquema, 'variable');
        // foreach ($rawData as $key => $value) {
        //     if (! in_array($key, self::CAMPOS_BASE, true) && ! in_array($key, $variablesEsquema, true)) {
        //         $metadata[$key] = $value;
        //     }
        // }

        // Llenar modelo
        $this->record->fill([
            'acervo_id' => $data['acervo_id'],
            'coleccion_id' => $data['coleccion_id'],
            'tipo_media' => $data['tipo_media'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * A diferencia de empty(), no trata "0" ni 0 como vacío.
     * Solo null, string vacía o string de puros espacios cuenta como vacío.
     */
    private function valorEstaVacio(mixed $valor): bool
    {
        if (is_null($valor)) {
            return true;
        }

        if (is_string($valor)) {
            return trim($valor) === '';
        }

        return false;
    }

    /**
     * Castea el valor crudo del Excel según el tipo declarado en el esquema.
     */
    private function castearValor(mixed $valor, string $tipo): mixed
    {
        return match ($tipo) {
            'number' => is_numeric($valor) ? $valor + 0 : $valor, // int o float según corresponda
            'text', 'textarea' => is_string($valor) ? trim($valor) : $valor,
            default => $valor,
        };
    }

    /**
     * Requerido por la clase abstracta de Filament.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de Recuros terminó correctamente.';

        $successfulRows = $import->successful_rows;

        if ($successfulRows) {
            $body .= " {$successfulRows} filas importadas.";
        }
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} filas fallaron.";
        }

        return $body;
    }
}