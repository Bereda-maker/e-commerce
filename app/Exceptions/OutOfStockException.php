<?php

namespace App\Exceptions;

use App\Models\ProductVariant;
use Exception;

class OutOfStockException extends Exception
{
    public function __construct(public readonly ProductVariant $variant, public readonly int $requested)
    {
        parent::__construct(sprintf(
            'Only %d unit(s) of "%s" (%s) left in stock, %d requested.',
            $variant->stock_count,
            $variant->product->name ?? $variant->sku,
            $variant->label(),
            $requested
        ));
    }
}
