<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddToCartForm extends Component
{
    public Product $product;

    public ?int $selectedVariantId = null;

    public int $quantity = 1;

    public ?string $feedback = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->selectedVariantId = $product->variants->firstWhere('stock_count', '>', 0)?->id;
    }

    public function add(): void
    {
        $this->feedback = null;

        if (! $this->selectedVariantId) {
            $this->addError('selectedVariantId', 'Please choose an option.');
            return;
        }

        // Pre-check only, for a responsive UI — not the authoritative
        // stock check. See CartComponent::addToCart() and
        // CheckoutService::checkout() for the real, race-safe check.
        $variant = ProductVariant::find($this->selectedVariantId);
        if (! $variant || $variant->stock_count < $this->quantity) {
            $this->addError('selectedVariantId', 'Sorry, that option just sold out.');
            return;
        }

        $cart = Auth::user()->cart()->firstOrCreate([]);
        $item = $cart->items()->firstOrNew(['product_variant_id' => $this->selectedVariantId]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $this->quantity;
        $item->cart_id = $cart->id;
        $item->save();

        $this->feedback = 'Added to cart.';
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.add-to-cart-form');
    }
}
