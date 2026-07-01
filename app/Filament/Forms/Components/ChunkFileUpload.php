<?php

namespace App\Filament\Forms\Components;

use Closure;
use DragonCode\Support\Helpers\Ables\Arrayable;
use Filament\Forms\Components\Field;

class ChunkFileUpload extends Field
{
    protected string $view = 'filament.forms.components.chunk-file-upload';

    protected string | null $uploadUrl = null;

    protected array | Arrayable | Closure | null $acceptedFileTypes = null;

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

    // ✅ Setter — permite encadenar: ChunkFileUpload::make('file')->acceptedFileTypes([...])
    public function acceptedFileTypes(array | Arrayable | Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    // ✅ Getter — usado en el blade y en la validación
    public function getAcceptedFileTypes(): ?array
    {
        $types = $this->evaluate($this->acceptedFileTypes);

        if ($types instanceof Arrayable) {
            $types = $types->toArray();
        }

        return $types;
    }
}
