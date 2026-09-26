<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <nav class="text-xs text-gray-500 mb-6 flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="hover:text-brand-600">Shop</a>
            <span>/</span>
            <span class="text-ink-800">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <div class="aspect-square rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden">
                    @if ($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <div class="flex items-start justify-between gap-4 mb-2">
                    @php $inStock = $product->totalStock() > 0; @endphp
                    <span class="inline-block text-xs font-bold tracking-wide uppercase {{ $inStock ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $inStock ? 'In stock' : 'Currently unavailable' }}
                    </span>
                    @auth
                        @livewire('favorite-button', ['product' => $product])
                    @endauth
                </div>
                <h1 class="text-3xl font-extrabold text-ink-900 mb-4">{{ $product->name }}</h1>

                @if ($product->variants->isNotEmpty())
                    <p class="text-brand-600 text-2xl font-bold mb-6">
                        ${{ number_format($product->variants->min('price_cents') / 100, 2) }}
                    </p>
                @endif

                @if ($product->description)
                    <p class="text-gray-600 leading-relaxed mb-8">{{ $product->description }}</p>
                @endif

                <div class="border-t pt-8">
                    @auth
                        @livewire('add-to-cart-form', ['product' => $product])
                    @else
                        <a href="{{ route('login') }}" class="inline-block rounded-full bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 font-semibold transition">
                            Log in to purchase
                        </a>
                    @endauth
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4 text-xs text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Fast dispatch
                    </div>
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Secure checkout
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
