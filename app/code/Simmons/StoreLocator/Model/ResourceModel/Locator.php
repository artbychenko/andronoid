<?php

namespace Simmons\StoreLocator\Model\ResourceModel;

use Netbaseteam\Locator\Model\ResourceModel\Locator as LocatorBase;

class Locator extends LocatorBase
{
    public function _disablePkAutoIncrement(){
        $this->_isPkAutoIncrement = false;
    }
}