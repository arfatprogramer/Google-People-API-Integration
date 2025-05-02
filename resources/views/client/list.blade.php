@extends('layout.index')

@push('styles')

<style>


    table{
    border-radius: 10px;
    padding: 10px;
    }
    tr{
        border:1px solid gray;
        border-left:none ;
        border-right: none;

    }
    tr:hover td{
        background-color: rgb(206, 206, 206);
    }



</style>
@endpush


@section('container')
<div class=" rounded-lg shadow-md p-6 ">
    <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold mb-6">Show Client</h2>
    <!-- <button class=" bg-blue-600 text-white  border-gray-700 border-2 rounded-md p-2 hover:bg-blue-800 "><a href="{{url('sync')}}">View Sync Details</a></button> -->
    </div>
    <div class=" overflow-auto ">


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


{{-- //demo model --}}
<!-- Modal Background -->
<div id="deleteModal" style="background-color: rgba(128, 128, 128, 0.25);" hidden class="fixed inset-0 bg-gray-300 bg-opacity-25 flex items-center justify-center z-50">
    <!-- Modal Box -->
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full text-center relative">

      <!-- Close Icon -->
      <button id="closeModal2" class="absolute top-2 right-7 text-gray-400 hover:text-gray-600 text-3xl cursor-pointer">&times;</button>

            <div class="w-full flex justify-center items-center">
                    <!-- Warning Icon -->
                    <div  class="w-20 h-20 flex justify-center items-center text-red-500 text-4xl mb-4 text-center cursor-pointer rounded-full shadow-lg border-2  border-red-500 outline-none">
                        <p class="text-center">X</p></div>

            </div>
      <!-- Title -->
      <h2 class="text-xl font-semibold mb-2">Are you sure?</h2>

      <!-- Subtext -->
      <p class="text-gray-600 text-sm mb-6">Do you really want to delete this? After deleting, you can't undo.</p>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3">
        <button id="closeModal" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 cursor-pointer">Cancel</button>
        <button id="googleOrCRMDelete" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded cursor-pointer">Google Or CRM</button>
        <button id="crmDelete" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 cursor-pointer">Only CRM</button>


      </div>
    </div>
  </div>

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


{{-- //Soft--delete----google--delete--contact --}}
<script>


    // When user clicks delete button
    $(document).ready(function() {
    let contactIdToDelete = null;
    let googleDelete = false;

    // When user clicks delete button
    $(document).on('click', '.deleteGoogleContact', function(e) {
        e.preventDefault();
        contactIdToDelete = $(this).data('bs-id'); // Get contact ID dynamically
        console.log('Contact ID:', contactIdToDelete);
        $('#deleteModal').removeAttr('hidden'); // Show modal
    });

    // When user clicks cancel
    $('#crmDelete').click(function() {
        if (contactIdToDelete) {
            sendDeleteRequest(false); // false = only soft delete CRM
        }
    });

    // When user clicks the close (×) button
    $('#closeModal').click(function() {
        $('#deleteModal').attr('hidden', true);
        contactIdToDelete = null;
    });

    $('#closeModal2').click(function() {
        $('#deleteModal').attr('hidden', true);
        contactIdToDelete = null;
    });

    // When user clicks confirm delete
    $('#googleOrCRMDelete').click(function() {
        if (contactIdToDelete) {
            sendDeleteRequest(true); // true = delete Google + CRM

        }
    });

    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
    });
    // Common function to send AJAX
    function sendDeleteRequest(deleteContact) {
        $.ajax({
            url: '/contacts/soft-delete',
            type: 'POST',
            data: {
                client_id: contactIdToDelete,
                delete_contact: deleteContact, // true or false
            },
            success: function(response) {
                console.log('Server Response:', response);
                if (response.success) {
                    // alert('Contact deleted successfully.');
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {

                    toastr.error(response.message);
                }
                // window.location.reload();

        }, // Refresh page or remove row

            error: function(xhr) {
                console.error('Error Response:', xhr.responseText);
                alert('Something went wrong.');
            },
            complete: function() {
                $('#deleteModal').addClass('hidden'); // Always hide modal after action
                contactIdToDelete = null; // Reset
            }
        });
    }

});


//reused able
    toastr.options = {
        "positionClass": "toast-top-center",
        "closeButton": true,
        "progressBar": true,
        "timeOut": "4000"
    };


    </script>
@endsection
