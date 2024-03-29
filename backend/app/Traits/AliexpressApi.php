<?php

namespace App\Traits;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;


trait AliexpressApi
{
    public $baseUrl = null;
    public $rapidApiHost = null;
    public $rapidApiKey = null;

    public function __construct()
    {
        $this->baseUrl = config('rapidapi.base_url');
        $this->rapidApiHost = config('rapidapi.x_rapidapi_host');
        $this->rapidApiKey = config('rapidapi.x_rapidapi_key');
    }

    public function makeRequest(string $url)
    {
        $headers = [
            'x-rapidapi-host' => $this->rapidApiHost,
            'x-rapidapi-key' => $this->rapidApiKey
        ];
        $client = new Client([
            'headers' => $headers
        ]);
        // $body = Psr7\Utils::streamFor(json_encode($body));
        $response = $client->request('GET', $url);
        return json_decode($response->getBody(), true);
    }

    public function searchProducts($query, $page = 1)
    {
        $url = "{$this->baseUrl}/api/products/search";
        $query = [
            'name' => $query,
            // 'minSalePrice' => 1,
            // 'maxSalePrice' => 99999999,
            'sort' => 'NEWEST_DESC',
            'page' => $page,
            'targetCurrency' => 'USD',
            'shipFromCountry' => 'CN',
            'fastDelivery' => 'true',
            'lg' => 'en',
            'getShopInformation' => 'true',
        ];
        return $this->makeRequest($url, $query);
    }


    public function ApiProductDetails($product_id)
    {
        $url = "{$this->baseUrl}/product/{$product_id}?language=en";
        return $this->makeRequest($url);
    }


    public function ApiProductShipping($product_id)
    {
        $url = "{$this->baseUrl}/shipping/{$product_id}?destination_country=BD&min_price=0.00&count=3";
        return $this->makeRequest($url);
    }

    public function productFeedback()
    {
    }

    public function storeFeedback()
    {
    }

    public function storeInformation()
    {
    }

    public function storeCategories()
    {
    }

    public function storeCategoryProducts()
    {
    }
}
