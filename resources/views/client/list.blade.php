@extends('layout.index')

@section('container')
<div class=" rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold mb-6">Show Client</h2>
    <!-- <button class=" bg-blue-600 text-white  border-gray-700 border-2 rounded-md p-2 hover:bg-blue-800 "><a href="{{url('sync')}}">View Sync Details</a></button> -->
    </div>
    <div class=" overflow-auto">


        <table  class="myTable">
            <thead>
                <tr>
                    <th class="whitespace-nowrap px-3">Action</th>
                    <th class="whitespace-nowrap px-3">Contact Name</th>
                    <th class="whitespace-nowrap px-3">Total Sip</th>
                    <th class="whitespace-nowrap px-3">familyOrgnization</th>
                    <th class="whitespace-nowrap px-3">PAN Card</th>
                    <th class="whitespace-nowrap px-3">Investmant Preferences</th>
                    <th class="whitespace-nowrap px-3">Total Investmant</th>
                    <th class="whitespace-nowrap px-3">Kyc Status</th>
                    <th class="whitespace-nowrap px-3">Email</th>
                    <th class="whitespace-nowrap px-3">Phone</th>
                    <th class="whitespace-nowrap px-3">Aadhar card</th>
                    <th class="whitespace-nowrap px-3">Relationship Manager</th>
                    <th class="whitespace-nowrap px-3">Created At</th>
                    <th class="whitespace-nowrap px-3">Updated At</th>
                </tr>
            </thead>


        </table>
    </div>


</div>
@endsection

@section('script')
<script type="text/javaScript">
    $(document).ready( function () {
        $('.myTable').DataTable({
            colReorder: true,
            processing: true,
            serverSide:true,
            searchable:false,

            ajax:{
                url:"{{route('client.list')}}",
            },
            columns: [
                { data: 'action', name: 'action', orderable:false, searchable:false,},
                { data: 'firstName', name: 'firstName' },
                { data: 'totalSIP', name: 'totalSIP' },
                { data: 'familyOrOrgnization', name: 'familyOrOrgnization' },
                { data: 'panCardNumber', name: 'panCardNumber' },
                { data: 'occupation', name: 'occupation' },
                { data: 'anulIncome', name: 'anulIncome' },
                { data: 'kycStatus', name: 'kycStatus' },
                { data: 'email', name: 'email' },
                { data: 'number', name: 'number' },
                { data: 'aadharCardNumber', name: 'aadharCardNumber' },
                { data: 'relationshipManager', name: 'relationshipManager' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' }
            ]
        });

    } );
</script>
@endsection
