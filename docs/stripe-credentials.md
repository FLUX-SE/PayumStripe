# Credentials

## API keys

Get your `publishable_key` and your `secret_key` on your Stripe account :

https://dashboard.stripe.com/test/apikeys

## Webhook key

Then get a `webhook_secret_key` configured with at least two events :
 
 - `payment_intent.canceled`
 - `checkout.session.completed`

The URL to fill is the url to your `notify.php`, here is an example :

```
http://localhost/notify.php?gateway=stripe_checkout_session
```

https://dashboard.stripe.com/test/webhooks
