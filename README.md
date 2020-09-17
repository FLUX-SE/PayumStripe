[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![codecov](ico-codecov)][link-codecov]

## Payum Stripe gateways

This library is designed to add a new gateways to Payum to support Stripe (with SCA support)
Actually there is one Gateway fully supported `Stripe checkout session` but soon `Stripe JS` will be supported too.

> If you are using Symfony use the bundle : [flux-se/payum-stripe-bundle](https://github.com/FLUX-SE/PayumStripeBundle)

> If you are using Sylius use the plugin : [flux-se/sylius-payum-stripe-plugin](https://github.com/FLUX-SE/SyliusPayumStripePlugin)

## Installation

Install using Composer :

```bash
composer require flux-se/payum-stripe
```

Choose one of [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation),
the most used is [php-http/guzzle6-adapter](https://packagist.org/packages/php-http/guzzle6-adapter)

```bash
composer require  php-http/guzzle6-adapter
```

## Gateways configuration

 - [Stripe Checkout Session](docs/stripe-checkout-session/README.md)
   
   Support :
    - ["One-time payments"](https://stripe.com/docs/payments/checkout/one-time)
    - ["Subscription"](https://stripe.com/docs/payments/checkout/subscriptions/starting)
    - ["Update payment details"](https://stripe.com/docs/payments/checkout/subscriptions/updating)
    
 - [WIP] [Stripe JS](docs/stripe-js/README.md)

## More

### What to use with Stripe Checkout Session gateway ?

 - A Symfony bundle : [flux-se/payum-stripe-bundle](https://github.com/FLUX-SE/PayumStripeBundle)
 - A Sylius plugin : [flux-se/sylius-payum-stripe-plugin](https://github.com/FLUX-SE/SyliusPayumStripePlugin)
  
### What to use with Stripe JS gateway ?

 - [WORK IN PROGRESS]


[ico-version]: https://img.shields.io/packagist/v/FLUX-SE/payum-stripe.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/FLUX-SE/PayumStripe/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/FLUX-SE/PayumStripe.svg?style=flat-square
[ico-codecov]: https://codecov.io/gh/FLUX-SE/PayumStripe/branch/master/graph/badge.svg

[link-packagist]: https://packagist.org/packages/flux-se/payum-stripe
[link-travis]: https://travis-ci.org/FLUX-SE/PayumStripe
[link-scrutinizer]: https://scrutinizer-ci.com/g/FLUX-SE/PayumStripe/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/FLUX-SE/PayumStripe
[link-codecov]: https://codecov.io/gh/FLUX-SE/PayumStripe
