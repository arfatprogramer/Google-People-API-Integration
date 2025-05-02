@extends('layout.index')

@section('container')
   
    <div class="w-full bg-white  rounded-lg shadow-md  ">
        <form action="{{ isset($data) ? route('client.ContactUpdate') : route('client.create') }}" method="post"
            class="w-full">

            {{-- ----------save-cancle--button-------------------- --}}
            <div class="w-[95vw]  p-2 border-b border-gray-100 shadow-lg fixed top-12  bg-white  z-5  ">
                <div class="flex justify-between  ">

                    <div class="flex justify-center items-center">
                        <a href="#" class="text-blue-500 hover:text-blue-600 ">Clients</a>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current text-gray-500 "
                            viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                        <span class="text-gray-500 hover:text-gray-600">Create</span>
                    </div>

                    <!-- Buttons -->
                    <div class=" flex justify-center-safe mr-2.5 space-x-4  ">
                        <button type="submit"
                            class="w-full border-1 pr-2 pl-2 border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white text-center cursor-pointer">Save
                        </button>
                        <button type='button'
                            class="w-full border-1 pr-2 pl-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white text-center cursor-pointer">Cancle
                        </button>
                    </div>
                </div>
            </div>
            {{-- ----------save-cancle--button-------------------- --}}
            <div class="pt-10  px-8">
                <!-- Tabs -->
                <div class="mb-4 border-b border-gray-100 shadow-lg w-full">
                    <ul class="flex space-x-6 text-gray-400 font-medium">
                        <button type="button" id="defaultOpen" onclick="changeCon(event, 'overview')"
                            class="tablinks p-3  hover:border-blue-400 hover:text-blue-400 cursor-pointer">Overview</button>
                        <button type="button" onclick="changeCon(event, 'address')"
                            class="tablinks p-3  hover:border-blue-400  hover:text-blue-400 cursor-pointer">Address Information</button>
                    </ul>
                </div>

                @if ($data ?? '')
                    @method('PUT')
                @endif

                @csrf

                <!-- Tab Contents -->
                <input type="hidden" name="id" value="{{ old('id', $data->id ?? '') }}">
                <!-- Include jQuery -->
                {{-- ---------addrress-------field--------start----------------- --}}
                <div id="address" class="p-6 tabcontent ">
                    <h2 class="text-xl font-bold mb-4">Address</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Address Type</th>
                                    <th class="px-4 py-2 text-left">Street</th>
                                    <th class="px-4 py-2 text-left">Area</th>
                                    <th class="px-4 py-2 text-left">City</th>
                                    <th class="px-4 py-2 text-left">State</th>
                                    <th class="px-4 py-2 text-left">Postal Code</th>
                                    <th class="px-4 py-2 text-left">Country</th>
                                    <th class="px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody id="addressTable" class="bg-white divide-y divide-gray-200">
                                @if (!empty($clientAddress) && count($clientAddress) > 0)
                                    <!-- Default empty input row -->
                                    @foreach ($clientAddress as $address)
                                        <tr>
                                            <td class="px-4 py-2">
                                                <input type="text" 
                                                    name="addresses[{{ $address->id ?? 0 }}][address_type]"
                                                    value="{{ old('address_type', $address->address_type ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 "
                                                    placeholder="Type">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[{{ $address->id ?? 0 }}][street]"
                                                    value="{{ old('street', $address->street ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Street">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[{{ $address->id ?? 0 }}][area]"
                                                    value="{{ old('area', $address->area ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Area">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[{{ $address->id ?? 0 }}][city]"
                                                    value="{{ old('city', $address->city ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="City">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[{{ $address->id ?? 0 }}][state]"
                                                    value="{{ old('state', $address->state ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="State">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text"
                                                    name="addresses[{{ $address->id ?? 0 }}][postal_code]"
                                                    value="{{ old('postal_code', $address->postal_code ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Postal Code">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[{{ $address->id ?? 0 }}][country]"
                                                    value="{{ old('country', $address->country ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Country">
                                            </td>
                                            <td class="px-4 py-2 flex space-x-2 justify-center">
                                                <button  class="setPrimary text-blue-500 hover:text-blue-700 cursor-pointer" type="button"
                                                    title="Set Primary">🔑</button>
                                                <button class="markWarning text-yellow-500 hover:text-yellow-700 cursor-pointer"
                                                    type="button" title="Warning">⚠️</button>
                                                <button class="deleteRow text-red-500 hover:text-red-700 cursor-pointer" type="button"
                                                    title="Delete">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][address_type]" 
                                                value="{{ old('addressType', $data->addressType ?? '') }}"
                                                class="firstaddress w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 "
                                                placeholder="Type">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][street]"
                                                value="{{ old('street', $data->street ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="Street">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][area]"
                                                value="{{ old('area', $data->area ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="Area">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][city]"
                                                value="{{ old('city', $data->city ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="City">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][state]"
                                                value="{{ old('state', $data->state ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="State">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][postal_code]"
                                                value="{{ old('postalCode', $data->postalCode ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="Postal Code">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][country]"
                                                value="{{ old('country', $data->country ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="Country">
                                        </td>
                                        <td class="px-4 py-2 flex space-x-2 justify-center">
                                            <button class="setPrimary text-blue-500 hover:text-blue-700 cursor-pointer" type="button"
                                                title="Set Primary">🔑</button>
                                            <button class="markWarning text-yellow-500 hover:text-yellow-700 cursor-pointer"
                                                type="button" title="Warning">⚠️</button>
                                            <button  class="deleteRow text-red-500 hover:text-red-700 cursor-pointer" type="button"
                                                title="Delete">🗑️</button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="addRow"
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Add
                    </button>
                </div>
                {{-- ---------addrress-------field--------start----------------- --}}

                <div class="tabcontent  grid grid-cols-1 md:grid-cols-2 gap-6 " id="overview">
                    <div>
                        <label class="block mb-1 font-medium">First Name</label>
                        <input type="text" name="firstName" value="{{ old('firstName', $data->firstName ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                        <span class="text-red-500">{{ $errors->first('firstName') }}</span>

                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Last Name</label>
                        <input type="text" name="lastName" value="{{ old('lastName', $data->lastName ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Phone</label>
                        <div>
                            <div class="flex">
                                <input type="text" value="{{ old('number', $data->number ?? '') }}" name="number"
                                    class="flex-1 border rounded-l px-3 py-2" />
                                <button type="button" class="bg-gray-100 px-4 border border-l-0 rounded-r">+</button>
                            </div>
                            <span class="text-red-500 ">{{ $errors->first('number') }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Family / Organisation Name</label>
                        <input type="text" name="familyOrOrgnization"
                            value="{{ old('familyOrOrgnization', $data->familyOrOrgnization ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>


                    <div>
                        <label class="block mb-1 font-medium">Email</label>
                        <div>
                            <div class="flex">
                                <input type="text" name="email" value="{{ old('email', $data->email ?? '') }}"
                                    class="flex-1 border rounded-l px-3 py-2" />
                                <button type="button" class="bg-gray-100 px-4 border border-l-0 rounded-r">+</button>
                            </div>
                            <span class="text-red-500">{{ $errors->first('email') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">PAN Card</label>
                        <input type="text" value="{{ old('panCardNumber', $data->panCardNumber ?? '') }}"
                            name="panCardNumber" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Aadhar Card</label>
                        <input type="text" value="{{ old('aadharCardNumber', $data->aadharCardNumber ?? '') }}"
                            name="aadharCardNumber" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Occupation</label>
                        <select name="occupation" class="w-full border rounded px-3 py-2">
                            <option value="{{ old('occupation', $data->occupation ?? '') }}">
                                {{ old('occupation', $data->occupation ?? '') }}</option>
                            <option value="Business">Business</option>
                            <option value="Agriculturist">Agriculturist</option>
                            <option value="Goverment Services">Goverment Services</option>
                            <option value="Public Sector Service">Public Sector Service</option>
                            <option value="Retried">Retried</option>
                            <option value="Student">Student</option>
                            <option value="Healthcare Professional">Healthcare Professional</option>
                            <option value="HomeMaker">HomeMaker</option>
                            <option value="Private Sector Employee">Private Sector Employee</option>
                            <option value="Skilled Worker/Tradesperson">Skilled Worker/Tradesperson</option>
                            <option value="Teacher/Professor">Teacher/Professor</option>
                            <option value="Profession">Profession</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">KYC Status</label>
                        <select name="kycStatus" class="w-full border rounded px-3 py-2">
                            <option value="{{ old('kycStatus', $data->kycStatus ?? '') }}">
                                {{ old('kycStatus', $data->kycStatus ?? '') }}</option>
                            <option value="Not Started">Not Started</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Annual Income</label>
                        <input type="text"name="anulIncome" value="{{ old('anulIncome', $data->anulIncome ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Referred By Person Name</label>
                        <div class="flex">
                            <input type="text" name="referredBy"
                                value="{{ old('referredBy', $data->referredBy ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Total Investment</label>
                        <input type="text" name="totalInvestment"
                            value="{{ old('totalInvestment', $data->totalInvestment ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Comments / History</label>
                        <textarea name="comments" class="w-full border rounded px-3 py-2" rows="3">{{ old('comments', $data->comments ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Relationship Manager</label>
                        <div class="flex">
                            <input type="text" name="relationshipManager"
                                value="{{ old('relationshipManager', $data->relationshipManager ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" value="Mo Arfat Ansari" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Service RM</label>
                        <div class="flex">
                            <input type="text" name="serviceRM" value="{{ old('serviceRM', $data->serviceRM ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Total SIP</label>
                        <input type="text" name="totalSIP" value="{{ old('totalSIP', $data->totalSIP ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Primary Contact Person</label>
                        <div class="flex">
                            <input type="text" name="primeryContactPerson"
                                value="{{ old('primeryContactPerson', $data->primeryContactPerson ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button " class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Meeting Scheduled</label>
                        <select name="meetinSchedule" class="w-full border rounded px-3 py-2">
                            <option value="{{ old('meetinSchedule', $data->meetinSchedule ?? '') }}">
                                {{ old('meetinSchedule', $data->meetinSchedule ?? '') }}</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Weekely">Weekely</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">First Meeting Date</label>
                        <input type="date" name="firstMeetingDate"
                            value="{{ old('firstMeetingDate', $data->firstMeetingDate ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>


                    <!-- Personal Information -->
                    <h3 class="text-xl font-semibold mt-10 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-medium">Type of Relation</label>
                            <select name="typeOfRelation" class="w-full border rounded px-3 py-2">
                                <option value="{{ old('typeOfRelation', $data->typeOfRelation ?? '') }}">
                                    {{ old('typeOfRelation', $data->typeOfRelation ?? '') }}</option>
                                <option value="Father">Father</option>
                                <option value="Father">Mother</option>
                                <option value="Father">Sister</option>
                                <option value="Father">Brother</option>
                                <option value="Father">Daughter</option>
                                <option value="Father">Spouse</option>
                                <option value="Father">Son</option>
                                <option value="Father">Famaily/Head</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 font-medium">Marital Status</label>
                            <select name="maritalStatus" class="w-full border rounded px-3 py-2">
                                <option value="{{ old('maritalStatus', $data->maritalStatus ?? '') }}">
                                    {{ old('maritalStatus', $data->maritalStatus ?? '') }}</option>
                                <option>Single</option>
                                <option>Married</option>
                                <option>Other</option>
                            </select>
                        </div>
                    </div>


                </div>


                    
            </div>
        </form>
    </div>



@endsection
@section('script')
    <script>
        function changeCon(evt, contact) {
            var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.add("hidden"); // Hide all tabs
            }

            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("border-b-2", "border-blue-500","text-blue-500"); // Remove active styles
            }

            document.getElementById(contact).classList.remove("hidden"); // Show the selected tab
            evt.currentTarget.classList.add("border-b-2", "border-blue-500",'text-blue-500'); // Highlight active tab
        }

        // Auto-click default tab on page load
        document.getElementById("defaultOpen").click();
    </script>

    <script>
        $(document).ready(function() {
            let addressIndex = 1;

            // Add default row on page load
            $('#addRow').on('click', function() {

                let address = $('.firstaddress').val().trim(); // Get and trim the input value
                if (address === '') {
                    // alert('Please fill the address field');
                    toastr.error(' Please fill in the address field.');
                    return; // Stop if input is empty
                }

                let newRow = `
      <tr>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][address_type]" value="{{ old('addressType', $data->addressType ?? '') }}" class="firstaddress w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 " placeholder="Type">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][street]" value="{{ old('street', $data->street ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Street">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][area]" value="{{ old('area', $data->area ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Area">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][city]" value="{{ old('city', $data->city ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="City">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][state]" value="{{ old('state', $data->state ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="State">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][postal_code]" value="{{ old('postalCode', $data->postalCode ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Postal Code">
        </td>
        <td class="px-4 py-2">
          <input type="text" name="addresses[${addressIndex}][country]" value="{{ old('country', $data->country ?? '') }}" class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300" placeholder="Country">
        </td>
        <td class="px-4 py-2 flex space-x-2 justify-center">
          <button class="setPrimary text-blue-500 hover:text-blue-700 cursor-pointer" type="button" title="Set Primary ">🔑</button>
          <button class="markWarning text-yellow-500 hover:text-yellow-700 cursor-pointer" type="button" title="Warning">⚠️</button>
          <button class="deleteRow text-red-500 hover:text-red-700 cursor-pointer" type="button" title="Delete">🗑️</button>
        </td>
      </tr>
    `;
                $('#addressTable').append(newRow);
                addressIndex++;
            });

            // Delete row
            $(document).on('click', '.deleteRow', function() {
                $(this).closest('tr').remove();
            });

            // Set as primary
            $(document).on('click', '.setPrimary', function() {
                $('.setPrimary').removeClass('font-bold');
                $(this).addClass('font-bold');
                alert('Set this address as primary!');
            });

            // Mark as warning
            $(document).on('click', '.markWarning', function() {
                $(this).closest('tr').toggleClass('bg-yellow-100');
                alert('Marked address with warning!');
            });

            
            
        });
    </script>
@endsection
