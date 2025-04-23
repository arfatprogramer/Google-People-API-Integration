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
                <p id="processPersentage" class="text-2xl font-bold">92%</p>
                <p class="text-xs text-gray-400">Last synced 23 minutes ago</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="processBar" class="bg-green-500 h-2 rounded-full" style="width: 92%"></div>
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
                <button id="pushToGoogle" class="bg-white border w-full py-2 rounded-lg text-sm flex justify-center items-center gap-2">⬆️ Push to Google</button>
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
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">Contact Sync Status</h2>
                        <p class="text-sm text-gray-500">Current status of Google Contacts synchronization</p>
                    </div>
                    <button id="refresh" class="flex items-center px-4 py-2 text-sm font-medium text-white bg-black rounded hover:bg-gray-800">
                        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <!-- Refresh Icon -->
                        </svg>
                        Refresh
                    </button>
                </div>


                <div class="bg-white border rounded shadow-sm divide-y">
                    <div class="flex justify-between items-center p-4">
                        <span class="text-gray-500">Last Sync:</span>
                        <span id="lastSync" class="text-sm font-medium">April 7, 2025 at 1:29 PM</span>
                        <span class="ml-2 text-green-600 text-sm font-semibold flex items-center">
                            ✔ Completed
                        </span>
                    </div>

                    <div class="flex justify-between items-center p-4">
                        <span>Contacts in CRM</span>
                        <span id="contactsInCrm" class="font-bold text-lg">1,248</span>
                        <span class="text-green-500 font-semibold text-sm">✔ Synced</span>
                    </div>

                    <div class="flex justify-between items-center p-4">
                        <span>Contacts in Google</span>
                        <span id="contactsInGoogle" class="font-bold text-lg">1,356</span>
                        <span class="text-blue-600 font-semibold text-sm">⏱ 108 to import</span>
                    </div>

                    <div class="flex justify-between items-center p-4">
                        <span>Pending Changes</span>
                        <span id="contactsInPending" class="font-bold text-lg">42</span>
                        <span class="text-yellow-500 font-semibold text-sm">🕒 Pending</span>
                    </div>

                    <div class="flex justify-between items-center p-4">
                        <span>Sync Errors</span>
                        <span id="contactsInError" class="font-bold text-lg ">0</span>
                        <span class="text-green-500 font-semibold text-sm">✔ No Errors</span>
                    </div>
                </div>
                <div class="mt-4 flex justify-between">
                    <button class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                        🔄 Sync Now
                    </button>

                    <div class="space-x-2">
                        <button class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                            View Logs
                        </button>
                        <button class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
                            Resolve All Issues
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


</div>

</div>
@endsection

@section('script')
<script type="text/javaScript">

    $('ducument').ready(function(){

        let isProcessingSync=false;

        // for Refreh data
        $("#refresh").click(function(){
            console.log("Refresh Button was Clicked");
            refresh()
        });

        $("#pushToGoogle").click(function(){
            console.log("pushToGoogle Button was Clicked");
            if (isProcessingSync) {
                console.log("Process is On Gioning");
            }else{
                isProcessingSync=true;
                pushToGoogle()
            }
        });


        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  Only Function Defination <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        function refresh(){
            $.ajax({url: "/refreshUrl",
                // method:'post',
                 success: function(result){
                    console.log(result);
                    if (result.status) {
                        $('#contactsInCrm').text(result?.data?.crm);
                        $('#contactsInGoogle').text(result?.data?.google);
                        $('#contactsInPending').text(result?.data?.pending);
                        $('#contactsInError').text(result?.data?.error);
                        $('#lastSync').text(result?.data?.lastSync);
                        console.log($('#contactsInCrm').val());

                    }
                },
                error:function(error){
                    console.log(error);
                },
            });
        }

        function pushToGoogle(){
            console.log("pushToGoogle Started");
            $.ajax({
                url:'pushToGoogle',
                method:'get',
                success:function(response){
                    if (response.status) {
                        isProcessingSync=response.data?.isProcessing
                        console.log(response);
                        SyncStatus();
                    }
                },
                error:function(error){
                    console.log(error);
                },

            })

        }

        function SyncStatus() {
            let count = 0;

            const interval = setInterval(() => {
                console.log("Progress Bar Running");
                count++;
                $.ajax({
                    url:"syncStatus",
                    method:'get',
                    success:function(response){
                        if (response.status) {
                            isProcessingSync=response.data?.isProcessing
                            console.log(response);
                        }
                    },
                    error:function(error){
                        console.log(error);
                    },

                })
                $("#processBar").css("width",(count*2)+"%");
                $("#processPersentage").text((count*2)+"%");

                if (count >= 50) {
                    clearInterval(interval);
                    isProcessingSync=false;
                    console.log("Progress Bar Stopped");
                }
            }, 1000);
        }


    })
</script>
@endsection
