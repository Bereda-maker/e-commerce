<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $favorites = Auth::user()->favorites()->with('product.variants')->latest()->paginate(24);

        return view('favorites.index', ['favorites' => $favorites]);
    }
}
