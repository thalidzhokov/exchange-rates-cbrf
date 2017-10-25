# PHP Class ExchangeRatesCBRF

## Requirements
1. PHP 5 or greater
2. SOAP and SimpleXML

## Installation

Install via Composer
```
composer require thalidzhokov/exchange-rates-cbrf
```

OR include _ExchangeRatesCBRF.php_ in your PHP code
```
include_once PATH_TO_CLASS . 'ExchangeRatesCBRF.php';
```

## Usage
Examples of using ExchangeRatesCBRF Class to get exchange rates of the Central Bank of Russia


__Example 1__ \
Get exchange rate of Ukrainian Hryvnia (Alphabetic currency code - UAH) on 25.05.2015 \
_<?php_ \
_require_once("ExchangeRatesCBRF.php");_ \
_$rates = new ExchangeRatesCBRF("2015-05-25");_ \
_echo $rates->GetRate("UAH");_


__Example 2__ \
Get cross-rate of the US Dollar to Euro on 26.06.2015 \
_<?php_ \
_require_once("ExchangeRatesCBRF.php");_ \
_$rates = new ExchangeRatesCBRF("2015-06-26");_ \
_echo $rates->GetCrossRate("EUR", "USD");_


__Example 3__ \
Get exchange rates of the Central Bank of Russia on 27.07.2015 \
_<?php_ \
_require_once("ExchangeRatesCBRF.php");_ \
_$rates = new ExchangeRatesCBRF("2015-07-27");_ \
_echo $rates->GetRates();_
