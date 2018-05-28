<?php
namespace Andronoid\Education\Plugin\Catalog;

use Magento\Catalog\Model\Product;

class ProductAround
{
    public function aroundGetName($interceptedInput)
    {
        return "name of product";
    }
}