![Laravel Wallet](./Banner.png)

# Laravel Wallet

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laraditz/wallet.svg?style=flat-square)](https://packagist.org/packages/laraditz/wallet)
[![Total Downloads](https://img.shields.io/packagist/dt/laraditz/wallet.svg?style=flat-square)](https://packagist.org/packages/laraditz/wallet)
[![License](https://poser.pugx.org/laraditz/wallet/license?format=flat-square)](https://packagist.org/packages/laraditz/wallet)

A simple virtual wallet or e-wallet for Laravel. DO NOT use for production as this is at experimental stage.

## Installation

You can install the package via composer:

```bash
composer require laraditz/wallet
```

## Usage

Add the `HasWallets` trait to your model.

```php
use Laraditz\Wallet\Traits\HasWallets;

class User extends Authenticatable
{
    use HasWallets;
    ...
}
```

With that we are set. You can now deposit, withdraw or transfer using your e-wallet. 

```php
$userOne = User::find(1);
$walletOne = $userOne->getWallet(); // get wallet for userOne

// deposit
$deposit = $walletOne->deposit("100"); // deposit amount of 100 into default wallet with processing status
$deposit->markAsCompleted(); // change the status from processing to completed

$walletOne->depositNow("100"); // Use depositNow() so that the transaction completed immediately

// withdraw
$withdraw = $walletOne->withdraw("100"); // withdraw amount of 100 into default wallet with processing status
$withdraw->markAsCompleted(); // change the status from processing to completed

$walletOne->withdrawNow("100"); // Use withdrawNow() so that the transaction completed immediately

$userTwo = User::find(2);
$walletTwo = $userTwo->getWallet(); // get wallet for userTwo

// transfer amount from userOne to userTwo
$transfer = $walletOne->transfer($walletTwo, "100");
$transfer->markAsCompleted(); // change the status from processing to completed

$walletOne->transferNow($walletTwo, "100"); // Use transferNow() so that the transaction completed immediately


// To add more wallet types
app('wallet')->createWalletType([
    'name' => 'New Wallet',  // will produce new-wallet slug
    'currency_code' => 'MYR'
]);

// to use the new wallet
$userThree = User::find(3);
$walletThree = $userThree->getWallet('new-wallet'); // use the wallet slug

// to update description or/and metadata to the wallet
$walletThree->update([
    'description' => 'This is my crypto wallet', 
    'metadata' => [
        'address' => '0xf6A32f757196ac753A354F145F408bF88BEacf77'
    ]
]);

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email raditzfarhan@gmail.com instead of using the issue tracker.

## Credits

-   [Raditz Farhan](https://github.com/laraditz)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
