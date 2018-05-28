<?php
namespace Andronoid\Education\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Education extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_productCollectionFactory;

    /**
     * Education constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, CollectionFactory $productCollectionFactory, array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set('my title');
    }

    public function getArt ()
    {
        return 'Artem';
    }

    public function getNewProducts()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection
            ->addAttributeToSelect('*')
            ->setOrder('created_at')
            ->setPageSize(5);
        return $collection;
    }
}