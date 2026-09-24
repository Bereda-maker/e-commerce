<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        // eager-load variants to avoid an N+1 query per product card —
        // one query for products, one for all their variants, instead of
        // one variants query per product rendered. See README "Performance".
        $search = $request->string('q')->trim()->toString();

        $products = Product::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->with('variants')
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('products.index', ['products' => $products, 'search' => $search]);
    }

    public function show(Product $product): View
    {
        $product->load('variants');

        return view('products.show', ['product' => $product]);
    }
}
