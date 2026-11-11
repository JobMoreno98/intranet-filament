@props([
    'title' => 'Aviso importante',
    'storageKey' => 'hide_announcement_popup',
    'button' => 'Entendido',
    'delay' => 1000,
])

<div x-data="{
        showPopup: false,
        dontShowAgain: false,
        storageKey: @js($storageKey),
        init() {
            if (!localStorage.getItem(this.storageKey)) {
                setTimeout(() => { this.showPopup = true; }, {{ (int) $delay }});
            }
        },
        closePopup() {
            if (this.dontShowAgain) {
                localStorage.setItem(this.storageKey, 'true');
            }
            this.showPopup = false;
        }
    }" x-show="showPopup" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div @click.away="closePopup()"
        class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 dark:text-gray-100"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                {{ $title }}
            </h3>
            <button type="button" @click="closePopup()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="py-4 text-sm text-gray-600 dark:text-gray-300 space-y-2">
            {{ $slot }}
        </div>

        <label class="flex items-center gap-2 py-2 text-xs text-gray-500 dark:text-gray-400 select-none cursor-pointer">
            <input type="checkbox" x-model="dontShowAgain"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
            No quiero volver a ver este aviso
        </label>

        <div class="mt-4 flex justify-end">
            <button type="button" @click="closePopup()"
                class="w-full rounded-lg bg-guinda px-4 py-2.5 text-center text-sm font-semibold text-white shadow-md hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-800">
                {{ $button }}
            </button>
        </div>
    </div>
</div>