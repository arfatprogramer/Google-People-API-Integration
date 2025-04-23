@extends('layout.index')

@section('container')
<div class=" mx-auto px-4 sm:px-6 lg:px-8 py-6">



    <div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold">Dashboard &rsaquo; <span class="text-gray-500">Google Contacts Integration</span></h1>
      <button class="bg-gray-100 px-4 py-2 rounded shadow-sm text-sm">⚙️ Settings</button>
    </div>

    <!-- Total Contacts -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-xl shadow">
        <h2 class="text-sm text-gray-500">Total Contacts</h2>
        <p class="text-2xl font-bold">1,248</p>
        <p class="text-xs text-green-500">+156 from last sync</p>
        <div class="mt-2 text-sm text-gray-500">
          <p>In CRM: <strong>1,248</strong></p>
          <p>In Google: <strong>1,356</strong></p>
        </div>
      </div>

      <!-- Sync Status -->
      <div class="bg-white p-5 rounded-xl shadow">
        <h2 class="text-sm text-gray-500">Sync Status</h2>
        <p class="text-2xl font-bold">92%</p>
        <p class="text-xs text-gray-400">Last synced 23 minutes ago</p>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
          <div class="bg-green-500 h-2 rounded-full" style="width: 92%"></div>
        </div>
        <div class="flex justify-between text-xs mt-1 text-gray-500">
          <span>Synced: 1,152</span>
          <span>Pending: 96</span>
          <span>Failed: 0</span>
        </div>
      </div>

      <!-- Changes Detected -->
      <div class="bg-white p-5 rounded-xl shadow">
        <h2 class="text-sm text-gray-500">Changes Detected</h2>
        <p class="text-2xl font-bold">42</p>
        <p class="text-xs text-gray-400">Since last sync</p>
        <div class="mt-2 text-sm text-gray-500">
          <p>Added: <strong>18</strong></p>
          <p>Updated: <strong>24</strong></p>
          <p>Deleted: <strong>0</strong></p>
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-white p-5 rounded-xl shadow space-y-2">
        <h2 class="text-sm text-gray-500">Actions</h2>
        <button class="bg-black text-white w-full py-2 rounded-lg text-sm font-medium">Sync Now</button>
        <button class="bg-white border w-full py-2 rounded-lg text-sm flex justify-center items-center gap-2">⬆️ Push to Google</button>
        <button class="bg-white border w-full py-2 rounded-lg text-sm flex justify-center items-center gap-2">⬇️ Import from Google</button>
      </div>
    </div>

    <div>
      <div class="border-b mb-4">
        <nav class="flex space-x-6 text-sm">
          <a href="#" class="text-black font-medium border-b-2 border-black py-2">Sync Status</a>
          <a href="#" class="text-gray-500 hover:text-black py-2">Sync History</a>
          <a href="#" class="text-gray-500 hover:text-black py-2">Contacts</a>
          <a href="#" class="text-gray-500 hover:text-black py-2">Settings</a>
        </nav>
      </div>

      <div class="bg-white rounded-xl shadow p-6 space-y-6">
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold">Contact Sync Status</h3>
            <p class="text-sm text-gray-500">Current status of Google Contacts synchronization</p>
          </div>
          <button class="bg-black text-white px-4 py-2 rounded shadow text-sm">🔄 Refresh</button>
        </div>

        <div class="space-y-4 text-sm">
          <div class="flex justify-between items-center border-b pb-2">
            <span>Last Sync:</span>
            <span class="font-medium">April 7, 2025 at 1:29 PM</span>
            <span class="text-green-600 font-semibold">🟢 Completed</span>
          </div>

          <div class="flex justify-between items-center border-b pb-2">
            <span>Contacts in CRM</span>
            <span class="font-medium">1,248</span>
            <span class="text-green-600 font-semibold">✅ Synced</span>
          </div>

          <div class="flex justify-between items-center border-b pb-2">
            <span>Contacts in Google</span>
            <span class="font-medium">1,356</span>
            <span class="text-blue-600 font-semibold">🔄 108 to import</span>
          </div>

          <div class="flex justify-between items-center border-b pb-2">
            <span>Pending Changes</span>
            <span class="font-medium">42</span>
            <span class="text-yellow-500 font-semibold">🕒 Pending</span>
          </div>

          <div class="flex justify-between items-center">
            <span>Sync Errors</span>
            <span class="font-medium">0</span>
            <span class="text-green-600 font-semibold">✅ No Errors</span>
          </div>
        </div>
      </div>
    </div>
  </div>


</div>






</div>
@endsection
