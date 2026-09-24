<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        // eager-load variants to avoid an N+1 query per product card —
        // one query for products, one for all their variants, instead of
        // one variants query per product rendered. See README "Performance".
        $products = Product::query()
            ->where('is_active', true)
            ->with('variants')
            ->latest()
            ->paginate(24);

        return view('products.index', ['products' => $products]);
    }

    public function show(Product $product): View
    {
        $product->load('variants');

        return view('products.show', ['product' => $product]);
    }
}
