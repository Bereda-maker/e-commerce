<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'size', 'color', 'stock_count', 'price_cents'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function label(): string
    {
        return collect([$this->size, $this->color])->filter()->implode(' / ') ?: $this->sku;
    }

    public function priceFormatted(): string
    {
        return '$'.number_format($this->price_cents / 100, 2);
    }

    public function inStock(): bool
    {
        return $this->stock_count > 0;
    }
}
