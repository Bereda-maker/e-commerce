<div>
    @if ($errorMessage)
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2" role="alert">{{ $errorMessage }}</div>
    @endif

    {{-- Step 1: review --}}
    @if ($step === 'review')
        @if (! $cart || $cart->isEmpty())
            <p class="text-gray-500">Your cart is empty.</p>
        @else
            <div class="divide-y rounded-lg border bg-white mb-6">
                @foreach ($cart->items as $item)
                    <div class="flex justify-between p-4">
                        <span>{{ $item->variant->product->name }} — {{ $item->variant->label() }} × {{ $item->quantity }}</span>
                        <span>${{ number_format($item->lineTotalCents() / 100, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between p-4 font-semibold">
                    <span>Total</span>
                    <span>${{ number_format($cart->totalCents() / 100, 2) }}</span>
                </div>
            </div>

            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled"
                    class="rounded bg-gray-900 text-white px-5 py-2.5 text-sm disabled:opacity-50">
                <span wire:loading.remove>Continue to payment</span>
                <span wire:loading>Placing order…</span>
            </button>
        @endif
    @endif

    {{-- Step 2: pay — Stripe Elements tokenizes card details entirely
         client-side; this component and the Laravel server never see raw
         card data, only the PaymentIntent Stripe itself created. See
         README "Security". --}}
    @if ($step === 'paying')
        <div
            x-data="stripePayment(@js($clientSecret), @js(config('services.stripe.key')))"
            x-init="init()"
            wire:ignore
        >
            <div id="payment-element" class="rounded border bg-white p-4"></div>

            <div id="payment-errors" class="text-sm text-red-600 mt-2" role="alert" x-text="errorMessage"></div>

            <button type="button" @click="submit()" :disabled="submitting"
                    class="mt-4 rounded bg-gray-900 text-white px-5 py-2.5 text-sm disabled:opacity-50">
                <span x-show="!submitting">Pay now</span>
                <span x-show="submitting">Processing…</span>
            </button>
        </div>

        <script src="https://js.stripe.com/v3/"></script>
        <script>
            function stripePayment(clientSecret, publishableKey) {
                return {
                    stripe: null,
                    elements: null,
                    submitting: false,
                    errorMessage: '',
                    init() {
                        this.stripe = Stripe(publishableKey);
                        this.elements = this.stripe.elements({ clientSecret });
                        this.elements.create('payment').mount('#payment-element');
                    },
                    async submit() {
                        this.submitting = true;
                        this.errorMessage = '';

                        const { error } = await this.stripe.confirmPayment({
                            elements: this.elements,
                            redirect: 'if_required',
                        });

                        this.submitting = false;

                        if (error) {
                            this.errorMessage = error.message;
                            @this.call('paymentFailed', error.message);
                            return;
                        }

                        // Payment succeeded client-side. The order's status
                        // still only flips to "paid" once Stripe's webhook
                        // confirms it server-side (see StripeWebhookController)
                        // — this call just advances the UI.
                        @this.call('paymentConfirmed');
                    },
                };
            }
        </script>
    @endif

    {{-- Step 3: confirmation --}}
    @if ($step === 'confirmation')
        <div class="rounded-lg border bg-green-50 p-6 text-center">
            <h2 class="text-xl font-semibold text-green-800">Thank you — your order is confirmed!</h2>
            <p class="mt-2 text-green-700">A confirmation email is on its way.</p>
            <a href="{{ route('orders.show', $orderId) }}" class="inline-block mt-4 underline">
                View order #{{ $orderId }}
            </a>
        </div>
    @endif

    {{-- Step 4: failed --}}
    @if ($step === 'failed')
        <div class="rounded-lg border bg-red-50 p-6 text-center">
            <h2 class="text-xl font-semibold text-red-800">Payment didn't go through</h2>
            <p class="mt-2 text-red-700">{{ $errorMessage }}</p>
            <button type="button" wire:click="$set('step', 'paying')" class="inline-block mt-4 underline">
                Try again
            </button>
        </div>
    @endif
</div>
