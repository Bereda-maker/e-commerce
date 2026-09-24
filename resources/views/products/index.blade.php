<x-layout>
    @if ($search === '')
        {{-- Hero --}}
        <section class="bg-gradient-to-br from-ink-900 via-ink-800 to-ink-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24 grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="inline-block text-brand-400 text-xs font-bold tracking-widest uppercase mb-4">New arrivals every week</span>
                    <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-5">
                        Shop with confidence.<br class="hidden sm:block"> Stock that's actually there.
                    </h1>
                    <p class="text-gray-300 text-lg mb-8 max-w-md">
                        Real-time inventory, secure checkout, and orders you can track — from browse to doorstep.
                    </p>
                    <a href="#catalog" class="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 transition px-7 py-3.5 font-semibold">
                        Shop now
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
                <div class="hidden md:flex justify-center">
                    <div class="w-72 h-72 rounded-full bg-brand-600/20 flex items-center justify-center">
                        <div class="w-52 h-52 rounded-full bg-brand-600/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Promo strip --}}
        <section class="border-b bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 grid sm:grid-cols-3 gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 3 7v6c0 5 4 9 9 9s9-4 9-9V7l-9-5Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Buyer protection</p>
                        <p class="text-xs text-gray-500">Stock reserved the moment you check out</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Fast dispatch</p>
                        <p class="text-xs text-gray-500">Orders confirmed and processed instantly</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Secure payment</p>
                        <p class="text-xs text-gray-500">Card details handled entirely by Stripe</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-ink-900">
                    {{ $search !== '' ? 'Results for "'.$search.'"' : 'Shop the catalog' }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}</p>
            </div>
            @if ($search !== '')
                <a href="{{ route('products.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Clear search</a>
            @endif
        </div>

        @if ($products->isEmpty())
            <div class="text-center py-24 text-gray-400">
                <p class="text-lg">No products found.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    @php
                        $inStock = $product->totalStock() > 0;
                        $isNew = $product->created_at->gt(now()->subDays(14));
                        $cheapestPrice = $product->variants->min('price_cents');
                    @endphp
                    <a href="{{ route('products.show', $product) }}"
                       class="group block rounded-xl border border-gray-200 bg-white overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <div class="relative aspect-square bg-gray-100 overflow-hidden">
                            @if ($product->image_path)
                                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                </div>
                            @endif

                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                @if ($isNew)
                                    <span class="bg-brand-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">NEW</span>
                                @endif
                                @unless ($inStock)
                                    <span class="bg-gray-700 text-white text-[10px] font-bold px-2 py-0.5 rounded">SOLD OUT</span>
                                @endunless
                            </div>
                        </div>

                        <div class="p-3.5">
                            <h3 class="text-sm font-semibold text-ink-900 truncate group-hover:text-brand-600 transition">{{ $product->name }}</h3>
                            <div class="flex items-center justify-between mt-1.5">
                                @if ($cheapestPrice)
                                    <span class="text-brand-600 font-bold text-sm">${{ number_format($cheapestPrice / 100, 2) }}</span>
                                @endif
                                <span class="text-[11px] {{ $inStock ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $inStock ? 'In stock' : 'Unavailable' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</x-layout>
