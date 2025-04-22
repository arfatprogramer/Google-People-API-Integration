@extends('layout.index')

@section('container')
<div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Create Client</h2>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
          <ul class="flex space-x-6 text-blue-600 font-medium">
            <li class="border-b-2 border-blue-600 pb-2">Overview</li>
            <li class="text-gray-500 cursor-pointer hover:text-blue-600">Address Information</li>
          </ul>
        </div>

        <!-- Form -->
        <form action="/create" method="post"class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
          <div>
            <label class="block mb-1 font-medium">First Name</label>
            <input name="firstName" type="text" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Last Name</label>
            <input name="lastName" type="text" class="w-full border rounded px-3 py-2" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Phone</label>
            <div class="flex">
              <input type="text" name="number" class="flex-1 border rounded-l px-3 py-2"  />
              <button class="bg-gray-100 px-4 border border-l-0 rounded-r">+</button>
            </div>
          </div>
          <div>
            <label class="block mb-1 font-medium">Family / Organisation Name</label>
            <input name="familyOrOrgnization" type="text" class="w-full border rounded px-3 py-2" />
          </div>


          <div>
            <label class="block mb-1 font-medium">Email</label>
            <div class="flex">
              <input type="text" name="email" class="flex-1 border rounded-l px-3 py-2" />
              <button class="bg-gray-100 px-4 border border-l-0 rounded-r">+</button>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">PAN Card</label>
            <input type="text" name="panCardNumber" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block mb-1 font-medium">Aadhar Card</label>
            <input type="text" name="aadharCardNumber" class="w-full border rounded px-3 py-2" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Occupation</label>
            <select name="occupation" class="w-full border rounded px-3 py-2">
              <option>Select</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium">KYC Status</label>
            <select name="kycStatus" class="w-full border rounded px-3 py-2">
              <option>Select</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium">Annual Income</label>
            <input type="text"name="anulIncome" class="w-full border rounded px-3 py-2" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Referred By Person Name</label>
            <div class="flex">
              <input type="text" name="referredBy" class="flex-1 border rounded-l px-3 py-2" />
              <button class="bg-gray-100 px-3 border border-l-0">🔍</button>
              <button class="bg-gray-100 px-3 border border-l">🗑️</button>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Total Investment</label>
            <input type="text" name="totalInvestment" class="w-full border rounded px-3 py-2" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Comments / History</label>
            <textarea name="comments" class="w-full border rounded px-3 py-2" rows="3"></textarea>
          </div>

          <div>
            <label class="block mb-1 font-medium">Relationship Manager</label>
            <div class="flex">
              <input type="text" name="relationshipManager" class="flex-1 border rounded-l px-3 py-2" value="Mo Arfat Ansari" />
              <button class="bg-gray-100 px-3 border border-l-0">🔍</button>
              <button class="bg-gray-100 px-3 border border-l">🗑️</button>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Service RM</label>
            <div class="flex">
              <input type="text" name="serviceRM" class="flex-1 border rounded-l px-3 py-2" />
              <button class="bg-gray-100 px-3 border border-l-0">🔍</button>
              <button class="bg-gray-100 px-3 border border-l">🗑️</button>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Total SIP</label>
            <input type="text" name="totalSIP" class="w-full border rounded px-3 py-2" />
          </div>

          <div>
            <label class="block mb-1 font-medium">Primary Contact Person</label>
            <div class="flex">
              <input type="text" name="primeryContactPerson" class="flex-1 border rounded-l px-3 py-2" />
              <button class="bg-gray-100 px-3 border border-l-0">🔍</button>
              <button class="bg-gray-100 px-3 border border-l">🗑️</button>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Meeting Scheduled</label>
            <select name="meetinSchedule" class="w-full border rounded px-3 py-2">
              <option>Select</option>
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium">First Meeting Date</label>
            <input type="date" name="firstMeetingDate" class="w-full border rounded px-3 py-2" />
          </div>


        <!-- Personal Information -->
        <h3 class="text-xl font-semibold mt-10 mb-4">Personal Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block mb-1 font-medium">Type of Relation</label>
            <select name="typeOfRelation" class="w-full border rounded px-3 py-2">
              <option>Select</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 font-medium">Marital Status</label>
            <select name="maritalStatus" class="w-full border rounded px-3 py-2">
              <option>Select</option>
            </select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end mt-8 space-x-4">
          <button class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
          <button class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
        </div>
        </form>
      </div>
@endsection

