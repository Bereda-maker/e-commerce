<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CartComponent extends Component
{
    public function addToCart(int $variantId, int $quantity = 1): void
    {
        $variant = ProductVariant::findOrFail($variantId);

        // A quick pre-check for a responsive UI — this is NOT the
        // authoritative stock check. The real, race-condition-safe check
        // happens with lockForUpdate() inside CheckoutService::checkout()
        // at actual checkout time. This one only avoids letting someone
        // add more to their cart than currently appears available.
        if ($variant->stock_count < 1) {
            $this->addError('stock', "Sorry, {$variant->label()} just sold out.");
            return;
        }

        $cart = Auth::user()->cart()->firstOrCreate([]);

        $item = $cart->items()->firstOrNew(['product_variant_id' => $variantId]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->cart_id = $cart->id;
        $item->save();

        $this->dispatch('cart-updated');
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = CartItem::whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($itemId);

        if ($quantity < 1) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        CartItem::whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($itemId)
            ->delete();

        $this->dispatch('cart-updated');
    }

    #[On('cart-updated')]
    public function render()
    {
        $cart = Auth::user()->cart()->with('items.variant.product')->first();

        return view('livewire.cart', ['cart' => $cart]);
    }
}
