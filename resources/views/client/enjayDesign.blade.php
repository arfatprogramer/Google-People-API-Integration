@extends('layout.index')

@section('container')
<div class=" mx-auto px-4  sm:px-6 lg:px-8 py-6 bg-gray-50">


    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1><span class="text-gray-400">›</span><span class="text-gray-600">Google Contacts Integration</span>
        </div>
        <div class="flex items-center gap-2"><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings h-4 w-4">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg><span>Settings</span></button><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis-vertical h-4 w-4">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                </svg></button></div>
    </div>
    <!-- >>>>>>>>>>>>>>>>>>>>>>>Dispaly Cards<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between pb-2">
                <h3 class="tracking-tight text-sm font-medium">Total Contacts</h3><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-muted-foreground">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div id="contactsInCrm" class="text-2xl font-bold">0</div>
                <p class="text-xs text-green-500 text-muted-foreground">+<span id="lastSyncNewContact">0</span> from last sync</p>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="flex flex-col"><span class="text-muted-foreground">In CRM</span><span id="contactsInCrm1" class="font-medium">0</span></div>
                    <div class="flex flex-col"><span class="text-muted-foreground">In Google</span><span id="contactsInGoogle" class="font-medium">0</span></div>
                </div>
            </div>
        </div>

        <!-- Sunc Status  -->
        <div class="rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between pb-2">
                <h3 class="tracking-tight text-sm font-medium">Sync Status</h3><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-muted-foreground">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div id="processPersentage" class="text-2xl font-bold">100%</div>
                <p id="processBarText" class="text-xs text-muted-foreground"></p>
                <div class="mt-4">
                    <div aria-valuemax="100" aria-valuemin="0" role="progressbar" data-state="indeterminate" data-max="100" class="relative bg-gray-300 w-full overflow-hidden rounded-full bg-secondary h-2">
                        <div id="processBar" data-state="indeterminate" data-max="100" class="bg-green-500 h-full w-full flex-1 bg-primary transition-all"></div>
                    </div>

                </div>
                <div class="mt-2 grid grid-cols-3 gap-1 text-xs">
                    <div class="flex flex-col"><span class="text-muted-foreground">Synced</span><span id="processBarSyned" class="font-medium">0</span></div>
                    <div class="flex flex-col"><span class="text-muted-foreground">Pending</span><span id="processBarPending" class="font-medium">0</span></div>
                    <div class="flex flex-col"><span class="text-muted-foreground">Failed</span><span id="processBarErros" class="font-medium">0</span></div>
                </div>
            </div>
        </div>
        <!--  Changes Update Card -->
        <div class="rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between pb-2">
                <h3 class="tracking-tight text-sm font-medium">Changes Detected</h3><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-muted-foreground">
                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                    <path d="M2 10h20"></path>
                </svg>
            </div>
            <div class="p-6 pt-0">
                <div id="lastSyncChangesDeteted" class="text-2xl font-bold">0</div>
                <p class="text-xs text-muted-foreground">Since last sync</p>
                <div class="mt-4 grid grid-cols-3 gap-1 text-xs">
                    <div class="flex flex-col"><span class="text-muted-foreground">Added</span><span id="lastSyncNewContact1" class="font-medium">0</span></div>
                    <div class="flex flex-col"><span class="text-muted-foreground">Updated</span><span id="lastSyncUpdatedContact" class="font-medium">0</span></div>
                    <div class="flex flex-col"><span class="text-muted-foreground">Deleted</span><span id="lastSyncDeletedContact" class="font-medium">0</span></div>
                </div>
            </div>
        </div>
        <!-- Action Card -->
        <div class="rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="space-y-1.5 p-6 flex flex-row items-center justify-between pb-2">
                <h3 class="tracking-tight text-sm font-medium">Actions</h3>
            </div>
            <div class="p-6 pt-0">
                <div class="mt-0 grid grid-cols-1 gap-2">
                    <button id="syncNow" class=" syncNow cursor-pointer bg-black text-white inline-flex items-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3 w-full justify-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                            <path d="M21 3v5h-5"></path>
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                            <path d="M8 16H3v5"></path>
                        </svg>Sync Now</button>
                    <button id="pushToGoogle" class="cursor-pointer inline-flex items-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full justify-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-4 w-4">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" x2="12" y1="3" y2="15"></line>
                        </svg>Push to Google</button>
                    <button id="importFromGoogle" class="cursor-pointer inline-flex items-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full justify-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg>Import from Google</button>
                </div>
            </div>
        </div>
    </div>


    <!-- >>>>>>>>>>>>>>>>>>>>>>>Navigation Table<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->

    <div dir="ltr" data-orientation="horizontal" class="mt-6 border-gray-200">
        <div role="tablist" aria-orientation="horizontal" class="h-10 mb-6 items-center justify-center rounded-md p-1 text-muted-foreground grid w-full grid-cols-4 bg-white">
            <button type="button" role="tab" aria-selected="false" aria-controls="content-sync" class="tab-button">Sync Status</button>
            <button type="button" role="tab" aria-selected="false" aria-controls="content-history" class="tab-button text-gray-500">Sync History</button>
            <button type="button" role="tab" aria-selected="true" aria-controls="content-contacts" class="tab-button text-gray-500">Contacts</button>
            <button type="button" role="tab" aria-selected="false" aria-controls="content-settings" class="tab-button text-gray-500">Settings</button>
        </div>

        <!-- >>>>>>>>>>>>>>>>>>>>>>>Sync Contact Table  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
        <div id="content-contacts" hidden class=" tab-panel rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Contacts</h3>
                        <p class="text-sm text-muted-foreground">Manage your contacts and their sync status</p>
                    </div>
                    <div class="flex gap-2"><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-4 w-4">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" x2="12" y1="3" y2="15"></line>
                            </svg>Export</button><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>Import</button><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>Add Contact</button></div>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="mb-4 flex items-center gap-2">
                    <div class="relative flex-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg><input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-8" placeholder="Search contacts..."></div><button type="button" role="combobox" aria-controls="radix-«r1n»" aria-expanded="false" aria-autocomplete="none" dir="ltr" data-state="closed" class="flex h-10 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&amp;>span]:line-clamp-1 w-[180px]"><span style="pointer-events: none;">All Contacts</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter h-4 w-4">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg></button>
                </div>
                <div class="rounded-md border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50 text-sm">
                                <th class="w-12 px-4 py-3 text-left"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></th>
                                <th class="px-4 py-3 text-left font-medium">Name</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Phone</th>
                                <th class="px-4 py-3 text-left font-medium">Sync Status</th>
                                <th class="px-4 py-3 text-left font-medium">Last Sync</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">Troy Moises</td>
                                <td class="px-4 py-3">jerde.blake@hotmail.com</td>
                                <td class="px-4 py-3">+911294114343</td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge">Synced</div>
                                </td>
                                <td class="px-4 py-3">2 hours ago</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">Marcelo Geovany</td>
                                <td class="px-4 py-3">kelly.okon@gmail.com</td>
                                <td class="px-4 py-3">+912823449678</td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge">Synced</div>
                                </td>
                                <td class="px-4 py-3">2 hours ago</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">Sim Jeffery</td>
                                <td class="px-4 py-3">nels22@jones.org</td>
                                <td class="px-4 py-3">+911867072</td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge">Synced</div>
                                </td>
                                <td class="px-4 py-3">2 hours ago</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">Markus Johnathan</td>
                                <td class="px-4 py-3">ddaniel@ruecker.com</td>
                                <td class="px-4 py-3">+914434256390</td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge">Synced</div>
                                </td>
                                <td class="px-4 py-3">2 hours ago</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">Olive Michelle</td>
                                <td class="px-4 py-3">olive@yahoo.co.in</td>
                                <td class="px-4 py-3">+479374902740</td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700" data-v0-t="badge">Pending</div>
                                </td>
                                <td class="px-4 py-3">1 hour ago</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                            <tr class="text-sm">
                                <td class="px-4 py-3"><input class="h-4 w-4 rounded border-gray-300" type="checkbox"></td>
                                <td class="px-4 py-3 font-medium">New Contact</td>
                                <td class="px-4 py-3">aakanksha.enjay@gmail.com</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-gray-50 text-gray-700" data-v0-t="badge">Not Synced</div>
                                </td>
                                <td class="px-4 py-3">Never</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                <path d="M8 16H3v5"></path>
                                            </svg></button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-md h-8 w-8 p-0"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 6V18M18 12H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex items-center justify-between text-sm">
                    <div class="text-muted-foreground">Showing 6 of 1,248 contacts</div>
                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" disabled="">Previous</button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Next</button></div>
                </div>
            </div>
        </div>

        <!-- >>>>>>>>>>>>>>>>>>>>>>>Sync History Table Table<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
        <div id="content-history" hidden class="tab-panel rounded-lg border-gray-200 bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Sync History</h3>
                        <p class="text-sm text-muted-foreground">History of Google Contacts synchronization</p>
                    </div>
                    <div class="flex gap-2"><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter h-4 w-4">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>Filter</button><button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>Export</button></div>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="rounded-md border">
                    <table id="contact-history-table" class="contact-history-table w-full">
                        <thead>
                            <tr class="border-b bg-muted/50 text-sm">
                                <th class="px-4 py-3 text-left font-medium">Date Time</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Added</th>
                                <th class="px-4 py-3 text-left font-medium">Updated</th>
                                <th class="px-4 py-3 text-left font-medium">Deleted</th>
                                <th class="px-4 py-3 text-left font-medium">Errors</th>
                                <th class="px-4 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="mt-4 flex items-center justify-between text-sm">
                    <div class="text-muted-foreground">Showing 5 of 24 entries</div>
                    <div class="flex gap-1"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3" disabled="">Previous</button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3">Next</button></div>
                </div>
            </div>
        </div>

        <!-- >>>>>>>>>>>>>>>>>>>>>>>vContactvSync Status Table<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< -->
        <div id="content-sync" class="tab-panel rounded-lg border-gray-200 border bg-card text-card-foreground shadow-sm" data-v0-t="card">
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-semibold leading-none tracking-tight">Contact Sync Status</h3>
                        <p class="text-sm text-muted-foreground">Current status of Google Contacts synchronization</p>
                    </div>
                    <button id="refresh" class=" bg-black text-white inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                            <path d="M21 3v5h-5"></path>
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                            <path d="M8 16H3v5"></path>
                        </svg>Refresh</button>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="rounded-md border-gray-200 border">
                    <div class="flex items-center justify-between border-gray-200 border-b bg-muted/50 px-4 py-3">
                        <div class="flex items-center gap-2"><span class="font-medium">Last Sync:</span><span id="lastSyncDate">April 7, 2025 at 1:29 PM</span></div>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-3 w-3">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <path d="m9 11 3 3L22 4"></path>
                            </svg>Completed</div>
                    </div>
                    <div class="divide-y">
                        <div class="grid grid-cols-5 gap-4 px-4 py-3 border-gray-200">
                            <div class="col-span-2">
                                <div class="font-medium">Contacts in CRM</div>
                                <div class="text-sm text-muted-foreground">Total contacts in your CRM system</div>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <div id="crmTotalClientSynced" class="text-2xl font-bold">0</div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="inline-flex items-center rounded-full border  px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-3 w-3">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="m9 11 3 3L22 4"></path>
                                    </svg>Synced</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-4 px-4 py-3  border-gray-200">
                            <div class="col-span-2">
                                <div class="font-medium">Contacts in Google</div>
                                <div class="text-sm text-muted-foreground">Total contacts in Google Contacts</div>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <div id="contactsInGoogle2" class="text-2xl font-bold">0</div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-blue-50 text-blue-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-3 w-3">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg> <span id="remanigToImportFromGoogle">0</span> to import</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-4 px-4 py-3  border-gray-200">
                            <div class="col-span-2">
                                <div class="font-medium">Pending Changes on Google Contacts</div>
                                <div class="text-sm text-muted-foreground">Changes waiting to be synced</div>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <div id="pendingChangesOnGoogle" class="text-2xl font-bold">0</div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="inline-flex items-center rounded-full border  px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-3 w-3">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>Pending</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-4 px-4 py-3 border-gray-200">
                            <div class="col-span-2">
                                <div class="font-medium">Pending Changes On CRM</div>
                                <div class="text-sm text-muted-foreground">Changes waiting to be synced</div>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <div id="pendingChangesOnCRM" class="text-2xl font-bold">0</div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="inline-flex items-center rounded-full border  px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-yellow-50 text-yellow-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-3 w-3">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>Pending</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-4 px-4 py-3 " >
                            <div class="col-span-2">
                                <div class="font-medium">Sync Errors</div>
                                <div class="text-sm text-muted-foreground">Contacts that failed to sync</div>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <div id="contactsInError" class="text-2xl font-bold">0</div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 gap-1 bg-green-50 text-green-700" data-v0-t="badge"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-3 w-3">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="m9 11 3 3L22 4"></path>
                                    </svg>No Errors</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <button id="syncNow1" class="syncNow bg-black text-white inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-4 w-4">
                        </svg>Sync Now</button>
                    <!-- <div class="flex gap-2"><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">View Logs</button><button class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">Resolve All Issues</button></div> -->
                </div>
            </div>
        </div>

        <div id="content-settings" class="tab-panel" hidden>
            <!-- Settings Table -->
            <h1>Setting page</h1>
        </div>
    </div>
