<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>ToS Papaya Patch Notes - Login</title>
    <link rel="icon" href="{{ asset('images/optimizing.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<nav class="bg-white shadow p-4 mb-6">
    <div class="container mx-auto flex justify-between">
    <a href="{{ route('patch-notes.index') }}" class="font-bold text-lg hover:underline">
        ToS Papaya Patch Notes
    </a>
    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-600 hover:underline">Sair</button>
        </form>
    @endauth
    </div>
</nav>

<div class="container mx-auto">
    @yield('content')
</div>
</body>
</html>
