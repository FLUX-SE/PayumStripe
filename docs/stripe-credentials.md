# Credentials

## API keys

Get your `publishable_key` and your `secret_key` on your Stripe account :

https://dashboard.stripe.com/test/apikeys

## Webhook key

Then get a `webhook_secret_key` configured with at those events :

| Gateway | `stripe_checkout_session` | `stripe_js` |
|-|-|-|
| Webhook events |  - `checkout.session.completed`<br> - `checkout.session.async_payment_failed`<br> - `checkout.session.async_payment_succeeded`<br> - `setup_intent.canceled` (⚠️ Only when using `setup` mode)<br> - `setup_intent.succeeded`  (⚠️ Only when using `setup` mode) |  - `payment_intent.canceled`<br> - `payment_intent.succeeded`<br> - `setup_intent.canceled` (⚠️ Only when using `setup` mode)<br> - `setup_intent.succeeded`  (⚠️ Only when using `setup` mode) |

The URL to fill is the url to your `notify.php`, here is an example :

```
https://localhost/notify.php?gatewayName=stripe_checkout_session
```

https://dashboard.stripe.com/test/webhooks

## Test or dev environment

Webhooks are triggered by Stripe on their server to your server.
If the server is into a private network, Stripe won't be allowed to reach your server.

Stripe provides an alternate way to catch those webhook events, you can use
`Stripe cli` : https://stripe.com/docs/stripe-cli
Follow the link and install `Stripe cli`, then use those command line to get
your webhook key :

First login to your Stripe account (needed every 90 days) :

```bash
stripe login
```

Then start to listen for the required events, forwarding request to you local server :

```bash
stripe listen \
    --events checkout.session.completed,checkout.session.async_payment_failed,checkout.session.async_payment_succeeded,setup_intent.canceled,setup_intent.succeeded \
    --forward-to https://localhost/notify.php?gateway=stripe_checkout_session
```

> Replace the --forward-to argument value with the right one you need.
