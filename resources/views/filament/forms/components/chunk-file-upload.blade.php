<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    @php
        $isDisabled = $isFieldDisabled();
        $acceptedTypes = $getAcceptedFileTypes() ?? [];
    @endphp

    {{-- MODO MULTIARCHIVO: el estado es un ARREGLO de rutas (antes era una sola cadena) --}}
    <div wire:ignore x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        uploading: false,
        total: 0,
        done: 0,
        failed: [],
        resumable: null,
        isDisabled: {{ $isDisabled ? 'true' : 'false' }},
        multiple: {{ $isMultiple() ? 'true' : 'false' }},
    
        // Siempre trabajamos con un arreglo, aunque el estado viejo sea una cadena o null
        files() {
            if (Array.isArray(this.state)) return this.state;
            return this.state ? [this.state] : [];
        },
    
        nameOf(path) {
            return String(path).split('/').pop().replace(/^[0-9a-f]{13}_(.+)$/i, '$1');
        },
    
        get percent() {
            if (!this.total) return 0;
            return Math.floor(((this.done + this.failed.length) / this.total) * 100);
        },
    
        getCsrfToken() {
            const meta = document.querySelector('meta[name=csrf-token]');
            return meta ? meta.getAttribute('content') : '';
        },
    
        setupResumable() {
            if (this.isDisabled || typeof Resumable === 'undefined') return;
    
            const self = this;
            const target = this.$el.dataset.uploadUrl;
            const accepted = JSON.parse(this.$el.dataset.acceptedTypes);
            const chunkMb = parseInt(this.$el.dataset.chunkMb) || 4;
            const parallel = parseInt(this.$el.dataset.parallel) || 1;
    
            this.resumable = new Resumable({
                target,
                headers: { 'Accept': 'application/json' },
                query() {
                    return {
                        _token: self.getCsrfToken(),
                        accepted_types: accepted,
                    };
                },
                chunkSize: chunkMb * 1024 * 1024,
                // En modo multiple los trozos no son exactos: un archivo < 2x chunkSize viaja en un solo trozo
                forceChunkSize: !self.multiple,
                // Cuántos trozos viajan a la vez (entre archivos distintos de la cola)
                simultaneousUploads: parallel,
                // Con false se evita una petición GET extra por trozo (800 menos con 400 imágenes).
                // Pierdes reanudar tras recargar la página; para imágenes sueltas no importa.
                testChunks: !self.multiple,
                maxChunkRetries: 5,
                chunkRetryInterval: 2000,
                permanentErrors: [400, 404, 415, 500, 501],
                xhrTimeout: 60000,
                prioritizeFirstAndLastChunk: false,
                // Identificador único por archivo: evita que dos archivos con mismo nombre y tamaño se mezclen
                generateUniqueIdentifier(file) {
                    const limpio = (file.webkitRelativePath || file.fileName || file.name || 'archivo').replace(/[^A-Za-z0-9_.-]/g, '_');
                    return Date.now() + '-' + Math.random().toString(36).slice(2, 10) + '-' + file.size + '-' + limpio;
                },
            });
    
            if (this.$refs.fileInput) this.resumable.assignBrowse(this.$refs.fileInput);
            if (this.$refs.dropZone) this.resumable.assignDrop(this.$refs.dropZone);
    
            // Se dispara UNA vez por selección (aunque sean 400 archivos)
            this.resumable.on('filesAdded', (added) => {
                this.total += added.length;
                this.uploading = true;
                this.resumable.upload();
            });
    
            this.resumable.on('fileSuccess', async (file, response) => {
                try {
                    let data = JSON.parse(response);
                    // En archivos de varios trozos la última respuesta puede no traer la ruta: se consulta al servidor
                    if (!data.path) {
                        const r = await fetch(target + '?result=' + encodeURIComponent(file.uniqueIdentifier), { headers: { 'Accept': 'application/json' } });
                        data = await r.json();
                    }
                    if (!data.path) throw new Error('Respuesta sin ruta');
                    this.state = this.multiple ? [...this.files(), data.path] : data.path;
                    this.done++;
                } catch (e) {
                    console.error(e);
                    console.log('Respuesta cruda del servidor:', response);
                    this.failed.push(file.fileName);
                }
                this.resumable.removeFile(file);
            });
    
            // Sin alert() por archivo: con 400 serían 400 ventanas. Se acumulan y se muestran abajo.
            this.resumable.on('fileError', (file, message) => {
                console.error('Error tras reintentos en ' + file.fileName + ':', message);
                this.failed.push(file.fileName);
                this.resumable.removeFile(file);
            });
    
            this.resumable.on('complete', () => {
                this.uploading = false;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            });
        },
    
        removeAt(index) {
            if (!confirm('¿Estás seguro de quitar este archivo?')) return;
            const resto = this.files().filter((_, i) => i !== index);
            this.state = this.multiple ? resto : null;
        },
    
        removeAll() {
            if (!confirm('¿Quitar todos los archivos de la lista?')) return;
            this.state = this.multiple ? [] : null;
            this.total = 0;
            this.done = 0;
            this.failed = [];
            this.uploading = false;
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            if (this.resumable) {
                this.resumable.cancel();
                while (this.resumable.files.length > 0) {
                    this.resumable.removeFile(this.resumable.files[0]);
                }
            }
        }
    }" x-init="setupResumable()" data-upload-url="{{ $getUploadUrl() }}"
        data-accepted-types='{{ json_encode($acceptedTypes) }}'
        data-chunk-mb="{{ $getChunkSizeMb() }}" data-parallel="{{ $getParallelUploads() }}">
        <div class="fi-fo-placeholder flex flex-col gap-y-3">

            {{-- Zona de selección: siempre visible para poder añadir más tandas --}}
            <div x-show="!isDisabled && (multiple || files().length === 0)" class="flex items-center justify-center w-full">
                <label x-ref="dropZone"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer border-gray-300 bg-gray-50/50 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-900 transition duration-75">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                        <svg class="w-8 h-8 mb-3 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-guinda dark:text-guinda-dark">Haz clic para
                                seleccionar</span>
                            o arrastra tus archivos
                        </p>
                    </div>
                    <input x-ref="fileInput" type="file" @if ($isMultiple()) multiple @endif class="hidden"
                        @if ($acceptedTypes) accept="{{ implode(',', $acceptedTypes) }}" @endif />
                </label>
            </div>

            {{-- Progreso global --}}
            <div x-show="uploading || total > 0" class="w-full" x-cloak>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                    <div class="bg-guinda h-2 rounded-full transition-all duration-150"
                        :style="`width: ${percent}%`"></div>
                </div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1.5 block"
                    x-text="uploading
                        ? `Subiendo: ${done} de ${total} archivos (${percent}%)`
                        : `Terminado: ${done} de ${total} archivos subidos`"></span>
            </div>

            {{-- Errores acumulados --}}
            <div x-show="failed.length > 0" x-cloak
                class="p-3 text-sm text-red-700 border border-red-200 rounded-xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                <p class="font-semibold" x-text="`${failed.length} archivo(s) no se pudieron subir:`"></p>
                <ul class="mt-1 ml-4 list-disc max-h-32 overflow-y-auto">
                    <template x-for="(nombre, i) in failed" :key="i">
                        <li x-text="nombre"></li>
                    </template>
                </ul>
            </div>

            <div x-show="files().length === 0 && isDisabled"
                class="text-sm italic text-gray-500 dark:text-gray-400 p-2">
                Sin archivos adjuntos.
            </div>

            {{-- Lista simple, sin miniaturas (con cientos de archivos las miniaturas saturan el navegador) --}}
            <div x-show="files().length > 0" x-cloak
                class="border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between px-3 py-2 border-b dark:border-white/10">
                    <span class="text-sm font-medium text-gray-950 dark:text-white"
                        x-text="`${files().length} archivo(s) listos`"></span>
                    @if (!$isDisabled)
                        <button type="button" @click="removeAll()"
                            class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">
                            Quitar todos
                        </button>
                    @endif
                </div>

                <ul class="max-h-80 overflow-y-auto divide-y dark:divide-white/10">
                    <template x-for="(path, index) in files()" :key="path">
                        <li class="flex items-center justify-between gap-x-2 px-3 py-1.5">
                            <span class="text-sm text-gray-950 dark:text-white truncate" x-text="nameOf(path)"
                                :title="path"></span>

                            @if (!$isDisabled)
                                @can('delete', $component)
                                    <button type="button" @click="removeAt(index)"
                                        class="flex items-center justify-center w-7 h-7 shrink-0 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition duration-75"
                                        title="Quitar archivo">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endcan
                            @endif
                        </li>
                    </template>
                </ul>
            </div>

        </div>
    </div>
</x-dynamic-component>