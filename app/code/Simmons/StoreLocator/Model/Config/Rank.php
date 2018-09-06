<?php

namespace Simmons\StoreLocator\Model\Config;

class Rank implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Default')],
            ['value' => '9', 'label' => __('Black Diamond Dealer')]
        ];
    }
}