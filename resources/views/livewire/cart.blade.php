<div>
    @error('stock')
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2" role="alert">{{ $message }}</div>
    @enderror

    @if (! $cart || $cart->isEmpty())
        <p class="text-gray-500">Your cart is empty. <a href="{{ route('products.index') }}" class="underline">Browse products</a>.</p>
    @else
        <div class="divide-y rounded-lg border bg-white">
            @foreach ($cart->items as $item)
                <div class="flex items-center justify-between p-4 gap-4">
                    <div>
                        <p class="font-medium">{{ $item->variant->product->name }}</p>
                        <p class="text-sm text-gray-500">{{ $item->variant->label() }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label for="qty-{{ $item->id }}" class="sr-only">Quantity</label>
                        <input id="qty-{{ $item->id }}" type="number" min="1"
                               value="{{ $item->quantity }}"
                               wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                               class="w-16 rounded border px-2 py-1 text-sm">

                        <span class="w-20 text-right text-sm">
                            ${{ number_format($item->lineTotalCents() / 100, 2) }}
                        </span>

                        <button type="button" wire:click="removeItem({{ $item->id }})"
                                aria-label="Remove {{ $item->variant->product->name }} from cart"
                                class="text-sm text-red-600">
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-lg font-semibold">Total: ${{ number_format($cart->totalCents() / 100, 2) }}</p>
            <a href="{{ route('checkout') }}" class="rounded bg-gray-900 text-white px-5 py-2.5 text-sm">
                Proceed to checkout
            </a>
        </div>
    @endif
</div>
