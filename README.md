# Broadcastify Downloader

[![TravisCI](https://travis-ci.org/phpexpertsinc/skeleton.svg?branch=master)](https://travis-ci.org/phpexpertsinc/skeleton)
[![Maintainability](https://api.codeclimate.com/v1/badges/503cba0c53eb262c947a/maintainability)](https://codeclimate.com/github/phpexpertsinc/SimpleDTO/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/503cba0c53eb262c947a/test_coverage)](https://codeclimate.com/github/phpexpertsinc/SimpleDTO/test_coverage)

The Broadcastify Downloader Project is meant to quickly bulk download archived EMS dispatches from Broadcastify.com.

## Installation

```bash
composer install
```

Edit `.env` and add your Broadcastify.com authorization cookie.

```
BROADCASTIFY_COOKIE=""
```

How to get that cookie is beyond the scope of this project. 
I am willing to get your cookie for you for $100.00 and train you how.

From my experience, the cookie lasts indefinitely if you use the downloader
at least once every 60 days. But the guys @ broadcastify could change this
at any time.

## Usage

```bash
./broadcastify download feedId YYYYMMDD
```


## Testing

*No tests have been created. I'm very open to contributions.*

```bash
phpunit --testdox
```

# Contributors

[Theodore R. Smith](https://www.phpexperts.pro/]) <theodore@phpexperts.pro>  
GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690  
CEO: PHP Experts, Inc.

## License

MIT license. Please see the [license file](LICENSE) for more information.