</div>


@endsection

@section('script')
<script type="text/javaScript">

    $('ducument').ready(function(){

        let isProcessingSync=false;
        refresh();
        SyncStatus();


        // getClinetSyncHistory Datatable
        $('.contact-history-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("ajax.getClinetSyncHistory") }}',
            },
            columns: [
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                            const date = new Date(data);
                            return date.toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                         }
                },
                { data: 'error',
                    name: 'error',
                    render:function(data){
                        if (data==0) {
                            return "Completed";
                        }
                        return "Warning";
                    }
                 },
                { data: 'created', name: 'created' },
                { data: 'updated', name: 'updated' },
                { data: 'deleted', name: 'deleted' },
                { data: 'error', name: 'error' },
                { data: 'action', name: 'action' },
            ],
        });

            // To swith table
        $('.tab-button').on('click', function() {
            // Deactivate all tabs
            $('.tab-button').attr('aria-selected', 'false');
            $('.tab-button').css('color', 'gray');

            // Hide all panels
            $('.tab-panel').attr('hidden', true);

            // Activate the clicked tab
            $(this).attr('aria-selected', 'true');
            $(this).css('color', 'black');

            // Show the corresponding panel
            var panelId = $(this).attr('aria-controls');
            $('#' + panelId).removeAttr('hidden');
        });

        // for Refreh data
        $("#refresh").click(function(){
            console.log("Refresh Button was Clicked");
            refresh()
        });


        $("#pushToGoogle").click(function(){
            console.log("pushToGoogle Button was Clicked");
                pushToGoogle()
        });

        $("#importFromGoogle").click(function(){
            console.log("importFromGoogle Button was Clicked");

                importFromGoogle();

        });

        $(".syncNow").click(function(){
                synNow();
        });



        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  Only Function Defination <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        function refresh(){
            $("#refresh").attr('disabled',true);
            $.ajax({url: "/refreshUrl",
                // method:'post',
                 success: function(result){
                    console.log(result);
                    if (result.status) {
                        $("#refresh").attr('disabled',false);
                        $('#contactsInCrm').text(result?.data?.crm);
                        $('#contactsInCrm1').text(result?.data?.crm);
                        $('#crmTotalClientSynced').text(result?.data?.crmTotalClientSynced);
                        $('#contactsInGoogle').text(result?.data?.TotalcontactInGoogle);
                        $('#contactsInGoogle1').text(result?.data?.TotalcontactInGoogle);
                        $('#contactsInGoogle2').text(result?.data?.TotalcontactInGoogle);
                        $('#pendingChangesOnCRM').text(result?.data?.pendingChangesOnCRM);
                        $('#pendingChangesOnGoogle').text(result?.data?.pendingChangesOnGoogle);
                        $('#remanigToImportFromGoogle').text(result?.data?.remanigToImportFromGoogle);
                        $('#contactsInError').text(result?.data?.lastSync?.error);
                        $('#lastSyncDate').text(timeToDateFormater(result?.data?.lastSync?.created_at));
                        $('#lastSyncNewContact').text(result?.data?.lastSync?.created);
                        $('#lastSyncNewContact1').text(result?.data?.lastSync?.created);
                        $('#lastSyncUpdatedContact').text(result?.data?.lastSync?.updated);
                        $('#lastSyncDeletedContact').text(result?.data?.lastSync?.deleted);
                        $('#lastSyncChangesDeteted').text(result?.data?.lastSyncChangesDeteted);

                        // SyncStatus();
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
                        SyncStatus();
                    }
                },
                error:function(error){
                    console.log(error);
                },

            })

        }

        function importFromGoogle(){
            console.log("importFromGoogle Started");
            $.ajax({
                url:'importFromGoogle',
                method:'get',
                success:function(response){
                    if (response.status) {

                        SyncStatus();
                    }
                },
                error:function(error){
                    console.log(error);
                },

            })

        }

        function synNow(){
            console.log("SynNow Started");
            $.ajax({
                url:'synNow',
                method:'get',
                success:function(response){
                    if (response.status) {
                        SyncStatus();
                    }
                },
                error:function(error){
                    console.log(error);
                },

            })

        }

        function SyncStatus() {
            // button disable
            $("#pushToGoogle").prop("disabled", true);
                $("#importFromGoogle").prop("disabled", true);
                $(".syncNow").prop("disabled", true);

            // // Initial state
            $("#processBar").css("width", 0 + "%");
            $("#processPersentage").text(0 + "%");

            // Set interval to fetch status
            const interval = setInterval(async () => {
                console.log("Progress Bar Running");

                $.ajax({
                    url: "syncStatus",
                    method: 'get',
                    success: function (response) {
                        if (response.status) {

                            let isProcessingSync = response.data?.isProcessing;
                            let width = response.data.porcessBarPersentage;
                            let isSynced = response.data.isSynced;

                            // Animate the progress bar width with CSS transition
                            $("#processBar").css("transition", "width 1s ease-out");
                            $("#processBarSyned").text(response.data.synced??0);
                            $("#processBarPending").text(response.data.synced??0);
                            $("#processBarErrors").text(response.data.synced??0);

                            // Change colors and text based on sync status
                            if (width == 0) {
                                $("#processBar").css("width", 100 + "%");
                                $("#processBar").css('backgroundColor', 'yellow');
                                $("#processBarText").text("Pending");
                                $("#processBarText").css('color', '#ffa225');

                            } else {
                                $("#processBar").css("width", width + "%");
                                $("#processPersentage").text(width + "%");
                                $("#processBar").css('backgroundColor', '#00C951');
                                $("#processBarText").text("Sync in Process");
                                $("#processBarText").css('color', '#00C951');
                            }

                            // Handle completion of sync
                            if (isSynced) {
                                $("#processBar").css("width", 100 + "%");
                                $("#processPersentage").text("100%");
                                $("#processBar").css('backgroundColor', '#00C951');

                                $("#processBarText").css('color', '#00C951');
                                clearInterval(interval);
                                // Btton Enable
                                $("#pushToGoogle").prop("disabled", false);
                                $("#importFromGoogle").prop("disabled", false);
                                $(".syncNow").prop("disabled", false);


                                let lastSynced = new Date(response.data.lastSync.created_at);
                                let now = new Date();

                                let diffMinutes = Math.floor((now - lastSynced) / 60000); // Difference in minutes
                                let diffHours = Math.floor(diffMinutes / 60);
                                let minutesOnly = diffMinutes % 60;

                                let text = diffHours > 0
                                    ? `Last Synced ${diffHours}h ${minutesOnly}m ago`
                                    : `Last Synced ${diffMinutes} minutes ago`;

                                $("#processBarText").text(text);

                            }

                        }
                    },
                    error: function (error) {
                        console.log("Error fetching sync status:", error);
                        clearInterval(interval);
                        SyncButtonEnableDisAble(true)
                    },
                });
            }, 4000);
        }

        // for conver from time to date
        function timeToDateFormater(data) {
            if (!data) {
                return "No Data Found"
            }
            const date = new Date(data);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

    })
</script>
@endsection
