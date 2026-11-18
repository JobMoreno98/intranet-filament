<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    @php
        $isDisabled = $isFieldDisabled();
        $acceptedTypes = $getAcceptedFileTypes() ?? [];

        // "image/jpeg" -> "JPEG", ".pdf" -> "PDF"
        $formatosTexto = collect($acceptedTypes)
            ->map(fn($t) => strtoupper(ltrim(preg_replace('#^[a-z]+/#i', '', (string) $t), '.')))
            ->unique()
            ->implode(', ');
    @endphp

    {{-- El estado es un ARREGLO de rutas en modo multiple, o una sola cadena en modo simple --}}
    <div wire:ignore x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        uploading: false,
        isDragging: false,
        current: '',
        total: 0,
        done: 0,
        failed: [],
        limit: 50,
        showAll: false,
        resumable: null,
        isDisabled: {{ $isDisabled ? 'true' : 'false' }},
        multiple: {{ $isMultiple() ? 'true' : 'false' }},
    
        // Siempre trabajamos con un arreglo, aunque el estado viejo sea una cadena o null
        files() {
            if (Array.isArray(this.state)) return this.state;
            return this.state ? [this.state] : [];
        },
    
        // Solo se pintan los primeros N para no cargar cientos de filas en el DOM
        visible() {
            const todos = this.files();
            return this.showAll ? todos : todos.slice(0, this.limit);
        },
    
        nameOf(path) {
            return String(path).split('/').pop().replace(/^[0-9a-f]{13}_(.+)$/i, '$1');
        },
    
        isPdf(path) {
            return /\.pdf$/i.test(String(path));
        },
    
        get percent() {
            if (!this.total) return 0;
            return Math.floor(((this.done + this.failed.length) / this.total) * 100);
        },
    
        get finished() {
            return !this.uploading && this.total > 0;
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
                simultaneousUploads: parallel,
                // En modo multiple se evita un GET extra por trozo; en modo simple se conserva la reanudación
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
    
            this.resumable.on('fileProgress', (file) => {
                this.current = file.fileName;
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
                this.current = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            });
        },
    
        resetStatus() {
            this.total = 0;
            this.done = 0;
            this.failed = [];
        },
    
        removeAt(index) {
            if (!confirm('¿Estás seguro de quitar este archivo?')) return;
            const resto = this.files().filter((_, i) => i !== index);
            this.state = this.multiple ? resto : null;
        },
    
        removeAll() {
            if (!confirm('¿Quitar todos los archivos de la lista?')) return;
            this.state = this.multiple ? [] : null;
            this.resetStatus();
            this.uploading = false;
            this.current = '';
            this.showAll = false;
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

        <div class="fi-fo-placeholder flex flex-col gap-y-4">

            {{-- ZONA DE SELECCIÓN --}}
            <div x-show="!isDisabled && (multiple || files().length === 0)">
                <label x-ref="dropZone" @dragover="isDragging = true" @dragleave="isDragging = false"
                    @drop="isDragging = false"
                    :class="isDragging
                        ?
                        'border-guinda bg-guinda/5 dark:bg-guinda/10' :
                        'border-gray-300 bg-gray-50/50 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-900'"
                    class="flex flex-col items-center justify-center w-full px-4 py-8 text-center transition duration-150 border-2 border-dashed cursor-pointer rounded-xl focus-within:ring-2 focus-within:ring-guinda/40">

                    <span
                        class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10"
                        :class="isDragging ? 'text-guinda' : 'text-gray-400 dark:text-gray-500'">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                        </svg>
                    </span>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-semibold text-guinda dark:text-guinda-dark"
                            x-text="isDragging ? 'Suelta los archivos aquí' : 'Haz clic para seleccionar'"></span>
                        <span x-show="!isDragging">o arrastra @if ($isMultiple()) tus archivos @else tu archivo @endif</span>
                    </p>

                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        @if ($isMultiple())
                            Puedes elegir varios a la vez.
                        @endif
                        @if ($formatosTexto !== '')
                            Formatos: {{ e($formatosTexto) }}.
                        @endif
                    </p>

                    <input x-ref="fileInput" type="file" @if ($isMultiple()) multiple @endif class="sr-only"
                        @if ($acceptedTypes) accept="{{ implode(',', $acceptedTypes) }}" @endif />
                </label>
            </div>

            {{-- TARJETA DE ESTADO DE LA SUBIDA --}}
            <div x-show="total > 0" x-cloak
                class="p-4 border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                role="status" aria-live="polite">

                <div class="flex items-center justify-between gap-x-3">
                    <div class="flex items-center min-w-0 gap-x-2">
                        {{-- Spinner --}}
                        <svg x-show="uploading" class="w-5 h-5 shrink-0 animate-spin text-guinda"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        {{-- Completado sin fallos --}}
                        <svg x-show="finished && failed.length === 0" class="w-5 h-5 text-green-600 shrink-0"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{-- Completado con fallos --}}
                        <svg x-show="finished && failed.length > 0" class="w-5 h-5 text-amber-500 shrink-0"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>

                        <span class="text-sm font-semibold text-gray-950 dark:text-white truncate"
                            x-text="uploading
                                ? 'Subiendo archivos…'
                                : (failed.length > 0 ? 'Subida terminada con errores' : 'Subida completada')"></span>
                    </div>

                    <div class="flex items-center gap-x-3 shrink-0">
                        <span class="text-sm tabular-nums text-gray-500 dark:text-gray-400"
                            x-text="`${done} / ${total}`"></span>
                        <button type="button" x-show="finished" @click="resetStatus()"
                            class="text-xs font-medium text-gray-500 hover:text-gray-700 hover:underline dark:text-gray-400 dark:hover:text-gray-200">
                            Ocultar
                        </button>
                    </div>
                </div>

                <div class="w-full h-2 mt-3 overflow-hidden bg-gray-200 rounded-full dark:bg-gray-700" role="progressbar"
                    aria-valuemin="0" aria-valuemax="100" :aria-valuenow="percent">
                    <div class="h-2 transition-all duration-200 rounded-full"
                        :class="finished && failed.length === 0 ? 'bg-green-600' : 'bg-guinda'"
                        :style="`width: ${percent}%`"></div>
                </div>

                <p x-show="uploading && current" class="mt-2 text-xs text-gray-500 truncate dark:text-gray-400"
                    x-text="`Archivo actual: ${current}`"></p>

                <p x-show="uploading"
                    class="mt-2 text-xs text-amber-700 dark:text-amber-400">
                    Espera a que termine antes de guardar el formulario; solo se guardan los archivos ya subidos.
                </p>
            </div>

            {{-- ERRORES ACUMULADOS --}}
            <div x-show="failed.length > 0" x-cloak
                class="p-3 text-sm text-red-700 border border-red-200 rounded-xl bg-red-50 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                <p class="font-semibold" x-text="`${failed.length} archivo(s) no se pudieron subir:`"></p>
                <ul class="mt-1 ml-4 overflow-y-auto list-disc max-h-32">
                    <template x-for="(nombre, i) in failed" :key="i">
                        <li class="truncate" x-text="nombre"></li>
                    </template>
                </ul>
                <p class="mt-2 text-xs">Vuelve a seleccionarlos para reintentar.</p>
            </div>

            <div x-show="files().length === 0 && isDisabled"
                class="p-2 text-sm italic text-gray-500 dark:text-gray-400">
                Sin archivos adjuntos.
            </div>

            {{-- LISTA DE ARCHIVOS (sin miniaturas: con cientos de archivos saturan el navegador) --}}
            <div x-show="files().length > 0" x-cloak
                class="border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between px-4 py-2.5 border-b dark:border-white/10">
                    <span class="text-sm font-medium text-gray-950 dark:text-white"
                        x-text="files().length === 1 ? '1 archivo listo' : `${files().length} archivos listos`"></span>
                    @if (!$isDisabled)
                        <button type="button" @click="removeAll()"
                            class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">
                            Quitar todos
                        </button>
                    @endif
                </div>

                <ul class="overflow-y-auto divide-y max-h-80 dark:divide-white/10">
                    <template x-for="(path, index) in visible()" :key="path">
                        <li class="flex items-center gap-x-3 px-4 py-2">
                            <span class="w-8 text-xs text-right text-gray-400 tabular-nums shrink-0 dark:text-gray-500"
                                x-text="index + 1"></span>

                            <span
                                class="flex items-center justify-center w-8 h-8 text-gray-500 rounded-lg shrink-0 bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                {{-- PDF --}}
                                <svg x-show="isPdf(path)" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                {{-- Imagen u otro --}}
                                <svg x-show="!isPdf(path)" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </span>

                            <span class="flex-1 min-w-0 text-sm text-gray-950 truncate dark:text-white"
                                x-text="nameOf(path)" :title="path"></span>

                            @if (!$isDisabled)
                                @can('delete', $component)
                                    <button type="button" @click="removeAt(index)"
                                        class="flex items-center justify-center w-7 h-7 text-red-600 transition duration-75 rounded-lg shrink-0 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                                        title="Quitar archivo" aria-label="Quitar archivo">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endcan
                            @endif
                        </li>
                    </template>
                </ul>

                <div x-show="!showAll && files().length > limit" class="px-4 py-2 border-t dark:border-white/10">
                    <button type="button" @click="showAll = true"
                        class="text-xs font-medium text-guinda hover:underline dark:text-guinda-dark"
                        x-text="`Mostrar los ${files().length - limit} restantes`"></button>
                </div>
            </div>

        </div>
    </div>
</x-dynamic-component>