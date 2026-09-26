<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-extrabold text-ink-900 mb-1">Dashboard</h1>
        <p class="text-sm text-gray-500 mb-8">Live numbers from paid orders and current stock — nothing here is sample data.</p>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-10">
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs text-gray-500 mb-1">Total revenue</p>
                <p class="text-2xl font-extrabold text-ink-900">${{ number_format($totalRevenueCents / 100, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs text-gray-500 mb-1">Paid orders</p>
                <p class="text-2xl font-extrabold text-ink-900">{{ $paidOrderCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs text-gray-500 mb-1">Avg. order value</p>
                <p class="text-2xl font-extrabold text-ink-900">${{ number_format($averageOrderCents / 100, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-xs text-gray-500 mb-1">Orders today</p>
                <p class="text-2xl font-extrabold text-ink-900">{{ $ordersToday }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-xs text-amber-700 mb-1">Pending payment</p>
                <p class="text-2xl font-extrabold text-amber-900">{{ $pendingOrders }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-10">
            {{-- Revenue chart --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="font-semibold text-ink-900 mb-4">Revenue — last 14 days</h2>
                <canvas id="revenueChart" height="90" aria-label="Line chart of daily revenue over the last 14 days" role="img"></canvas>
            </div>

            {{-- Top products chart --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="font-semibold text-ink-900 mb-4">Top products by units sold</h2>
                @if ($topProducts->isEmpty())
                    <p class="text-sm text-gray-400 py-10 text-center">No paid orders yet.</p>
                @else
                    <canvas id="topProductsChart" height="220" aria-label="Bar chart of top-selling products" role="img"></canvas>
                @endif
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Low stock --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="font-semibold text-ink-900 mb-4 flex items-center gap-2">
                    Low stock
                    @if ($lowStockVariants->isNotEmpty())
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ $lowStockVariants->count() }}</span>
                    @endif
                </h2>
                @if ($lowStockVariants->isEmpty())
                    <p class="text-sm text-gray-400 py-6 text-center">Nothing below the 5-unit threshold.</p>
                @else
                    <div class="divide-y">
                        @foreach ($lowStockVariants as $variant)
                            <div class="flex justify-between items-center py-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-900">{{ $variant->product->name ?? 'Unknown product' }}</p>
                                    <p class="text-xs text-gray-500">{{ $variant->label() }}</p>
                                </div>
                                <span class="text-sm font-bold {{ $variant->stock_count === 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $variant->stock_count }} left
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent orders --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="font-semibold text-ink-900 mb-4">Recent orders</h2>
                @if ($recentOrders->isEmpty())
                    <p class="text-sm text-gray-400 py-6 text-center">No orders yet.</p>
                @else
                    <div class="divide-y">
                        @foreach ($recentOrders as $order)
                            @php
                                $statusStyle = match ($order->status) {
                                    'paid' => 'bg-green-100 text-green-700',
                                    'payment_failed' => 'bg-red-100 text-red-700',
                                    'cancelled' => 'bg-gray-100 text-gray-600',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <div class="flex justify-between items-center py-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-900">#{{ $order->id }} — {{ $order->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full capitalize {{ $statusStyle }}">{{ str_replace('_', ' ', $order->status) }}</span>
                                    <span class="text-sm font-bold text-ink-900 w-14 text-right">{{ $order->totalFormatted() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const revenueLabels = @json($revenueByDay->pluck('date'));
        const revenueValues = @json($revenueByDay->pluck('cents')->map(fn ($c) => $c / 100));

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueValues,
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234, 88, 12, 0.08)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: (v) => '$' + v } } },
            },
        });

        @if ($topProducts->isNotEmpty())
            const topProductLabels = @json($topProducts->pluck('product_name'));
            const topProductValues = @json($topProducts->pluck('units_sold'));

            new Chart(document.getElementById('topProductsChart'), {
                type: 'bar',
                data: {
                    labels: topProductLabels,
                    datasets: [{
                        label: 'Units sold',
                        data: topProductValues,
                        backgroundColor: '#f97316',
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        @endif
    </script>
</x-layout>
