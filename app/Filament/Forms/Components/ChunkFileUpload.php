<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\Support\Arrayable;

class ChunkFileUpload extends Field
{
    protected string $view = 'filament.forms.components.chunk-file-upload';

    protected string | null $uploadUrl = null;

    protected array | Arrayable | Closure | null $acceptedFileTypes = null;

    protected bool $isMultiple = false;

    protected int $chunkSizeMb = 4;

    protected int $parallelUploads = 1;

    public function uploadUrl(string $url): static
    {
        $this->uploadUrl = $url;
        return $this;
    }

    public function getUploadUrl(): string
    {
        return $this->uploadUrl ?? route("api.chunks.upload");
    }

    public function isFieldDisabled(): bool
    {
        return $this->isDisabled();
    }

    /**
     * Varios archivos: el estado del campo pasa a ser un arreglo de rutas.
     * Sin llamar a este método el campo sigue funcionando como antes (una sola ruta en cadena).
     */
    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        if ($condition) {
            $this->default([]);
        }

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * Tamaño de trozo en MB. En modo multiple los trozos NO son exactos: un archivo
     * de menos de 2x este tamaño viaja en un solo trozo. Verifica que upload_max_filesize,
     * post_max_size y client_max_body_size (nginx) sean >= 2x este valor.
     */
    public function chunkSize(int $megabytes): static
    {
        $this->chunkSizeMb = max(1, $megabytes);
        return $this;
    }

    public function getChunkSizeMb(): int
    {
        return $this->chunkSizeMb;
    }

    /** Cuántos trozos viajan a la vez (entre archivos distintos de la cola). */
    public function parallelUploads(int $n): static
    {
        $this->parallelUploads = max(1, $n);
        return $this;
    }

    public function getParallelUploads(): int
    {
        return $this->parallelUploads;
    }

    // Setter — permite encadenar: ChunkFileUpload::make('file')->acceptedFileTypes([...])
    public function acceptedFileTypes(array | Arrayable | Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    // Getter — usado en el blade y en la validación
    public function getAcceptedFileTypes(): ?array
    {
        $types = $this->evaluate($this->acceptedFileTypes);

        if ($types instanceof Arrayable) {
            $types = $types->toArray();
        }

        return $types;
    }
}