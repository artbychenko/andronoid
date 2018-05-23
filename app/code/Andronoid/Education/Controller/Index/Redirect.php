<?php

namespace Andronoid\Education\Controller\Index;

class Redirect extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}