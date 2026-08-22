@extends('layouts.plantilla')

@section('css')
    <style>
        .bg-custom-wine {
            background-color: #86212b;
        }

        .text-custom-wine {
            color: #86212b;
        }

        .border-custom-wine {
            border-color: #86212b;
        }

        .hover-text-custom-wine:hover {
            color: #86212b;
        }
    </style>
@endsection

@section('content')
    <section>
        {{-- Hero del blog --}}
        <div class="w-full bg-[#86212b] py-8 px-4">
            <div class="max-w-6xl mx-auto text-center flex flex-col gap-3">
                <span class="text-red-200 text-md uppercase tracking-[0.2em] font-bold">
                    Arreola Histórica
                </span>
                {{--  
                <h1 class="text-3xl md:text-4xl font-bold text-white">
                    Blog del Archivo
                </h1>
                --}}
            </div>
        </div>

        <div class="sm:px-7 px-3 w-full py-12 max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10">

            {{-- Columna principal: listado de artículos --}}
            <div class="lg:col-span-2 flex flex-col gap-8">
                <livewire:blog-list :tag="request('tag')" :key="'blog-' . request('tag', 'todas')" />
            </div>

            {{-- Sidebar --}}
            <aside class="flex flex-col gap-8">
                @include('blog-sidebar')
            </aside>

        </div>
    </section>
@endsection