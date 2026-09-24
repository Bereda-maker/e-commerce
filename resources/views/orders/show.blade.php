<x-layout>
    <h1 class="text-2xl font-semibold mb-2">Order #{{ $order->id }}</h1>
    <p class="text-gray-500 mb-6 capitalize">Status: {{ str_replace('_', ' ', $order->status) }}</p>

    <div class="divide-y rounded-lg border bg-white">
        @foreach ($order->items as $item)
            <div class="flex justify-between p-4">
                <div>
                    <p>{{ $item->product_name }}</p>
                    @if ($item->variant_label)
                        <p class="text-sm text-gray-500">{{ $item->variant_label }}</p>
                    @endif
                </div>
                <span>{{ $item->quantity }} × ${{ number_format($item->unit_price_cents / 100, 2) }}</span>
            </div>
        @endforeach
        <div class="flex justify-between p-4 font-semibold">
            <span>Total</span>
            <span>{{ $order->totalFormatted() }}</span>
        </div>
    </div>
</x-layout>
