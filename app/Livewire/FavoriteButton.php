<?php

namespace App\Livewire;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Product $product;

    public bool $isFavorited = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->isFavorited = Auth::user()->hasFavorited($product);
    }

    public function toggle(): void
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $this->isFavorited = false;
        } else {
            Favorite::create(['user_id' => Auth::id(), 'product_id' => $this->product->id]);
            $this->isFavorited = true;
        }
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }
}
