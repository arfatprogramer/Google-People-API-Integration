<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite('resources/css/app.css')
    <!-- boot strape CDN -->
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-BDXgFqzL/EpYeT/J5XTrxR+qDB4ft42notjpwhZDEjDIzutqmXeImvKS3YPH/WJX" crossorigin="anonymous">
    <!-- Data table CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
  {{-- ------Bootstrap--icon---cdn------------------- --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Include Toastr and jQuery -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  @stack('styles')
  
    <title>CRM</title>
</head>

<body class="h-screen grid]">
    <!-- Top Navbar -->
    <nav class="bg-[#3A5199] h-12 text-white">
        <div class="relative flex items-center h-full justify-between px-10">
            <div class="flex gap-8">
            <div class="text-2xl font-bold ">CRM</div>
            <form action="#" class="bg-white border rounded-sm ">
                <input class="text-gray-600 h-full w-64 outline-none px-4" type="text" placeholder="Global Search">
                <button class="text-black px-4">search</button>
            </form>
            </div>
            <div class="flex items-center space-x-4 border-l px-4">
                <div class="w-8 h-8  bg-purple-500 text-white rounded-full flex items-center justify-center font-semibold border-2 p-1">MA</div>
                <span class=" font-sm ">Mo Arfat Ansari</span>
            </div>
        </div>
    </nav>

    <div class="flex h-[calc(100vh-75px)] relative">
        <!-- Sidebar -->
        <aside class=" w-12 bg-white shadow-lg absolute h-full overflow-hidden hover:w-fit z-50">

                            <a class="flex items-center  hover:bg-gray-200" href="{{route('client.list')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-red-500 flex items-center justify-center text-white m-2">C</span>
                                <span class="pr-4">Clinet</span>
                            </a>

                            <a class="flex items-center hover:bg-gray-200" href="{{route('client.create')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-yellow-500 flex items-center justify-center text-white m-2">C</span>
                                <span class="pr-4">Create</span>
                            </a>

                            <a class="flex items-center hover:bg-gray-200" href="{{route('client.list')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-green-500 flex items-center justify-center text-white m-2">FO</span>
                                <span class="pr-4">Family/Orgnization</span>
                            </a>

                            <a class="flex items-center hover:bg-gray-200" href="{{route('client.list')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-pink-500 flex items-center justify-center text-white m-2">R</span>
                                <span class="pr-4">Reports</span>
                            </a>

                            <a class="flex items-center hover:bg-gray-200" href="{{route('client.list')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-yellow-500 flex items-center justify-center text-white m-2">L</span>
                                <span class="pr-4">Lead</span>
                            </a>
                            <a class="flex items-center hover:bg-gray-200" href="{{route('ajax.index')}}">
                                <span class="shrink-0 h-8 w-8 rounded-full border-amber-50 bg-blue-500 flex items-center justify-center text-white m-2">GS</span>
                                <span class="pr-4">Google Sync DashBord</span>
                            </a>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-10  p-6 overflow-y-auto w-screen" >
            @yield("container")
        </main>
    </div>

    <footer class=" bg-white border-t text-center">
        <p class="text-sm font-semibold text-gray-500">Powered By <span class="text-blue-900">Sanchay CRM </span></p>
    </footer>
    @stack('scripts')

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" ></script>

    <!-- DataTable JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.2.2/cr-2.0.4/r-3.0.4/rr-1.5.0/datatables.min.js" integrity="sha384-9bYIk8wcWyHP6sGRy9fZWduNYeGcDw+PZhWc+ue0Hrt0iNDOn8OTj+YLtvuZ/dth" crossorigin="anonymous"></script>

    @yield("script")

</body>

</html>
