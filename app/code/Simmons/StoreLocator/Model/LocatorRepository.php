<?php

namespace Simmons\StoreLocator\Model;

use Magento\Framework\Exception\LocalizedException;

class LocatorRepository
{
    /**
     * rray to map fields from api response to DB column names
     * @var array
     */
    protected $apiFieldsMap = [
        'localtor_id' => 'Dealer_Id',
        'store_name' => 'Dealer',
        'store_link' => 'URL',
        'phone_number' => 'Phone',
        'address' => 'Address',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'state' => 'State',
        'city' => 'City',
        'zip_code' => 'Zip'
    ];

    /**
     * @var SimmonsApi
     */
    protected $simmonsApi;

    /**
     * @var \Simmons\StoreLocator\Model\ResourceModel\Locator
     */
    protected $locatorResource;

    /**
     * @var \Netbaseteam\Locator\Model\LocatorFactory
     */
    protected $locatorFactory;

    /**
     * @var \Netbaseteam\Locator\Model\ResourceModel\Locator\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * LocatorRepository constructor.
     * @param SimmonsApi $simmonsApi
     * @param \Simmons\StoreLocator\Model\ResourceModel\Locator $locatorResource
     * @param \Netbaseteam\Locator\Model\LocatorFactory $locatorFactory
     * @param \Netbaseteam\Locator\Model\ResourceModel\Locator\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Simmons\StoreLocator\Model\SimmonsApi $simmonsApi,
        \Simmons\StoreLocator\Model\ResourceModel\Locator $locatorResource,
        \Netbaseteam\Locator\Model\LocatorFactory $locatorFactory,
        \Netbaseteam\Locator\Model\ResourceModel\Locator\CollectionFactory $collectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {

        $this->simmonsApi = $simmonsApi;
        $this->locatorResource = $locatorResource;
        $this->locatorFactory = $locatorFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filterManager = $filterManager;
    }

    /**
     * @param $address
     * @param $distance
     * @return \Netbaseteam\Locator\Model\ResourceModel\Locator\Collection
     * @throws LocalizedException
     */
    public function getStoresByAddress($address, $distance, $productLine = 0)
    {
        // get stores from simmons api
        $stores = $this->simmonsApi->getStoresByAddress($address, $distance, $productLine);

        if (!is_array($stores)) {
            throw new LocalizedException(__('API request failed.'));
        }
        $this->saveStores($stores);

        $idsForFilter = [];
        foreach ($stores as $store) {
            $idsForFilter[] = $store['Dealer_Id'];
        }

        /** @var \Netbaseteam\Locator\Model\ResourceModel\Locator\Collection $collection */
        $collection = $this->collectionFactory->create();
        /** @var \Netbaseteam\Locator\Model\Locator[] $stores */
        $collection->addFieldToFilter('status', array('eq' => '1'))
            ->addFieldToFilter('localtor_id', array('in' => $idsForFilter))
            ->setOrder('store_rank','DESC');

        return $collection;
    }

    /**
     * @param array $stores
     * Store data sample
     * {
     * "Zip": 22312,
     * "Distance": 9.813995109852984,
     * "ProductDisplay": "<p>Beautyrest Black&reg; Hybrid</p>\r\n, <p>Beautyrest Black&reg; Memory Foam</p>\r\n, , <p>Beautyrest&reg; Legend&trade;</p>\r\n, , <p>Beautyrest Recharge World Class&reg;</p>\r\n",
     * "Longitude": -77.1396769,
     * "Phone": "571-528-4357",
     * "State": "VA ",
     * "Street": "6198 B Little River Tpke Plaza At Landmark",
     * "Dealer": "Mattress Firm",
     * "URL": "www.mattressfirm.com",
     * "CR": "",
     * "SAID": 7446630,
     * "RankBase": "",
     * "City": "Alexandria",
     * "Dealer_Id": 39264,
     * "Address": "6198 B Little River Tpke Plaza At Landmark, Alexandria, VA  22312",
     * "StatusLevel": "Black Diamond Preferred",
     * "Latitude": 38.8183623,
     * "StoreCount": 0
     * }
     * @return void
     * @throws LocalizedException
     */
    protected function saveStores($stores)
    {
        $idsFromApi = [];
        foreach ($stores as $store) {
            $idsFromApi[] = $store['Dealer_Id'];
        }

        /** @var \Netbaseteam\Locator\Model\ResourceModel\Locator\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('localtor_id', array('in' => $idsFromApi));

        $existingIds = $collection->getAllIds();
        $idsToSave = array_diff($idsFromApi, $existingIds);

        if (!$idsToSave) {
            return;
        }

        $this->locatorResource->_disablePkAutoIncrement();

        foreach ($stores as $store) {
            if (!in_array($store['Dealer_Id'], $idsToSave)) {
                continue;
            }

            $data = [];
            foreach ($this->apiFieldsMap as $dbField => $apiField) {
                $data[$dbField] = $store[$apiField];
            }
            $data['identifier'] = $this->filterManager->translit($data['store_name']);
            $data['status'] = 1;
            $data['schedule_id'] = 0;
            $data['zoom_level'] = 1;
            switch ($store['StatusLevel']) {
                case 'Black Diamond Preferred':
                    $data['store_rank'] = 9;
                    break;
                default:
                    $data['store_rank'] = 1;
                    break;
            }

            $model = $this->locatorFactory->create();
            $model->setData($data);
            $model->isObjectNew(true);

            try {
                $this->locatorResource->save($model);
            } catch (\Exception $e) {
                if ($e instanceof LocalizedException) {
                    $message = $e->getMessage();
                } else {
                    $message = __('Something went wrong while saving stores from API');
                }
                throw new LocalizedException($message);
            }
        }
    }
}
