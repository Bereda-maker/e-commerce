<x-layout>
    <div class="max-w-sm mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Create an account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium mb-1">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       autocomplete="name"
                       aria-describedby="@error('name') name-error @enderror"
                       class="w-full rounded border px-3 py-2">
                @error('name')
                    <p id="name-error" class="text-sm text-red-600 mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
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
                       autocomplete="new-password"
                       aria-describedby="@error('password') password-error @enderror"
                       class="w-full rounded border px-3 py-2">
                @error('password')
                    <p id="password-error" class="text-sm text-red-600 mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password"
                       class="w-full rounded border px-3 py-2">
            </div>

            <button type="submit" class="w-full rounded bg-gray-900 text-white px-4 py-2">Register</button>
        </form>

        <p class="mt-4 text-sm text-gray-500">
            Already have an account? <a href="{{ route('login') }}" class="underline">Log in</a>
        </p>
    </div>
</x-layout>
