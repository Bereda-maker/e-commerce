# Deployment Guide

This app has **two processes that need to run continuously**, not one:

- The **web server** (handles requests, including the Stripe webhook)
- The **queue worker** (`php artisan queue:work`) — sends order
  confirmation emails. If you only deploy the web process, orders will
  still work, but confirmation emails will silently pile up undelivered
  in the `jobs` table forever.

Plus a **MySQL database**.

This guide uses **Render** (consistent with Project 4's deployment in this
series — no credit card required for its free web service tier) for both
processes, and a managed MySQL provider for the database.

---

## 1. Provision MySQL

Pick a managed MySQL provider:
- [PlanetScale](https://planetscale.com) — MySQL-compatible, has a free tier
- [Railway](https://railway.app) or Render's own managed MySQL (paid) also work

You need a connection string or these five values: host, port, database
name, username, password.

## 2. Push this repo to GitHub, then connect it to Render

(You've already got a GitHub repo — connect Render to it the same way as
Project 4: render.com → sign up/sign in with GitHub → authorize access to
this repo.)

## 3. Create the web service

- Dashboard → **New +** → **Web Service** → select this repo
- **Environment:** Docker (add the `Dockerfile` below first — see step 5)
- **Instance Type:** Free
- **Health Check Path:** `/up` (Laravel's built-in health route, registered in `bootstrap/app.php`)

## 4. Create the queue worker as a second service

- Dashboard → **New +** → **Background Worker** → select the same repo
- Same Docker image as the web service, but with a different start
  command (see Dockerfile's `CMD` vs. the worker override below)
- **Start Command override:** `php artisan queue:work --tries=3 --sleep=3`

Background Workers on Render don't serve HTTP traffic, which is exactly
right here — this process only needs to keep pulling jobs off the queue.

## 5. Add a Dockerfile

This project doesn't ship one yet (it's PHP/Laravel rather than a
container-native runtime by default) — add this at the repo root:

```dockerfile
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libpng-dev \
    && docker-php-ext-install pdo_mysql zip bcmath pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Migrations run once as part of boot; in a real multi-instance deploy
# this should be a separate release step instead — see the note in
# Project 4's Dockerfile for the same tradeoff.
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

## 6. Environment variables (set on both the web service AND the worker)

```
APP_NAME=E-Commerce Platform
APP_ENV=production
APP_KEY=<run: php artisan key:generate --show, paste the output>
APP_DEBUG=false
APP_URL=https://<your-render-web-url>.onrender.com

DB_CONNECTION=mysql
DB_HOST=<from step 1>
DB_PORT=<from step 1>
DB_DATABASE=<from step 1>
DB_USERNAME=<from step 1>
DB_PASSWORD=<from step 1>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=<your transactional email provider>
MAIL_PORT=587
MAIL_USERNAME=<...>
MAIL_PASSWORD=<...>
MAIL_FROM_ADDRESS=orders@yourdomain.com

STRIPE_KEY=pk_live_...       # switch from pk_test_ once you're ready to take real payments
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=<set in step 7>
```

## 7. Point Stripe's webhook at your live URL

Stripe dashboard → Developers → Webhooks → **Add endpoint**:

- URL: `https://<your-render-web-url>.onrender.com/webhooks/stripe`
- Events to send: `payment_intent.succeeded`, `payment_intent.payment_failed`

Stripe shows you a signing secret (`whsec_...`) once the endpoint is
created — set that as `STRIPE_WEBHOOK_SECRET` on the web service (the
worker doesn't need it; only the web process handles the webhook route)
and redeploy.

## 8. Smoke test

1. Visit your Render URL, register an account, browse to a product.
2. Add it to cart, go to checkout, click "Continue to payment."
3. Use [Stripe's test card](https://stripe.com/docs/testing) `4242 4242
   4242 4242`, any future expiry, any CVC.
4. Confirm the order shows "confirmation" in the UI, and check your email
   (if `MAIL_MAILER` is configured) or the worker's logs for the
   dispatched `SendOrderConfirmationEmail` job.
5. In the Stripe dashboard, confirm the webhook delivery shows a `200`.

## Testing the concurrency guarantee against your live database

Not something to do against production data, but worth doing once against
a staging copy: seed a variant with `stock_count = 1`, then run
`vendor/bin/pest --filter=CheckoutConcurrencyTest` pointed at that
database (via `DB_DATABASE` in a staging `.env`) to confirm the row-lock
behaves the same way outside your local Docker MySQL as it did in CI.
