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

        .hover-bg-custom-wine:hover {
            background-color: #6d1b23;
        }
    </style>
@endsection

@section('content')
    <section class="">
        <div class="sm:px-7 px-2 w-full py-20 flex flex-col gap-6">
                <div class="p-4 bg-gray-100/50 dark:bg-neutral-600">
                    <livewire:advanced-search />
                </div>
            </div>

        </div>
    </section>
@endsection

@section('js')

@endsection
