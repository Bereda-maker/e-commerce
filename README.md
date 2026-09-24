# E-Commerce Platform

Project 5 of "Build 10 Real-World Projects" — an online store built around
one hard guarantee: **never sell the same unit of stock twice, and never
lose track of a payment that actually went through.**

**Stack:** PHP · Laravel 11 · Livewire 3 (reactive UI without a separate
JS frontend) · MySQL · Stripe (test mode)

## Why this stack?

A framework this opinionated (routing, ORM, queues, auth, mailing all
included) is the right call for a project with this many moving parts
touching each other — checkout has to coordinate the cart, inventory,
payment, and order-confirmation email as one coherent flow, and Laravel's
conventions keep that coordination in one place instead of scattered
across hand-wired pieces. Livewire gets you a reactive cart/checkout UI
(quantity updates, live stock checks) without standing up a separate
React/Vue app and a JSON API just to talk to itself.

## The core problem this project solves

Two customers click "Buy" on the last unit of the same product within a
few milliseconds of each other. Naively:

1. Both requests read `stock_count = 1`.
2. Both decide "there's enough."
3. Both succeed. You've sold one physical item to two people.

The fix is in `app/Services/CheckoutService.php`:

```php
$variant = ProductVariant::where('id', $item->product_variant_id)
    ->lockForUpdate()
    ->firstOrFail();

if ($variant->stock_count < $item->quantity) {
    throw new OutOfStockException($variant, $item->quantity);
}

$variant->decrement('stock_count', $item->quantity);
```

`lockForUpdate()` inside a `DB::transaction()` locks that row for the
duration of the transaction. The second request's `lockForUpdate()` query
doesn't run until the first transaction commits (or rolls back) — so it
sees the **post-decrement** count, not a stale read from before. One buyer
gets the unit; the other gets a correct, immediate "out of stock" error.

`tests/Feature/CheckoutConcurrencyTest.php` proves this isn't just
correct-by-inspection: it forks two real OS processes (`pcntl_fork`), each
with its own MySQL connection, both attempting to check out the same
last-unit variant at the same instant, and asserts exactly one succeeds.

## Payment: synchronous checkout, asynchronous confirmation

- **Checkout** (`CheckoutComponent` → `CheckoutService::checkout()`) locks
  stock, creates the order as `pending`, and creates a Stripe
  PaymentIntent — all inside one DB transaction. If Stripe's API call
  fails, the whole transaction rolls back, including the stock decrement.
- **Card details never reach this server.** Stripe Elements (loaded
  client-side in `resources/views/livewire/checkout.blade.php`) tokenizes
  the card directly against Stripe. Laravel only ever sees a
  PaymentIntent id.
- **The order is only marked `paid` by a signed Stripe webhook**
  (`StripeWebhookController`), never by a client-side "it worked" message.
  A forged POST to the webhook endpoint without a valid `Stripe-Signature`
  is rejected outright. This is deliberate: trusting the browser to say
  "payment succeeded" would let anyone mark an order paid without paying.
- **A failed payment releases the reserved stock** back
  (`CheckoutService::handlePaymentFailed()`) so a declined card doesn't
  leave inventory silently locked up forever.

## Project layout

```
app/
  Contracts/PaymentGateway.php      the one Stripe operation CheckoutService needs, behind an interface
  Services/CheckoutService.php      the row-locking transaction — the heart of this project
  Services/StripePaymentGateway.php real implementation, wraps the Stripe SDK
  Http/Livewire/                    CartComponent, CheckoutComponent, AddToCartForm
  Http/Controllers/                 ProductController, OrderController, StripeWebhookController
  Jobs/SendOrderConfirmationEmail.php  queued — see "Queue worker" below
database/migrations/                products, product_variants, carts, cart_items, orders, order_items
tests/Feature/CheckoutConcurrencyTest.php   the two-simultaneous-checkouts proof
```

## Local development

Requires PHP 8.2+, Composer, and MySQL (Docker is easiest).

```bash
# 1. Install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database — either point .env at your own MySQL, or run one via Docker:
docker run -d --name ecommerce-mysql -e MYSQL_ALLOW_EMPTY_PASSWORD=true \
  -e MYSQL_DATABASE=ecommerce -p 3306:3306 mysql:8.0

# 4. Migrate + seed demo data (12 products, 3 variants each, one demo user)
php artisan migrate --seed

# 5. Stripe test keys — from dashboard.stripe.com (toggle "Test mode"),
#    Developers > API keys. Put them in .env as STRIPE_KEY / STRIPE_SECRET.

# 6. Run the app
php artisan serve
# in a separate terminal, the queue worker (for order confirmation emails):
php artisan queue:work
```

Demo login: `demo@example.com` / `password` (seeded by `DatabaseSeeder`).

### Testing Stripe webhooks locally

Use the [Stripe CLI](https://stripe.com/docs/stripe-cli) to forward events
to your local server:

```bash
stripe listen --forward-to localhost:8000/webhooks/stripe
```

It prints a `whsec_...` signing secret — put that in `.env` as
`STRIPE_WEBHOOK_SECRET`.

## Testing

```bash
composer test
# or directly:
vendor/bin/pest
```

The concurrency test requires the `pcntl` PHP extension (present by
default on Linux; not available on Windows — run it inside WSL or Docker
there). CI (`.github/workflows/ci.yml`) runs on `ubuntu-latest` against a
real MySQL service container specifically so this test runs for real, not
skipped.

## Security notes

- Raw card numbers never touch this server — see "Payment" above.
- Every order/product mutation checks ownership server-side
  (`OrderController::show` aborts 403 for a non-owner) — never trust a
  hidden form field or a client-supplied user id.
- The Stripe webhook route is the one place CSRF protection is
  deliberately disabled (`bootstrap/app.php`), because Stripe's server
  can't present a CSRF token — it's protected instead by signature
  verification inside `StripeWebhookController`.

## License

MIT — built for educational purposes as part of a "Build 10 Real-World
Projects" learning series.
