## Laravel PDFmerge wrapper

This is a CMD wrapper for https://github.com/metaist/pdfmerge

## Installation

```shell
composer require tomato-technologies/tomato-pdfmerger
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

### Laravel 5.5+:

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Tomato\PDFMerger\ServiceProvider::class,
```

If you want to make it easier to access Pusher or Event class, add this to your facades in app.php:

```php
'TomatoPDFMerger' => Tomato\PDFMerger\Facade::class,
```

## Usage

Before usage, please remember to set your `PDFMERGE_BIN` in `.env` file. The merchant number should be a number with "M", eg. "000034".


If you want to get more config on this wrapper, you can pull a configuration file into your application by running on of the following artisan command:
