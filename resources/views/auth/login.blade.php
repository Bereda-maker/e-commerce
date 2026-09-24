<x-layout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <span class="inline-flex w-11 h-11 rounded-xl bg-brand-600 items-center justify-center text-white font-extrabold text-xl mb-4">{{ Str::substr(config('app.name'), 0, 1) }}</span>
                <h1 class="text-2xl font-extrabold text-ink-900">Welcome back</h1>
                <p class="text-sm text-gray-500 mt-1">Log in to continue shopping</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4 bg-white border border-gray-200 rounded-2xl p-7">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-ink-800 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           autocomplete="username"
                           aria-describedby="@error('email') email-error @enderror"
                           class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('email')
                        <p id="email-error" class="text-sm text-red-600 mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-ink-800 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required
                           autocomplete="current-password"
                           aria-describedby="@error('password') password-error @enderror"
                           class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('password')
                        <p id="password-error" class="text-sm text-red-600 mt-1.5" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"> Remember me
                </label>

                <button type="submit" class="w-full rounded-full bg-brand-600 hover:bg-brand-700 text-white px-4 py-3 font-semibold text-sm transition">
                    Log in
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                No account? <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:text-brand-700">Register</a>
            </p>
        </div>
    </div>
</x-layout>
