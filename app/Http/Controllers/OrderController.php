<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()->orders()->with('items')->paginate(15);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        // Ownership check: a buyer can only view their own orders, not
        // guess another user's order id — the same "check ownership
        // server-side on every read/write" discipline this book applies
        // to Project 6's employer-owns-listing checks.
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('items');

        return view('orders.show', ['order' => $order]);
    }
}
