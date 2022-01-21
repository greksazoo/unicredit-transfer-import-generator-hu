# Transfer Import file Generator for Unicredit Bank Zrt. (Hungary) Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/greksazoo/unicredit-transfer-import-generator-hu.svg?style=flat-square)](https://packagist.org/packages/greksazoo/unicredit-transfer-import-generator-hu)
[![Total Downloads](https://img.shields.io/packagist/dt/greksazoo/unicredit-transfer-import-generator-hu.svg?style=flat-square)](https://packagist.org/packages/greksazoo/unicredit-transfer-import-generator-hu)

This package mainly used for generate Unicredit Bank (Hungary) HUF transfers import file.
This is for Laravel 8.0 or newer.

## Installation

You can install the package via composer:

```bash
composer require greksazoo/unicredit-transfer-import-generator-hu
```

## Usage

```php
$uni = new UnicreditHuGenerator( 
	    [
            'currency'     => 'HUF',
            'account_number' => '11112222-33334444-00000000',
	    ],
		[
		    [
                'partner_name'=>'TesztPartner1',
                'account_number' => '11112222-00000099',
				'date'=>'2022-02-02',
				'amount'=>'200010',
				'notice'=>'ez egy hosszabb kozlemeny is lehet'
            ],
            [
                'partner_name'=>'TesztPartner2',
                'account_number' => '11112299-33334444-00000002',
                'amount'=>'3000100',
                'notice'=>'ez egy hosszabb kozlemeny is lehet'
            ],
        ]
    );
$filenev = $uni->generateFile();
return Storage::download($filenev,'data.pay');
```

or simply generate only text in a variable:
```php
$uni = new UnicreditHuGenerator( 
	    [
            'currency'     => 'HUF',
            'account_number' => '11112222-33334444-00000000',
	    ],
		[
		    [
                'partner_name'=>'TesztPartner1',
                'account_number' => '11112222-00000099',
				'date'=>'2022-02-02',
				'amount'=>'200010',
				'notice'=>'ez egy hosszabb kozlemeny is lehet'
            ],
            [
                'partner_name'=>'TesztPartner2',
                'account_number' => '11112299-33334444-00000002',
                'amount'=>'3000100',
                'notice'=>'ez egy hosszabb kozlemeny is lehet'
            ],
        ]
    );
$text = $uni->generateText();
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email zoli.greksa@gmail.com instead of using the issue tracker.

## Credits

- [Zoltan Greksa](https://github.com/greksazoo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
