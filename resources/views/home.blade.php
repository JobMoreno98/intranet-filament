@extends('layouts.plantilla')

@section('content')
    <section >
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-10 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="flex flex-col gap-8">
                @forelse ($colecciones as $index => $item)
                    <div data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                        <livewire:parent-collection :collection="$item" :key="$item->id" />
                    </div>

                @empty
                    <div class="w-full text-center py-12">
                        <h3 class="text-lg font-medium text-zinc-500">No hay datos aun</h3>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-2">
            {{ $colecciones->links() }}
        </div>

    </section>
@endsection

@section('js')
    <script>
        function toggleChildren(parentId) {
            const childrenContainer = document.getElementById(`children-${parentId}`);
            if (childrenContainer) {
                childrenContainer.classList.toggle('hidden');
            }
        }
    </script>
@endsection
