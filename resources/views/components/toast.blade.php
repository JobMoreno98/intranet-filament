<div
    x-data="{ 
        show: false, 
        mensaje: '',
        progreso: 100,
        temporizador: null,
        intervalo: null,
        
        mostrar(texto) {
            this.mensaje = texto;
            this.show = true;
            this.progreso = 100;
            
            clearTimeout(this.temporizador);
            clearInterval(this.intervalo);
            
            this.intervalo = setInterval(() => {
                if(this.progreso > 0) this.progreso -= 1;
            }, 30);
            
            this.temporizador = setTimeout(() => {
                this.show = false;
                clearInterval(this.intervalo);
            }, 3000);
        }
    }"
    x-on:mostrar-toast.window="mostrar($event.detail.mensaje)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    
    class="fixed bottom-5 right-5 bg-[#ffffff] text-[#131f30] dark:bg-[#131f30] dark:text-[#ffffff] rounded-lg shadow-xl z-50 overflow-hidden min-w-[280px]"
    style="display: none;"
>
    <!-- Contenedor del texto -->
    <div class="px-6 py-3">
        <span x-text="mensaje" class="font-medium text-sm"></span>
    </div>
    
    <!-- Contenedor de la barra de progreso -->
    <!-- Claro: Fondo gris claro, Oscuro: Fondo gris oscuro (#1d1d1b de la paleta) -->
    <div class="w-full h-1.5 border bg-gray-100 dark:bg-[#1d1d1b]">
        <!-- La barra dinámica: Azul acento (#0a415c de la paleta) -->
        <div 
            class="h-full bg-[#0a415c] transition-all duration-75 ease-linear"
            x-bind:style="`width: ${progreso}%`"
        ></div>
    </div>
</div>