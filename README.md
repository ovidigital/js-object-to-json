# ovidigital/js-object-to-json
###### PHP library to convert a JavaScript object string to JSON formatted string

[![Latest Version](https://img.shields.io/packagist/v/ovidigital/js-object-to-json.svg?acheSeconds=3600&label=latest%20version)](https://github.com/ovidigital/js-object-to-json/releases)
[![Build Status](https://travis-ci.com/ovidigital/js-object-to-json.svg?branch=master)](https://travis-ci.com/ovidigital/js-object-to-json)

## Installation
```bash
composer require ovidigital/js-object-to-json
```

## Usage

```php
// A variable containing a JavaScript object as a string
$jsObjectString = "{ foo:  'bar' }";

// Convert the Javascript object to JSON format
$json = \OviDigital\JsObjectToJson\JsConverter::convertToJson($jsObjectString);
```

## Contributing

Feel free to submit a pull request or create an issue.

## License
This project is licensed under the terms of the MIT license.

Check the [LICENSE.md](LICENSE.md) file for license rights and limitations.
