<?php

namespace Perfacilis\Geocoder;

use Exception;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Roy Arisse <support@perfacilis.com>
 * @copyright (c) 2021, Perfacilis
 */
class Geocoder
{
    public function __construct(string $api_key = '')
    {
        $this->api_key = $api_key;
    }

    /**
     * Set cache interface to save queries, thus save a bit of moneyz.
     *
     * @param CacheInterface $cacher
     * @param int $ttl Results lifetime in seconds
     * @return void
     */
    public function setCacheInterface(CacheInterface $cacher, int $ttl = 0): void
    {
        $this->cacher = $cacher;
        $this->cache_ttl = $ttl;
    }

    public function geocode(string $address): Result
    {
        $params = Query::fromAddress($address)->getParamms();
        return $this->getResult($params);
    }

    public function reverseGeocode(float $lat, float $lng): Result
    {
        $params = Query::fromLatLng($lat, $lng)->getParamms();
        return $this->getResult($params);
    }

    private const ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @var string
     */
    private $api_key = '';

    /**
     * @var CacheInterface
     */
    private $cacher = null;

    /**
     * @var int
     */
    private $cache_ttl = 0;

    private function getResult(array $params): Result
    {
        $items = $this->query($params);
        return $items[0];
    }

    /**
     * @param array $params
     * @return Result[]
     * @throws Exception
     */
    private function query(array $params): array
    {
        $result = $this->queryCache($params);
        if (!$result) {
            $result = $this->queryEndpoint($params);
            $this->saveCache($params, $result);
        }

        $items = [];
        foreach ($result['results'] as $item) {
            $items[] = new Result($item);
        }

        return $items;
    }

    private function queryEndpoint(array $params): array
    {
        // Append api key
        $params['key'] = $this->api_key;

        // Build URL with query parameters
        $url = self::ENDPOINT . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $json = curl_exec($ch);
        $result = $json ? json_decode($json, true) : [];
        if (!$result) {
            throw new Exception(sprintf('Invalid json response from %s: %s', $url, $json));
        }

        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($error || $status < 200 || $status > 299) {
            throw new Exception(sprintf('Invalid status %d from %s; error: %s.', $status, $url, $error));
        }

        if (array_key_exists('error_message', $result)) {
            throw new Exception(sprintf('Geocoder api returned error: %s', $result['error_message']));
        }

        return $result;
    }

    /**
     * Get result from cache, if cacher is set
     *
     * @param array $params
     * @return array
     */
    private function queryCache(array $params): array
    {
        if (!$this->cacher) {
            return [];
        }

        $key = $this->getCacheKey($params);

        return $this->cacher->get($key, []);
    }

    private function saveCache(array $params, array $result): void
    {
        if (!$this->cacher) {
            return;
        }

        $key = $this->getCacheKey($params);
        $this->cacher->set($key, $result, $this->cache_ttl);
    }

    private function getCacheKey(array $params): string
    {
        return get_called_class() . ':' . json_encode($params);
    }
}
