@extends('layout.index')

@section('container')
<div class=" mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Heading -->
    <div class="flex justify-between">


        <div class="">
            <h1 class="text-3xl font-bold text-gray-900"> Sync Process</h1>
            <p class="text-sm text-gray-500 mt-1">View  sync Process contacts from your Google account.</p>
        </div>
        <a href="{{ route('client.list') }}" class="h-fit bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md shadow-sm text-sm font-medium transition">
            Clients
        </a>
    </div>



    <!-- Client Details Section -->
    <div class="mt-10 bg-white p-6 rounded-lg shadow-xl border">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Client Details</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
            <div>
                <p class="text-gray-500">Name</p>
                <p class="font-medium text-gray-900">{{ $client->name ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium text-gray-900">{{ $client->email ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-medium text-gray-900">{{ $client->phone ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-gray-500">Last Sync Date</p>
                <p class="font-medium text-gray-900">

                </p>
            </div>

            <div>
                <p class="text-gray-500">Last Sync Token</p>
                <p class="font-mono break-all bg-gray-100 p-2 rounded-md border">
                    {{ $client->access_token ?? 'Not Available' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Last Sync (Seconds Ago)</p>
                <p class="font-semibold text-blue-700">

                </p>
            </div>
        </div>
    </div>





</div>
@endsection
