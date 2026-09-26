<x-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-extrabold text-ink-900 mb-8">Your wishlist</h1>

        @if ($favorites->isEmpty())
            <div class="text-center py-20 border border-dashed rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/></svg>
                <p class="text-gray-500 mb-4">Nothing saved yet.</p>
                <a href="{{ route('products.index') }}" class="inline-block rounded-full bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 font-semibold text-sm transition">
                    Browse products
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($favorites as $favorite)
                    @php $product = $favorite->product; @endphp
                    @if ($product)
                        <a href="{{ route('products.show', $product) }}"
                           class="group block rounded-xl border border-gray-200 bg-white overflow-hidden hover:shadow-lg transition-all duration-200">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                @if ($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3.5">
                                <h3 class="text-sm font-semibold text-ink-900 truncate group-hover:text-brand-600 transition">{{ $product->name }}</h3>
                                @if ($product->variants->isNotEmpty())
                                    <span class="text-brand-600 font-bold text-sm">${{ number_format($product->variants->min('price_cents') / 100, 2) }}</span>
                                @endif
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="mt-10">{{ $favorites->links() }}</div>
        @endif
    </div>
</x-layout>
