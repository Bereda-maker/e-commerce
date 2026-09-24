<x-layout>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div>
            @if ($product->image_path)
                <img src="{{ Storage::url($product->image_path) }}"
                     alt="{{ $product->name }}"
                     class="w-full rounded-lg border">
            @else
                <div class="w-full aspect-square rounded-lg border bg-gray-100 flex items-center justify-center text-gray-400">
                    No image
                </div>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-semibold">{{ $product->name }}</h1>
            <p class="mt-3 text-gray-600">{{ $product->description }}</p>

            <div class="mt-6">
                @auth
                    @livewire('add-to-cart-form', ['product' => $product])
                @else
                    <p>
                        <a href="{{ route('login') }}" class="underline">Log in</a> to add this to your cart.
                    </p>
                @endauth
            </div>
        </div>
    </div>
</x-layout>
