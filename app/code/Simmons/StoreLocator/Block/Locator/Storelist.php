<?php

namespace Simmons\StoreLocator\Block\Locator;

use Simmons\StoreLocator\Model\LocatorRepository;

class Storelist extends \Netbaseteam\Locator\Block\Locator\Storelist
{
    /**
     * @var LocatorRepository
     */
    protected $locatorRepository;

    /**
     * @var \Netbaseteam\Locator\Model\ResourceModel\Locator\Collection
     */
    protected $collection;

    /**
     * Storelist constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Netbaseteam\Locator\Model\LocatorFactory $localtorFactory
     * @param \Netbaseteam\Locator\Helper\Data $dataHelper
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\ResponseInterface $responInterface
     * @param \Netbaseteam\Locator\Model\WorkdateFactory $workdateFactory
     * @param \Netbaseteam\Locator\Model\ScheduleFactory $scheduleFactory
     * @param LocatorRepository $locatorRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Netbaseteam\Locator\Model\LocatorFactory $localtorFactory,
        \Netbaseteam\Locator\Helper\Data $dataHelper,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\ResponseInterface $responInterface,
        \Netbaseteam\Locator\Model\WorkdateFactory $workdateFactory,
        \Netbaseteam\Locator\Model\ScheduleFactory $scheduleFactory,
        LocatorRepository $locatorRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $localtorFactory,
            $dataHelper,
            $countryFactory,
            $jsonHelper,
            $responInterface,
            $workdateFactory,
            $scheduleFactory,
            $data
        );
        $this->locatorRepository = $locatorRepository;
    }

    public function getCollection()
    {
        if ($this->collection === null) {
            $address = $this->getRequest()->getParam('zip_code');
            $distance = $this->getRequest()->getParam('distance');
            $productLineId = $this->getRequest()->getParam('product_line', 0);

            if ($address && $distance) {
                try {
                    $collection = $this->locatorRepository->getStoresByAddress($address, $distance, $productLineId);
                } catch (\Exception $e) {
                }
            } else {
                $collection = $this->_getCollection();
                $collection->addFieldToFilter('status', array('eq'=>'1'))
                    ->setOrder('ordering','ASC');
            }

            $this->collection = $collection;
        }

        return $this->collection;
    }

    public function toPositionJson($store){
        $position = array();
        $position['lat'] = floatval($store['latitude']);
        $position['lng'] = floatval($store['longitude']);
        $position = json_encode($position, JSON_HEX_APOS|JSON_HEX_QUOT);
        return $position;

    }

    public function toStoreJsonData($store){
        $storeInfo = array();
        $storeInfo['store_name'] = $store['store_name'];
        $storeInfo['store_link'] = $store['store_link'];
        $storeInfo['phone_number'] = $store['phone_number'];
        $storeInfo['fax_number'] = $store['fax_number'];
        $storeInfo['address'] = $store['address'];
        
        $storeInfo['store_id'] =  intval($store['localtor_id']);
        $storeInfo = json_encode($storeInfo, JSON_HEX_APOS|JSON_HEX_QUOT);
        return $storeInfo;

    }

    public function toZoonLevelJsonData($store){
        $level = array();
        $level['zoom_level'] = intval($store['zoom_level']);
        $level = json_encode($level, JSON_HEX_APOS|JSON_HEX_QUOT);
        return $level;

    }
}