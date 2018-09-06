<?php
namespace Simmons\StoreLocator\Controller\Search;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Simmons\StoreLocator\Model\LocatorRepository;

/**
 * Class Index
 * @package Simmons\StoreLocator\Controller\Search
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var LocatorRepository
     */
    protected $locatorRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param LocatorRepository $locatorRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LocatorRepository $locatorRepository
    ) {
        parent::__construct($context);
        $this->locatorRepository = $locatorRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $address = $this->getRequest()->getParam('zip_code');
        $distance = $this->getRequest()->getParam('distance');
        $productLineId = $this->getRequest()->getParam('product_line', 0);
        $storeData = [];

        try {
            if ($address && $distance) {
                $storeCollection = $this->locatorRepository->getStoresByAddress($address, $distance, $productLineId);
                foreach ($storeCollection as $store) {
                    $storeData[] = $store->getData();
                }
            } else {
                throw new LocalizedException(__('Input parameters are invalid'));
            }
        } catch (\Exception $e) {
            $error = array('error' => $e->getMessage());
            return $result->setData($error);
        }

        return $result->setData($storeData);
    }
}