<!-- ix-docs-ignore -->
# avetec-php
A php client library for generating image URLs with avetec

[![PHP-Version](https://img.shields.io/badge/php%20version-8.2-blue)](#)
[![Downloads](https://img.shields.io/packagist/dt/mongroove/avetec-php)](https://packagist.org/packages/mongroove/avetec-php)
[![License](https://img.shields.io/github/license/mongroove/avetec-php)](https://github.com/mongroove/avetec-php/blob/main/LICENSE)

---
<!-- /ix-docs-ignore -->

- [Installation](#installation)
- [Usage](#usage)
- [Base64 Params](#base64-params)

## Installation

You can install the package via composer:

```bash
composer require mongroove/avetec-php
```

## Usage

To begin creating avetec URLs programmatically, add the php files to your project. The URL builder can be reused to create URLs for any
images on the domains it is provided.

```php
use Avetec\UrlBuilder;

$urlBuilder = new UrlBuilder("example.domain.com", 'your-secret-key');
$params = array("w" => 500, "h" => 500);

echo $urlBuilder->createURL("856c4490-784f-4a8e-a918-aa66c0398a9q", 'png', $params);

// 'https://example.domain.com/856c4490-784f-4a8e-a918-aa66c0398a9q.png?h=500&w=500'
```

## Base64 params

The parameters can be bundled and output with an encrypted base64 parameter, call `setUseBase64` on the builder.

```php
$urlBuilder->setUseBase64(true);

echo $urlBuilder->createURL("856c4490-784f-4a8e-a918-aa66c0398a9q", 'png', $params);

// https://example.domain.com/v1/856c4490-784f-4a8e-a918-aa66c0398a9q.png?bc=eyJ3Ijo1MDAsImgiOjUwMCwi...
```