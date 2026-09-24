<x-layout>
    <h1 class="text-2xl font-semibold mb-6">Shop</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($products as $product)
            <a href="{{ route('products.show', $product) }}"
               class="block rounded-lg border bg-white overflow-hidden hover:shadow-md transition">
                @if ($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}"
                         alt="{{ $product->name }}"
                         class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">
                        No image
                    </div>
                @endif
                <div class="p-4">
                    <h2 class="font-medium">{{ $product->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $product->totalStock() > 0 ? 'In stock' : 'Sold out' }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</x-layout>
