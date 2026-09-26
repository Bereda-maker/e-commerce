<x-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-brand-600 mb-6 inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            My orders
        </a>

        @php
            $statusStyle = match ($order->status) {
                'paid' => 'bg-green-100 text-green-700',
                'payment_failed' => 'bg-red-100 text-red-700',
                default => 'bg-amber-100 text-amber-700',
            };
        @endphp

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-extrabold text-ink-900">Order #{{ $order->id }}</h1>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full capitalize {{ $statusStyle }}">
                    {{ str_replace('_', ' ', $order->status) }}
                </span>
                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                    <form method="POST" action="{{ route('orders.cancel', $order) }}"
                          onsubmit="return confirm('Cancel this order? Reserved stock will be released.');">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 underline">
                            Cancel order
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white divide-y">
            @foreach ($order->items as $item)
                <div class="flex justify-between p-5">
                    <div>
                        <p class="text-sm font-medium text-ink-900">{{ $item->product_name }}</p>
                        @if ($item->variant_label)
                            <p class="text-xs text-gray-500">{{ $item->variant_label }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-semibold">{{ $item->quantity }} × ${{ number_format($item->unit_price_cents / 100, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between p-5 font-bold text-ink-900 bg-gray-50 rounded-b-2xl">
                <span>Total</span>
                <span>{{ $order->totalFormatted() }}</span>
            </div>
        </div>
    </div>
</x-layout>
