<div>
    <fieldset>
        <legend class="text-sm font-semibold text-ink-900 mb-3">Choose an option</legend>
        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Product variant">
            @foreach ($product->variants as $variant)
                <label class="cursor-pointer">
                    <input type="radio" wire:model="selectedVariantId" value="{{ $variant->id }}"
                           class="sr-only peer" @disabled(!$variant->inStock())>
                    <span class="block text-sm px-4 py-2 rounded-full border-2 border-gray-200 peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed transition">
                        {{ $variant->label() }}
                        @unless ($variant->inStock())
                            <span class="text-red-500">· sold out</span>
                        @endunless
                    </span>
                </label>
            @endforeach
        </div>
        @error('selectedVariantId')
            <p class="text-sm text-red-600 mt-2" role="alert">{{ $message }}</p>
        @enderror
    </fieldset>

    <div class="mt-5 flex items-center gap-3">
        <div class="flex items-center border border-gray-300 rounded-full">
            <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                    class="w-9 h-9 flex items-center justify-center text-gray-600 hover:text-brand-600" aria-label="Decrease quantity">−</button>
            <span class="w-8 text-center text-sm font-medium" aria-live="polite">{{ $quantity }}</span>
            <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})"
                    class="w-9 h-9 flex items-center justify-center text-gray-600 hover:text-brand-600" aria-label="Increase quantity">+</button>
        </div>

        <button type="button" wire:click="add" wire:loading.attr="disabled"
                class="flex-1 rounded-full bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 font-semibold transition disabled:opacity-60">
            <span wire:loading.remove wire:target="add">Add to cart</span>
            <span wire:loading wire:target="add">Adding…</span>
        </button>
    </div>

    @if ($feedback)
        <p class="text-sm text-green-700 mt-3 flex items-center gap-1.5" role="status">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
            {{ $feedback }}
        </p>
    @endif
</div>
