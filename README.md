# Simple Geocoder with Cache using Google Maps API

Uses PSR SimpleCache interface to allow for query results to be stored.
This is a standalone library, no Laravel or other Frameworks, to keep it as simple and lightweight as possible. 

## Installation

```bash
composer require perfacilis/geocoder
```

## Usage

### Get a Google Maps API key

See: https://console.cloud.google.com/apis/credentials

Create a new API key, ensure to:
1. Restrict it to your IP(s) only
2. Restrict it to use the Geocoding API only.

If you're using the Google Maps JS API you'll probably have to create a different key because that key should be restricted to HTTP referers (web sites).

### Simple Example

```php
$api_key = '123foo456bar';

$geocoder = new Perfacilis\Geocoder\Geocoder($api_key);
$result = $geocoder->geocode('Street 12, 1234AB, Place, Country');

$lat = $result->getLat();
$lng = $result->getLng();
```

## Enabling Cache

Since Google's Geocoding API is on a Pay-Per-Use basis, it's recommended to implement your own Cacher using PSR's SimpleCache interface:

```php
$api_key = '123foo456bar';
$cacher = new GeocoderCache();

$geocoder = new Geocoder($api_key);
$geocoder->setCacheInterface($cacher);

$result = $geocoder->query(...);

```

You can manually create a cacher to store results in a simple database:
```php
class GeocoderCache implements Psr\SimpleCache\CacheInterface
{
   ...
}
```
