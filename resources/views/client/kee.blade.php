@extends('layout.index')

@section('container')

<div class="bg-pink-500 h-screen relative">
    <div class="absolute inset-0 flex justify-center items-center bg-opacity-25 backdrop-blur-3xl overflow-hidden">
        <div class="bg-gray-100 shadow-lg text-black p-2 w-[600px]  mx-auto border-2 border-black rounded-lg overflow-auto">
            <div class="flex justify-between p-2 pb-4">
                <h1 class="font-bold text-2xl">Upload New Product</h1>
                <button onclick="closeModal()" class="text-2xl w-7 hover:scale-125 hover:text-red-500 transition-transform">
                    &times;
                </button>
            </div>

            <!-- Your form or content here -->

        </div>
    </div>
</div>

@endsection





