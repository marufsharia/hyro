<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Hyro</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @hyroAssets
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        .hyro-auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hyro-auth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body class="antialiased">
<div class="hyro-auth-container">
    <div class="hyro-auth-card p-8">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="text-2xl font-bold text-gray-800">
                Hyro<span class="text-purple-600">Auth</span>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('hyro.password.email') }}">
            @csrf

            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email Address
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="you@example.com">
            </div>

            <!-- Submit Button -->
            <div class="mb-4">
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Send Reset Link
                </button>
            </div>

            <!-- Back to Login -->
            @if (Route::has('hyro.login'))
                <div class="text-center">
                    <a href="{{ route('hyro.login') }}" class="text-sm font-medium text-purple-600 hover:text-purple-500">
                        Back to login
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>
</body>
</html>
