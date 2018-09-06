<?php
namespace Simmons\StoreLocator\Helper;

use Magento\Framework\App\Request\Http;
use Simmons\StoreLocator\Model\Source\Distance;
use Simmons\StoreLocator\Model\Source\ProductLine;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Simmons\StoreLocator\Model\Source\Distance
     */
    protected $_distance;

    /**
     * @var \Simmons\StoreLocator\Model\Source\ProductLine
     */
    protected $_productLine;

    /**
     * @param \Magento\Framework\App\Request\Http $_request
     * @param \Simmons\StoreLocator\Model\Source\Distance $_distance
     * @param \Simmons\StoreLocator\Model\Source\ProductLine $_productLine
     */
    public function __construct(
        Http $_request,
        Distance $_distance,
        ProductLine $_productLine
    ) {
        $this->request = $_request;
        $this->distance = $_distance;
        $this->productLine = $_productLine;
    }

    /**
     * Return array of requested values as value => label items or NULL
     *
     * @return array|null
     */
    public function getPost() {
        $request = $this->request->getPost();

        if (count($request)) {
            $data = [
                'zip_code' => $request->findStoreCity,
                'product_line' => $request->findStoreProducts,
                'distance' => $request->findStoreDistance
            ];

            return $data;
        }

        return null;
    }

    /**
     * Return array of options as value => label items
     *
     * @return array
     */
    public function getDistance() {
        return $this->distance->getOptions();
    }

    /**
     * Return array of options as value => label items
     *
     * @return array
     */
    public function getProductLine() {
        return $this->productLine->getOptions();
    }
}
