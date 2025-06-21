@extends('layouts.login')

@section('content')
    <div class="container mx-auto max-w-md mt-20">
        <h2 class="text-2xl font-bold mb-6">Login</h2>

        @if (session('error'))
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block font-medium">Email</label>
                <input type="email" name="email" id="email" required
                       class="w-full border p-2 rounded mt-1" value="{{ old('email') }}">
            </div>

            <div class="mb-6">
                <label for="password" class="block font-medium">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full border p-2 rounded mt-1">
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Enter
            </button>
        </form>
    </div>
@endsection
