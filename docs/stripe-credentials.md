# Credentials

## API keys

Get your `publishable_key` and your `secret_key` on your Stripe account :

https://dashboard.stripe.com/test/apikeys

## Webhook key
Then get a `webhook_secret_key` configured with at least two events : 
`payment_intent.canceled` and `checkout.session.completed`
and configured with the url to the URL to `notify.php` 

https://dashboard.stripe.com/test/webhooks