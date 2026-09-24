<div>
    {{-- Progress indicator --}}
    <div class="flex items-center gap-2 mb-10 text-xs font-semibold">
        @php
            $steps = ['review' => 'Review', 'paying' => 'Payment', 'confirmation' => 'Confirmed'];
            $order = ['review', 'paying', 'confirmation', 'failed'];
            $currentIndex = array_search($step, $order);
        @endphp
        @foreach ($steps as $key => $label)
            @php $stepIndex = array_search($key, $order); $active = $currentIndex >= $stepIndex; @endphp
            <div class="flex items-center gap-2 {{ ! $loop->first ? 'flex-1' : '' }}">
                @unless ($loop->first)
                    <div class="flex-1 h-0.5 {{ $active ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
                @endunless
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] {{ $active ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                        {{ $loop->index + 1 }}
                    </span>
                    <span class="{{ $active ? 'text-ink-900' : 'text-gray-400' }} hidden sm:inline">{{ $label }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @if ($errorMessage)
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm" role="alert">{{ $errorMessage }}</div>
    @endif

    {{-- Step 1: review --}}
    @if ($step === 'review')
        @if (! $cart || $cart->isEmpty())
            <p class="text-gray-500">Your cart is empty.</p>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white divide-y mb-6">
                @foreach ($cart->items as $item)
                    <div class="flex justify-between items-center p-4">
                        <div>
                            <p class="text-sm font-medium text-ink-900">{{ $item->variant->product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->variant->label() }} × {{ $item->quantity }}</p>
                        </div>
                        <span class="font-semibold text-sm">${{ number_format($item->lineTotalCents() / 100, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between p-4 font-bold text-ink-900 bg-gray-50 rounded-b-2xl">
                    <span>Total</span>
                    <span>${{ number_format($cart->totalCents() / 100, 2) }}</span>
                </div>
            </div>

            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled"
                    class="w-full rounded-full bg-brand-600 hover:bg-brand-700 text-white px-5 py-3.5 font-semibold transition disabled:opacity-60">
                <span wire:loading.remove wire:target="placeOrder">Continue to payment</span>
                <span wire:loading wire:target="placeOrder">Placing order…</span>
            </button>
        @endif
    @endif

    {{-- Step 2: pay --}}
    @if ($step === 'paying')
        <div
            x-data="stripePayment(@js($clientSecret), @js(config('services.stripe.key')))"
            x-init="init()"
            wire:ignore
        >
            <div id="payment-element" class="rounded-xl border border-gray-200 bg-white p-5"></div>

            <div id="payment-errors" class="text-sm text-red-600 mt-2" role="alert" x-text="errorMessage"></div>

            <button type="button" @click="submit()" :disabled="submitting"
                    class="w-full mt-5 rounded-full bg-brand-600 hover:bg-brand-700 text-white px-5 py-3.5 font-semibold transition disabled:opacity-60">
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

                        @this.call('paymentConfirmed');
                    },
                };
            }
        </script>
    @endif

    {{-- Step 3: confirmation --}}
    @if ($step === 'confirmation')
        <div class="text-center py-12 rounded-2xl border border-green-200 bg-green-50">
            <div class="w-14 h-14 mx-auto rounded-full bg-green-600 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h2 class="text-xl font-bold text-green-900">Order confirmed!</h2>
            <p class="mt-2 text-green-700 text-sm">A confirmation email is on its way.</p>
            <a href="{{ route('orders.show', $orderId) }}" class="inline-block mt-6 rounded-full bg-ink-900 hover:bg-ink-800 text-white px-6 py-2.5 font-semibold text-sm transition">
                View order #{{ $orderId }}
            </a>
        </div>
    @endif

    {{-- Step 4: failed --}}
    @if ($step === 'failed')
        <div class="text-center py-12 rounded-2xl border border-red-200 bg-red-50">
            <h2 class="text-xl font-bold text-red-900">Payment didn't go through</h2>
            <p class="mt-2 text-red-700 text-sm">{{ $errorMessage }}</p>
            <button type="button" wire:click="$set('step', 'paying')" class="inline-block mt-6 rounded-full bg-ink-900 hover:bg-ink-800 text-white px-6 py-2.5 font-semibold text-sm transition">
                Try again
            </button>
        </div>
    @endif
</div>
