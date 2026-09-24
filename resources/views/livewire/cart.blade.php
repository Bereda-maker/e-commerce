<div>
    @error('stock')
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm" role="alert">{{ $message }}</div>
    @enderror

    @if (! $cart || $cart->isEmpty())
        <div class="text-center py-20 border border-dashed rounded-2xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <p class="text-gray-500 mb-4">Your cart is empty.</p>
            <a href="{{ route('products.index') }}" class="inline-block rounded-full bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 font-semibold text-sm transition">
                Browse products
            </a>
        </div>
    @else
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-3">
                @foreach ($cart->items as $item)
                    <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4">
                        <div class="w-16 h-16 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                            @if ($item->variant->product->image_path)
                                <img src="{{ Storage::url($item->variant->product->image_path) }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-ink-900 truncate">{{ $item->variant->product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->variant->label() }}</p>
                        </div>

                        <div class="flex items-center border border-gray-300 rounded-full">
                            <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-brand-600" aria-label="Decrease quantity">−</button>
                            <span class="w-7 text-center text-sm">{{ $item->quantity }}</span>
                            <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-brand-600" aria-label="Increase quantity">+</button>
                        </div>

                        <span class="w-20 text-right font-semibold text-sm text-ink-900">
                            ${{ number_format($item->lineTotalCents() / 100, 2) }}
                        </span>

                        <button type="button" wire:click="removeItem({{ $item->id }})"
                                aria-label="Remove {{ $item->variant->product->name }} from cart"
                                class="text-gray-400 hover:text-red-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 sticky top-24">
                    <h2 class="font-semibold text-ink-900 mb-4">Order summary</h2>
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Subtotal</span>
                        <span>${{ number_format($cart->totalCents() / 100, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 mb-4">
                        <span>Shipping</span>
                        <span class="text-green-600">Free</span>
                    </div>
                    <div class="border-t pt-4 flex justify-between font-bold text-ink-900 mb-6">
                        <span>Total</span>
                        <span>${{ number_format($cart->totalCents() / 100, 2) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" class="block text-center rounded-full bg-brand-600 hover:bg-brand-700 text-white px-5 py-3 font-semibold text-sm transition">
                        Proceed to checkout
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
