<div>
    <fieldset>
        <legend class="font-medium mb-2">Choose an option</legend>
        <div class="space-y-2" role="radiogroup" aria-label="Product variant">
            @foreach ($product->variants as $variant)
                <label class="flex items-center gap-3 rounded border px-3 py-2 {{ $variant->inStock() ? '' : 'opacity-50' }}">
                    <input type="radio" wire:model="selectedVariantId" value="{{ $variant->id }}"
                           @disabled(!$variant->inStock())>
                    <span>{{ $variant->label() }} — {{ $variant->priceFormatted() }}</span>
                    @unless ($variant->inStock())
                        <span class="text-xs text-red-600" aria-label="Out of stock">Sold out</span>
                    @endunless
                </label>
            @endforeach
        </div>
        @error('selectedVariantId')
            <p class="text-sm text-red-600 mt-2" role="alert">{{ $message }}</p>
        @enderror
    </fieldset>

    <div class="mt-4 flex items-center gap-3">
        <label for="quantity" class="text-sm">Qty</label>
        <input id="quantity" type="number" min="1" wire:model="quantity" class="w-16 rounded border px-2 py-1">
        <button type="button" wire:click="add" class="rounded bg-gray-900 text-white px-4 py-2 text-sm">
            Add to cart
        </button>
    </div>

    @if ($feedback)
        <p class="text-sm text-green-700 mt-2" role="status">{{ $feedback }}</p>
    @endif
</div>
