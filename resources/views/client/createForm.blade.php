@extends('layout.index')

@section('container')
   
    <div class="w-full bg-white  rounded-lg shadow-md  ">
        <form action="{{ isset($contacts) ? route('client.ContactUpdate') : route('client.create') }}" method="post"
            class="w-full">
            {{-- {{print_r($contacts['email_json']);}} --}}
              {{-- {{dd($contacts);}} --}}
              {{-- {{dd($contacts['first_name']); }} --}}
            {{-- ----------save-cancle--button-------------------- --}}
            <input type="hidden" name="sync_status_c" value="{{$contacts['sync_status_c'] ?? ''}}">
            <div class="w-[95vw]  p-2 border-b border-gray-100 shadow-lg fixed top-12  bg-white  z-5  ">
                <div class="flex justify-between  ">

                    <div class="flex justify-center items-center ml-2.5">
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

                @if ($contacts ?? '')
                    @method('PUT')
                @endif

                @csrf
                {{-- {{print_r($contacts['phone_json'])}} --}}
                <!-- Tab Contents -->
                <input type="hidden" name="id" value="{{ old('id', $contacts['id'] ?? '') }}">
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
                                @if (!empty($contacts['address_type']))
                                    <!-- Default empty input row -->
                                    {{-- @foreach ($clientAddress as $address) --}}
                                        <tr>
                                            <td class="px-4 py-2">
                                                <input type="text" 
                                                    name="address_type"
                                                    value="{{ old('address_type', $contacts['address_type'] ?? '') }}"
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 "
                                                    placeholder="Type">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[0][street]"
                                                    {{-- value="{{ old('street', $contacts['street'] ?? '') }}" --}}
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Street">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[0][area]"
                                                    {{-- value="{{ old('area', $contacts['area'] ?? '') }}" --}}
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Area">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[0][city]"
                                                    {{-- value="{{ old('city', $contacts['area'] ?? '') }}" --}}
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="City">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[0][state]"
                                                    {{-- value="{{ old('state', $contacts['state'] ?? '') }}" --}}
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="State">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text"
                                                    name="addresses[0][postal_code]"
                                                    {{-- value="{{ old('postal_code', $contacts['postal_code'] ?? '') }}" --}}
                                                    class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                    placeholder="Postal Code">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="addresses[0][country]"
                                                    {{-- value="{{ old('country', $contacts['country'] ?? '') }}" --}}
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
                                    {{-- @endforeach --}}
                                @else
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][address_type]" 
                                                value="{{ old('addressType', $data->addressType ?? '') }}"
                                                class="firstaddress w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300 "
                                                placeholder="Type">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][street]" id="street"
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
                                            <input type="text" name="addresses[0][city]" id="city"
                                                value="{{ old('city', $data->city ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="City">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][state]" id="state"
                                                value="{{ old('state', $data->state ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="State">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][postal_code]" id="postal_code"
                                                value="{{ old('postalCode', $data->postalCode ?? '') }}"
                                                class="w-full border  border-gray-300 p-0.5 shadow-sm outline-none focus:border-blue-300"
                                                placeholder="Postal Code">
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" name="addresses[0][country]" id="country"
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
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 cursor-pointer">
                        Add
                    </button>
                </div>
                {{-- ---------addrress-------field--------start----------------- --}}

                <div class="tabcontent  grid grid-cols-1 md:grid-cols-2 gap-6 " id="overview">
                    <div>
                        <label class="block mb-1 font-medium">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $contacts['first_name']  ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                        <span class="text-red-500">{{ $errors->first('firstName') }}</span>

                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $contacts['last_name'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                   {{-- {{print_r($contacts)}} --}}
                     <!-- Phone Numbers -->
                       
                    <div class="relative">
                        <label class="block mb-1 font-medium">Phone</label>
                        <div>
                            <div class="flex relative">
                                
                                <input type="text"   value="{{old('phone',$contacts['phone_primary'] ?? '')}}"
                                 name="phone" data-target-input=".popUpinput"
                                    class="toggle-container-input flex-1 border rounded-l px-3 py-2" />
                                    <span id="countNumber" class="absolute bg-blue-500 text-white rounded-lg  right-12 top-2   z-50 "></span>
                                <button   data-target=".phone-container" type="button" class="toggle-container-btn bg-gray-100 px-4 border border-l-0 rounded-r cursor-pointer">+</button>
                            </div>
                            <span class="text-red-500">{{ $errors->first('phone') }}</span>
                        </div>
                        {{-- -------multiple--number---popUp-window---start----- --}}
                            <div  class="popUpinput dynamic-container phone-container  hidden absolute top-full left-0 mt-2  p-4   border-1 border-gray-400 rounded-md w-full max-w-5xl bg-white shadow-lg z-50" data-type="phone">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Phone No</label>

                            <!-- Input Group Template -->
                             @php
                            $phones = json_decode($contacts['phone_json'] ?? '[]', true);
                            // dd($phones);
                            @endphp
                            
                            @if(!empty($phones))
                            @foreach ($phones as $index => $phone)
                            <div class="input-group  flex items-center gap-2 mb-2 ">
                                 <label for="phone_{{ $index }}" class="block font-medium">Phone #{{ $index + 1 }}</label>
                                <input type="text" id="popupPhoneInput"  value="{{ $phone['phone_number'] ?? '' }}"
                                name="phone_json[]" placeholder="Enter phone number"
                                    class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <!-- Icons -->
                                <label title="set Primary" class="text-gray-600 hover:text-gray-900 cursor-pointer">🔑</label>
                                <!-- Radio --> 
                                <input type="radio" name="phone_primary" value="{{$index}}" 
                                       {{ $phone['primary'] ? 'checked' : '' }} 
                                class="form-radio accent-blue-600 cursor-pointer">
                                <label title="set Whatsapp" class="text-gray-600 hover:text-green-600 cursor-pointer">
                                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/whatsapp.svg"
                            class="h-4 w-4 accent-green-500" alt="WhatsApp Icon" />

                                </label>
                                <!-- Checkboxes -->
                                <input type="checkbox" title="unsubscribe" class="form-checkbox accent-blue-600 cursor-pointer">
                                <label title="Unsubscribe" type="button" class="text-gray-600 hover:text-red-600 cursor-pointer">🚫</label>
                                <input type="checkbox" class="form-checkbox accent-blue-600 cursor-pointer">
                                <!-- Delete Button -->
                                <button title="delete" type="button" class="delete-btn text-gray-500 hover:text-red-600 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                                </button>
                            </div>
                            @endforeach
                            @else
                            <div class="input-group  flex items-center gap-2 mb-2 ">
                                <input type="text" id="popupPhoneInput"  
                                name="phone_json[]" placeholder="Enter phone number"
                                    class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <!-- Icons -->
                                <label title="set Primary" class="text-gray-600 hover:text-gray-900 cursor-pointer">🔑</label>
                                <!-- Radio --> 
                                <input type="radio" name="phone_primary"  
                                       
                                class="form-radio accent-blue-600 cursor-pointer">
                                <label title="set Whatsapp" class="text-gray-600 hover:text-green-600 cursor-pointer">
                                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/whatsapp.svg"
                            class="h-4 w-4 accent-green-500" alt="WhatsApp Icon" />

                                </label>
                                <!-- Checkboxes -->
                                <input type="checkbox" title="unsubscribe" class="form-checkbox accent-blue-600 cursor-pointer">
                                <label title="Unsubscribe" type="button" class="text-gray-600 hover:text-red-600 cursor-pointer">🚫</label>
                                <input type="checkbox" class="form-checkbox accent-blue-600 cursor-pointer">
                                <!-- Delete Button -->
                                <button title="delete" type="button" class="delete-btn text-gray-500 hover:text-red-600 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                                </button>
                            </div>
                            @endif
                            <!-- Add Button -->
                            <button type="button"  title="add"
                                class="add-input-btn mt-2 px-4 py-1 bg-blue-100 border border-blue-300 rounded text-sm hover:bg-blue-200 cursor-alias">
                                Add
                            </button>
                        </div>

                        {{-- -------multiple--number------end----- --}}
                    </div>
                   
                    <div>
                        <label class="block mb-1 font-medium">Family / Organisation Name</label>
                        <input type="text" name="familyOrOrgnization"
                            value="{{ old('familyOrOrgnization', $data->familyOrOrgnization ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>


                  
                      <div class="relative">
                        <label class="block mb-1 font-medium">Email</label>
                        <div>
                            <div class="flex">
                                <input type="email"  value="{{old('email',$contacts['email_primary'] ?? '')}}"
                                 name="email" data-target-input=".popUpinputEmail"
                                    class="toggle-container-input flex-1 border rounded-l px-3 py-2" />
                                <button   data-target=".email-container" type="button" class="toggle-container-btn bg-gray-100 px-4 border border-l-0 rounded-r cursor-pointer">+</button>
                            </div>
                            <span class="text-red-500 ">{{ $errors->first('email') }}</span>
                        </div>

                        {{-- -------multiple--email------start----- --}}
                            <div  class="popUpinputEmail dynamic-container email-container hidden absolute top-full left-0 mt-2  p-4   border-1 border-gray-400 rounded-md w-full max-w-5xl bg-white shadow-lg z-50" data-type="email">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                              @php
                                  $email_json = json_decode($contacts['email_json'] ?? '[]',true);
                                //   print_r($email_json);
                                //   dd($email_json);
                              @endphp
                             
                              @if(!empty($email_json))
                              
                            <!-- Input Group Template -->
                            @foreach ($email_json as $index => $email)

                            <div class="input-group  flex items-center gap-2 mb-2 ">
                                <input type="email" name="email_json[]" value="{{$email['email_address'] ?? ''}}"
                                    class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <!-- Icons -->
                                <label title="set Primary" class="text-gray-600 hover:text-gray-900 cursor-pointer">🔑</label>
                                <!-- Radio -->
                                <input type="radio" name="email_primary" class="form-radio accent-blue-500" value="{{$index}}" {{$email['primary'] ? 'checked' : ''}}>
                                <label title="Status" class="text-gray-600 hover:text-green-600 cursor-pointer">
                                  Status
                                </label>
                               
                                <!-- Delete Button -->
                                <button title="delete" type="button" class="delete-btn text-gray-500 hover:text-red-600 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>

                                </button>
                            </div>
                             @endforeach  
                            {{-- //else condition --}}
                            @else
                                                        <!-- Input Group Template -->
                            <div class="input-group  flex items-center gap-2 mb-2 ">
                                <input type="email" name="email_json[]" 
                                    class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                                <!-- Icons -->
                                <label title="set Primary" class="text-gray-600 hover:text-gray-900 cursor-pointer">🔑</label>
                                <!-- Radio -->
                                <input type="radio" name="email_primary" class="form-radio accent-blue-500" > 
                                <label title="Status" class="text-gray-600 hover:text-green-600 cursor-pointer">
                                  Status
                                </label>
                                <!-- Delete Button -->
                                <button title="delete" type="button" class="delete-btn text-gray-500 hover:text-red-600 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                                </button>
                            </div>
                            @endif
                            <!-- Add Button -->
                            <button type="button"  title="add"
                                class="add-input-btn mt-2 px-4 py-1 bg-blue-100 border border-blue-300 rounded text-sm hover:bg-blue-200 cursor-alias">
                                Add
                            </button>
                        </div>

                        {{-- -------multiple--number------end----- --}}
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">PAN Card</label>
                        <input type="text" value="{{ old('panCardNumber',$contacts['panCardNumber']['value'] ?? '') }}"
                            name="panCardNumber" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block mb-1 font-medium">Aadhar Card</label>
                        <input type="text" value="{{ old('aadharCardNumber', $contacts['panCardNumber']['value'] ?? '') }}"
                            name="aadharCardNumber" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Occupation</label>
                        <select name="occupation" class="w-full border rounded px-3 py-2">
                            <option value="{{ old('occupation', $contacts['occupation'] ?? '') }}">
                                {{ old('occupation', $contacts['occupation'] ?? '') }}</option>
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
                            <option value="{{ old('kycStatus', $contacts['occupation']['value'] ?? '') }}">
                                {{ old('kycStatus', $contacts['occupation']['value'] ?? '') }}</option>
                            <option value="Not Started">Not Started</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Annual Income</label>
                        <input type="text"name="anulIncome" value="{{ old('anulIncome', $contacts['occupation']['value'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Referred By Person Name</label>
                        <div class="flex">
                            <input type="text" name="referredBy"
                                value="{{ old('referredBy',$contacts['occupation']['value'] ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Total Investment</label>
                        <input type="text" name="totalInvestment"
                            value="{{ old('totalInvestment', $contacts['occupation']['value'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Comments / History</label>
                        <textarea name="comments" class="w-full border rounded px-3 py-2" rows="3">{{ old('comments', $contacts['occupation']['value'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Relationship Manager</label>
                        <div class="flex">
                            <input type="text" name="relationshipManager"
                                value="{{ old('relationshipManager', $contacts['occupation']['value'] ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" value="Mo Arfat Ansari" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Service RM</label>
                        <div class="flex">
                            <input type="text" name="serviceRM" value="{{ old('serviceRM',$contacts['occupation']['value'] ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button" class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Total SIP</label>
                        <input type="text" name="totalSIP" value="{{ old('totalSIP', $contacts['occupation']['value'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Primary Contact Person</label>
                        <div class="flex">
                            <input type="text" name="primeryContactPerson"
                                value="{{ old('primeryContactPerson',$contacts['occupation']['value'] ?? '') }}"
                                class="flex-1 border rounded-l px-3 py-2" />
                            <button type="button" class="bg-gray-100 px-3 border border-l-0">🔍</button>
                            <button type="button " class="bg-gray-100 px-3 border border-l">🗑️</button>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">Meeting Scheduled</label>
                        <select name="meetinSchedule" class="w-full border rounded px-3 py-2">
                            <option value="{{ old('meetinSchedule', $contacts['occupation']['value'] ?? '') }}">
                                {{ old('meetinSchedule', $contacts['occupation']['value'] ?? '') }}</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Weekely">Weekely</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-medium">First Meeting Date</label>
                        <input type="date" name="firstMeetingDate"
                            value="{{ old('firstMeetingDate', $contacts['occupation']['value'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" />
                    </div>


                    <!-- Personal Information -->
                    <h3 class="text-xl font-semibold mt-10 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 font-medium">Type of Relation</label>
                            <select name="typeOfRelation" class="w-full border rounded px-3 py-2">
                                <option value="{{ old('typeOfRelation', $contacts['occupation']['value'] ?? '') }}">
                                    {{ old('typeOfRelation', $contacts['occupation']['value'] ?? '') }}</option>
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



<script>
    //---crreate--multiple----Phone-NO---or----Email---address----------
$(document).ready(function () {
    // Toggle container (+ to ✖)
    $('.toggle-container-btn').on('click', function () {
        const $btn = $(this);
        const $target = $($btn.data('target'));

        if ($target.is(':visible')) {
            $target.slideUp();
            $btn.text('+');
        } else {
            $target.slideDown();
            $btn.text('✖');
        }
    });

     $('.toggle-container-input').on('click',function(){
         const $input = $(this);
        const $targetinput = $($input.data('target-input'));
       
        
           $(this).on('keyup', function () {
          const value1 = $input.val();
           $('#popupPhoneInput').val(value1);
        //    $('#popupPhoneInput').val(value1);
        });

        $('#emailInput').on('keyup', function () {
                const email = $(this).val();
                $('#popupEmailInput').val(email);
            });

        if ($targetinput.is(':visible')) {
            $targetinput.slideUp();
            // $btn.text('+');
        } else {
            $targetinput.slideDown();
            // $btn.text('✖');
        }
     });


    // Add new input group
    $('.dynamic-container').on('click', '.add-input-btn', function () {
        const $container = $(this).closest('.dynamic-container');

        // Count current input groups BEFORE adding the new one
         let count = $container.find('.input-group').length;
          // Clone the first group
        const $group = $container.find('.input-group').first().clone();

        // Clear input values
        $group.find('input[type="text"], input[type="email"]').val('');
        $group.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);
        // Append the cloned group after the last one
        $container.find('.input-group').last().after($group);
         // Update the count after adding
            let newCount = count + 1;
            $('#countNumber').text(newCount); // Display count
    });

    // Delete input group
    $('.dynamic-container').on('click', '.delete-btn', function () {
        const $container = $(this).closest('.dynamic-container');
        if ($container.find('.input-group').length > 1) {
            $(this).closest('.input-group').remove();
        }
    });
});


</script>

@endsection
