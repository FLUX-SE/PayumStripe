[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

## Payum Stripe gateways

This library is designed to add a new gateways to Payum to support Stripe (with SCA support)

## Installation

Install using Composer :

```bash
composer require prometee/payum-stripe
```

Choose one of [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation),
the most used is [php-http/guzzle6-adapter](https://packagist.org/packages/php-http/guzzle6-adapter)

```bash
composer require  php-http/guzzle6-adapter
```

## Configuration

 - [Stripe Checkout Session gateway](docs/stripe-checkout-session/README.md)
 - [Stripe JS gateway](docs/stripe-js/README.md)




[ico-version]: https://img.shields.io/packagist/v/Prometee/payum-stripe.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Prometee/PayumStripe/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Prometee/PayumStripe.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/prometee/payum-stripe
[link-travis]: https://travis-ci.org/Prometee/PayumStripe
[link-scrutinizer]: https://scrutinizer-ci.com/g/Prometee/PayumStripe/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Prometee/PayumStripe
