<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">

    @php
        $isDisabled    = $isFieldDisabled();
        $acceptedTypes = $getAcceptedFileTypes() ?? [];
    @endphp

    <div
        wire:ignore
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            progress: 0,
            uploading: false,
            fileName: '',
            resumable: null,
            isDisabled: {{ $isDisabled ? 'true' : 'false' }},

            getCsrfToken() {
                const meta = document.querySelector('meta[name=csrf-token]');
                return meta ? meta.getAttribute('content') : '';
            },

            setupResumable() {
                if (this.isDisabled || typeof Resumable === 'undefined') return;

                const self     = this;
                const target   = this.$el.dataset.uploadUrl;
                const accepted = JSON.parse(this.$el.dataset.acceptedTypes);

                this.resumable = new Resumable({
                    target,
                    query()  {
                        return {
                            _token: self.getCsrfToken(),
                            accepted_types: accepted,
                        };
                    },
                    chunkSize: 4 * 1024 * 1024,
                    forceChunkSize: true,
                    simultaneousUploads: 1,
                    testChunks: true,
                    maxChunkRetries: 5,
                    chunkRetryInterval: 2000,
                    permanentErrors: [400, 404, 415, 500, 501],
                    xhrTimeout: 60000,
                    prioritizeFirstAndLastChunk: false,
                    generateUniqueIdentifier: null,
                });

                if (this.$refs.fileInput) {
                    this.resumable.assignBrowse(this.$refs.fileInput);
                }

                this.resumable.on('fileAdded', (file) => {
                    this.uploading = true;
                    this.progress = 0;
                    this.fileName = file.fileName;
                    this.resumable.upload();
                });

                this.resumable.on('fileProgress', (file) => {
                    this.progress = Math.floor(file.progress() * 100);
                });

                this.resumable.on('fileSuccess', (file, response) => {
                    this.uploading = false;
                    try {
                        const data = JSON.parse(response);
                        this.state = data.path;
                        this.progress = 100;
                        this.resumable.removeFile(file);
                    } catch (e) {
                        console.error(e);
                        alert('Respuesta inválida del servidor');
                    }
                });

                this.resumable.on('fileError', (file, message) => {
                    this.uploading = false;
                    this.progress = 0;
                    console.error('Error fatal tras reintentos:', message);
                    alert('Error al subir el archivo. Intenta de nuevo.');
                    this.resumable.removeFile(file);
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                });
            },

            removeFile() {
                if (!confirm('¿Estás seguro de quitar este archivo?')) return;
                this.state = null;
                this.progress = 0;
                this.uploading = false;
                this.fileName = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                if (this.resumable) {
                    this.resumable.cancel();
                    while (this.resumable.files.length > 0) {
                        this.resumable.removeFile(this.resumable.files[0]);
                    }
                }
            }
        }"
        x-init="setupResumable()"
        data-upload-url="{{ $getUploadUrl() }}"
        data-accepted-types='{{ json_encode($acceptedTypes) }}'
    >
        <div class="fi-fo-placeholder flex flex-col gap-y-2">

            <div x-show="!state && !isDisabled">
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer border-gray-300 bg-gray-50/50 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-900 transition duration-75">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                            <svg class="w-8 h-8 mb-3 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold text-custom-600 dark:text-custom-400">Haz clic para seleccionar</span>
                                o arrastra tu archivo grande
                            </p>
                        </div>
                        <input x-ref="fileInput" type="file" class="hidden"
                            @if ($acceptedTypes) accept="{{ implode(',', $acceptedTypes) }}" @endif />
                    </label>
                </div>

                <div x-show="uploading" class="w-full mt-3" x-cloak>
                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                        <div class="bg-custom-600 h-2 rounded-full transition-all duration-150"
                            :style="`width: ${progress}%`"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1.5 block"
                        x-text="`Subiendo ${fileName}: ${progress}%`"></span>
                </div>
            </div>

            <div x-show="!state && isDisabled"
                class="text-sm italic text-gray-500 dark:text-gray-400 p-2">
                Sin archivo adjunto.
            </div>

            <template x-if="state">
                <div class="flex items-center justify-between p-3 border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center gap-x-3 truncate">
                        <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-600 dark:text-gray-400">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col truncate">
                            <span class="text-sm font-medium text-gray-950 dark:text-white truncate"
                                x-text="state.split('/').pop().replace(/^\d+_(.+)$/, '$1')"></span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 truncate" x-text="state"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2 pl-2">
                        <a :href="'/storage/' + state" target="_blank" download
                            class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-800 transition duration-75"
                            title="Descargar archivo">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </a>

                        @if (!$isDisabled)
                            @can('delete', $component)
                                <button type="button" @click="removeFile()"
                                    class="flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 transition duration-75"
                                    title="Eliminar archivo">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </template>

        </div>
    </div>
</x-dynamic-component>