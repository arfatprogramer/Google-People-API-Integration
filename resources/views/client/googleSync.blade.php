@extends('layout.index')

@section('container')
<div class=" mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Heading -->
    <div class="flex justify-between">


        <div class="">
            <h1 class="text-3xl font-bold text-gray-900">Google Contacts Sync</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage synced contacts from your Google account.</p>
        </div>
        <a href="{{ route('client.syncProcess') }}" class="h-fit bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md shadow-sm text-sm font-medium transition">
            Sync Now
        </a>
    </div>

    <!-- Sync Details -->
    <div class="flex gap-20 ">
    <div class="mt-10 w-full bg-white p-6 rounded-lg shadow-xl border">
    <p class="text-2xl font-semibold text-gray-800 mb-4">CRM Contacts</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <!-- Total Contacts -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md">
            <p class="text-gray-500 text-sm">Total Contacts</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $crmData['total'] ?? 0 }}</p>
        </div>

        <!-- Synced Contacts -->
        <div class="bg-green-50 p-6 rounded-lg shadow-md">
            <p class="text-gray-500 text-sm">Synced</p>
            <p class="text-2xl font-semibold text-green-600">{{ $crmData['sync'] ?? 0 }}</p>
        </div>

        <!-- Pending CRM to Google -->
        <div class="bg-yellow-50 p-6 rounded-lg shadow-md">
            <p class="text-gray-500 text-sm">Pending on CRM to Sync Google</p>
            <p class="text-2xl font-semibold text-yellow-500">{{ $crmData['pending'] ?? 0 }}</p>
        </div>

        <!-- Pending Google to CRM -->
        <div class="bg-blue-50 p-6 rounded-lg shadow-md">
            <p class="text-gray-500 text-sm">Pending on Google to Sync CRM</p>
            <p class="text-2xl font-semibold text-blue-500">{{ $googleContact['pending'] ?? 0 }}</p>
        </div>

    </div>
</div>


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




    <!-- Contacts Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-xl my-10">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Last Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($contacts as $contact)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $contact->name }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $contact->email }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $contact->phone }}</td>
                    <td class="px-6 py-4">
                        @if($contact->is_synced)
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Synced</span>
                        @else
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-1 rounded-full">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $contact->synced_at ? $contact->synced_at->format('Y-m-d H:i') : 'N/A' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-center text-gray-400">No contacts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
