<?php

namespace Simmons\StoreLocator\Model\Source;

class ProductLine implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $productLines = [
        '0' => 'All Products',
        '1077' => 'Beautyrest Black® Hybrid',
        '1080' => 'Beautyrest Black® Memory Foam',
        '1025' => 'Beautyrest Black®',
        '1040' => 'Beautyrest Recharge World Class®',
        '1078' => 'Beautyrest® Legend™',
        '1052' => 'Beautyrest® Platinum™ Hybrid',
        '1051' => 'Beautyrest® Platinum™',
        '1076' => 'Beautyrest® Silver™ Hybrid',
        '1075' => 'Beautyrest® Silver™',
        '1053' => 'Beautyrest SmartMotion™ Base',
        '1048' => 'Beautyrest® Pillows',
        '1079' => 'Beautyrest® Sleeptracker®',
        '1082' => 'Beautyrest® ST Mattress-In-A-Box',
        '1004' => 'BeautySleep®',
        '1084' => 'Simmons® BeautySleep® Mattress-In-A-Box 10"',
        '1083' => 'Simmons® BeautySleep® Mattress-In-A-Box 8"'
    ];

    /**
     * Return array of options as value => label items
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->productLines;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->productLines as $productLineId => $productName) {
            $options[] = ['value' => $productLineId, 'label' => $productName];
        }
        return $options;
    }
}
