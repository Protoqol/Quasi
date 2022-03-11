![Quasi](assets/quasi-promo.jpg)

# Quasi

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protoqol/quasi.svg?style=flat-square)](https://packagist.org/packages/protoqol/quasi)

This package generates API resources with keys preset to their respective table's columns.

## Installation

You can install the package via composer:

```bash
composer require protoqol/quasi
```

## Usage

```php
//  Table name is "guessed" based of the resource name and will result in 'users' in this case.
php artisan make:qresource UserResource 

// Table name is given as the second argument.
php artisan make:qresource UserResource users
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

-   [Quinten Justus](https://github.com/protoqol)
-   [All Contributors](../../contributors)

## License

The GNU GPL (GPL). Please see [License File](LICENSE.md) for more information.
