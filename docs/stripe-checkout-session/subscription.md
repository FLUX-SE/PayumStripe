# Stripe Checkout Session : `subscription`

# Subscription handling

Payum don't have php `Interface`s to handle subscriptions, that's why subscriptions should be
managed by yourself. There is maybe a composer packages which fit your need,
but you will have to build the interface between your subscription `Model` class and `Payum`.

Usually you will have to build a `ConvertPaymentAction` like this one : [ConvertPaymentAction.php](https://github.com/FLUX-SE/SyliusPayumStripePlugin/blob/master/src/Action/ConvertPaymentAction.php)
customizing the `supports` method to fit your need and provide the right `$details` array.

Example : https://stripe.com/docs/payments/checkout/subscriptions/starting#create-checkout-session (`$details` is the array given to create a `Session`)

## Subscription update payment details

Same as the [previous chapter](#subscription-handling)

Example : https://stripe.com/docs/payments/checkout/subscriptions/updating#create-checkout-session (`$details` is the array given to create a `Session`)
