@extends('layout.index')

@section('container')
<div class=" rounded-lg shadow-md p-6 ">
    <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold mb-6">Show Client</h2>
    <button class=" bg-blue-600 text-white  border-gray-700 border-2 rounded-md p-2 hover:bg-blue-800 "><a href="{{url('sync')}}">View Sync Details</a></button>
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

  
  <!-- Modal -->
<div id="deleteModal" style="background-color: rgba(128, 128, 128, 0.25);" class="fixed hidden inset-0 bg-gray-300 bg-opacity-25 z-40 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg mx-4 relative">
        
        <!-- Close Icon -->
        <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl font-bold leading-none">&times;</button>
        
        <h2 class="text-xl font-bold mb-4">Delete Contact</h2>
        <p class="mb-6 text-gray-600">Are you sure you want to delete this Google contact?</p>

        <div class="flex justify-end space-x-4">

            <button id="confirmDelete" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded cursor-pointer">
                Yes
              </button>
              <button id="cancelDelete" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded cursor-pointer">
                No
              </button>
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
        $('#deleteModal').removeClass('hidden'); // Show modal
    });

    // When user clicks cancel
    $('#cancelDelete').click(function() {
        if (contactIdToDelete) {
            sendDeleteRequest(false); // false = only soft delete CRM
        }
    });

    // When user clicks the close (×) button
    $('#closeModal').click(function() {
        $('#deleteModal').addClass('hidden');
        contactIdToDelete = null;
    });

    // When user clicks confirm delete
    $('#confirmDelete').click(function() {
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
