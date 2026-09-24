<x-layout>
    <h1 class="text-2xl font-semibold mb-6">My orders</h1>

    @if ($orders->isEmpty())
        <p class="text-gray-500">You haven't placed any orders yet.</p>
    @else
        <div class="divide-y rounded-lg border bg-white">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex justify-between p-4 hover:bg-gray-50">
                    <div>
                        <p class="font-medium">Order #{{ $order->id }}</p>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">{{ $order->totalFormatted() }}</p>
                        <p class="text-sm capitalize text-gray-500">{{ str_replace('_', ' ', $order->status) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-layout>
