<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>login</title>
    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-6xl bg-white rounded-xl shadow-lg overflow-hidden grid grid-cols-1 md:grid-cols-2">

            <!-- Left: Login Form -->
            <div class="p-10">
                <h2 class="text-2xl font-semibold text-gray-800 mb-8">Welcome To Sanchay CRM</h2>
                <form id="login-form">
                    <div class="mb-5">
                        <label class="block text-gray-700 mb-1" for="email">Username / Email</label>
                        <input id="user_name" name="user_name"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <span id="userError" class="text-red-500"></span>
                    </div>
                    <div class="mb-5">
                        <label class="block text-gray-700 mb-1" for="password">Password</label>
                        <div class="relative">
                            <input id="password" type="password"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10" />
                            <span id="passwordError" class="text-red-500"></span>
                        </div>
                    </div>
                    <div class="mb-5 flex items-center">
                        <input id="remember" type="checkbox" class="mr-2" />
                        <label for="remember" class="text-sm text-gray-600">Remember Me</label>
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-700 text-white py-2 rounded-lg hover:bg-blue-800 transition cursor-pointer">Sign
                        In</button>
                </form>
                <p class="text-xs text-center text-gray-400 mt-6">v94.1.44</p>
            </div>

            <!-- Right: Carousel Info -->
            <div class="bg-blue-700 text-white flex items-center justify-center p-10 relative">
                <div class="bg-white text-gray-800 rounded-lg p-6 max-w-md shadow-md text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/6062/6062646.png" alt="CRM Illustration"
                        class="w-24 mx-auto mb-4" />
                    <h3 class="font-semibold text-lg mb-2">Centralize your lead via integration of different lead
                        sources with CRM</h3>
                    <p class="text-sm text-gray-600">
                        "Integrate CRM with IndiaMART, Just Dial, Facebook, and more to centralize leads effortlessly.
                        Maximize efficiency and streamline your workflow."
                    </p>
                    <!-- Dots -->
                    <div class="flex justify-center mt-4 space-x-2">
                        <span class="w-2 h-2 bg-blue-700 rounded-full"></span>
                        <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                        <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                    </div>
                </div>
                <!-- Arrows -->
                <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-2xl cursor-pointer">‹
                </div>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-2xl cursor-pointer">›
                </div>
            </div>
        </div>
    </div>
    // <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                // alert('hello');
                $.ajax({
                    url: '/crmlogin',
                    type: 'POST',
                    data: {
                        user_name: $('#user_name').val(),
                        password: $('#password').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        // $('#login-message').text(res.message).css('color', 'green');
                        console.log(res.data);
                        console.log(res.message);
                        window.location.href = '/'; // Your internal redirect
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};

                        // Clear previous error messages
                        $('#userError').text('');
                        $('#passwordError').text('');

                        if (errors.user_name) {
                            $('#userError').text(errors.user_name[0]);
                        }

                        if (errors.password) {
                            $('#passwordError').text(errors.password[0]);
                        }

                        const msg = xhr.responseJSON?.message || 'Login failed.';
                        console.log(msg);
                    }

                });
            });
        });
    </script>

</body>

</html>
