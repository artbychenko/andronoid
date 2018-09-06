<?php

namespace Simmons\StoreLocator\Model\Source;

class Distance implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $distanceOptions = [
        '5' => '5 Miles',
        '10' => '10 Miles',
        '25' => '25 Miles',
        '50' => '50 Miles'
    ];

    /**
     * Return array of options as value => label items
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->distanceOptions;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->distanceOptions as $optionId => $optionName) {
            $options[] = ['value' => $optionId, 'label' => $optionName];
        }
        return $options;
    }
}
