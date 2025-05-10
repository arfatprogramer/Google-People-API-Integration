@extends('layout.index')

@section('container')
    <div class="space-y-12">
        <div>
            <h2 class="text-xl font-semibold mb-4">Synced Contacts</h2>
            {!! $contactsTable->table(['class' => 'table table-striped w-full']) !!}
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-4">Sync History</h2>
            {!! $historyTable->table(['class' => 'table table-striped w-full']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    {!! $contactsScripts !!}
    {!! $historyScripts !!}
@endpush
