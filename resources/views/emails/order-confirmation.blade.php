<x-mail::message>
# Thanks for your order, {{ $order->user->name }}!

Order **#{{ $order->id }}** is confirmed and being prepared.

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} {{ $item->variant_label ? "($item->variant_label)" : '' }} | {{ $item->quantity }} | ${{ number_format($item->unit_price_cents / 100, 2) }} |
@endforeach
</x-mail::table>

**Total: {{ $order->totalFormatted() }}**

<x-mail::button :url="route('orders.show', $order)">
View your order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
