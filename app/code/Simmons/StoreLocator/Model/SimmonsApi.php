<?php

namespace Simmons\StoreLocator\Model;

/**
 * Class SimmonsApi
 * @package Simmons\StoreLocator\Model
 */
class SimmonsApi
{
    const DEALER_LOCATOR_URL = 'http://webservices.simmons.com/DLService/dealersStreets.cfc?method=DealerLocatorStreetJSON';

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * SimmonsApi constructor.
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     */
    public function __construct(\Magento\Framework\HTTP\Client\Curl $curlClient)
    {
        $this->curlClient = $curlClient;
    }

    /**
     * @param $address
     * @param $distance
     * @param int $productLineId
     * @return array|bool
     */
    public function getStoresByAddress($address, $distance, $productLineId = 0)
    {
        $response = [];
        try {
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $this->curlClient->setHeaders($headers);
            $this->curlClient->post(
                self::DEALER_LOCATOR_URL,
                [
                    'address' => $address,
                    'distance' => (int)$distance,
                    'productLine' => (int)$productLineId
                ]
            );
            if ($this->curlClient->getStatus() == 200) {
                $response = json_decode($this->curlClient->getBody(),true);
            }
        } catch (\Exception $e) {
            return false;
        }

        return $response;
    }
}
