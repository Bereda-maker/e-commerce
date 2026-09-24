<x-layout>
    <div class="max-w-sm mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Log in</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username"
                       aria-describedby="@error('email') email-error @enderror"
                       class="w-full rounded border px-3 py-2">
                @error('email')
                    <p id="email-error" class="text-sm text-red-600 mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       autocomplete="current-password"
                       aria-describedby="@error('password') password-error @enderror"
                       class="w-full rounded border px-3 py-2">
                @error('password')
                    <p id="password-error" class="text-sm text-red-600 mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Remember me
            </label>

            <button type="submit" class="w-full rounded bg-gray-900 text-white px-4 py-2">Log in</button>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            No account? <a href="{{ route('register') }}" class="underline">Register</a>
        </p>
    </div>
</x-layout>
