<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        brand: {
                            50: '#fff7ed', 100: '#ffedd5', 400: '#fb923c',
                            500: '#f97316', 600: '#ea580c', 700: '#c2410c',
                        },
                        ink: { 900: '#0f172a', 800: '#1e293b', 700: '#334155' },
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body class="bg-gray-50 text-ink-900 font-sans antialiased">

    {{-- Top utility bar --}}
    <div class="bg-ink-900 text-gray-300 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center justify-between">
            <span>Free shipping on every order — no minimum.</span>
            <div class="hidden sm:flex items-center gap-4">
                @auth
                    <a href="{{ route('orders.index') }}" class="hover:text-white transition">My Orders</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Main header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-6">
            <a href="{{ route('products.index') }}" class="flex items-center gap-2 shrink-0">
                <span class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center text-white font-extrabold text-lg">{{ Str::substr(config('app.name'), 0, 1) }}</span>
                <span class="font-extrabold text-xl tracking-tight text-ink-900">{{ config('app.name') }}</span>
            </a>

            <form method="GET" action="{{ route('products.index') }}" class="flex-1 hidden md:flex">
                <div class="relative w-full max-w-xl">
                    <input type="search" name="q" value="{{ request('q') }}"
                           placeholder="Search products…"
                           class="w-full rounded-full border border-gray-300 pl-5 pr-11 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <button type="submit" aria-label="Search"
                            class="absolute right-1 top-1 bottom-1 aspect-square rounded-full bg-brand-600 text-white flex items-center justify-center hover:bg-brand-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-5 ml-auto text-sm font-medium">
                @auth
                    <div class="relative group">
                        <button class="flex items-center gap-1.5 text-ink-800 hover:text-brand-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"/>
                            </svg>
                            <span class="hidden sm:inline">{{ Str::before(auth()->user()->name, ' ') }}</span>
                        </button>
                        <div class="absolute right-0 mt-2 w-40 bg-white border rounded-lg shadow-lg py-1 hidden group-hover:block">
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">My Orders</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-gray-50">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">Log out</button>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('cart') }}" class="relative flex items-center gap-1.5 text-ink-800 hover:text-brand-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        @php $cartCount = auth()->user()->cart?->items()->sum('quantity') ?? 0; @endphp
                        @if ($cartCount > 0)
                            <span class="absolute -top-2 -right-2 w-[18px] h-[18px] min-w-[18px] px-1 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-ink-800 hover:text-brand-600 transition">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-brand-600 text-white px-4 py-2 hover:bg-brand-700 transition">Sign up</a>
                @endauth
            </div>
        </div>

        {{-- Mobile search --}}
        <form method="GET" action="{{ route('products.index') }}" class="md:hidden px-4 pb-3">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…"
                   class="w-full rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </form>
    </header>

    <main>
        @if (session('status'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6">
                <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('status') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-ink-900 text-gray-400 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-2 md:grid-cols-4 gap-10">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white font-extrabold text-sm">{{ Str::substr(config('app.name'), 0, 1) }}</span>
                    <span class="font-extrabold text-white text-lg">{{ config('app.name') }}</span>
                </div>
                <p class="text-sm leading-relaxed">Straightforward, dependable checkout — built so an item can never be sold twice.</p>
            </div>

            <div>
                <h3 class="text-white font-semibold text-sm mb-4 tracking-wide uppercase">Shop</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white transition">All products</a></li>
                    @auth
                        <li><a href="{{ route('cart') }}" class="hover:text-white transition">Your cart</a></li>
                        <li><a href="{{ route('orders.index') }}" class="hover:text-white transition">Order history</a></li>
                    @else
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Create an account</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="text-white font-semibold text-sm mb-4 tracking-wide uppercase">Support</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#" class="hover:text-white transition">Shipping &amp; returns</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact us</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-semibold text-sm mb-4 tracking-wide uppercase">Payments</h3>
                <p class="text-sm leading-relaxed">Secure checkout powered by Stripe. Card details never touch our servers.</p>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 text-xs flex flex-col sm:flex-row justify-between gap-2">
                <span>© {{ date('Y') }} {{ config('app.name') }}. Built for educational purposes.</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
