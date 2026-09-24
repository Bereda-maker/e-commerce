<x-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-extrabold text-ink-900 mb-8">My orders</h1>

        @if ($orders->isEmpty())
            <div class="text-center py-20 border border-dashed rounded-2xl">
                <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" class="inline-block rounded-full bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 font-semibold text-sm transition">
                    Browse products
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white divide-y">
                @foreach ($orders as $order)
                    @php
                        $statusStyle = match ($order->status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'payment_failed' => 'bg-red-100 text-red-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <a href="{{ route('orders.show', $order) }}" class="flex justify-between items-center p-5 hover:bg-gray-50 transition">
                        <div>
                            <p class="font-semibold text-sm text-ink-900">Order #{{ $order->id }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $order->created_at->format('M j, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full capitalize {{ $statusStyle }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                            <span class="font-bold text-sm text-ink-900 w-16 text-right">{{ $order->totalFormatted() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</x-layout>
