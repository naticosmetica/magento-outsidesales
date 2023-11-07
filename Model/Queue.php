<?php

namespace Nati\OutsideSales\Model;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Nati\OutsideSales\Model\ResourceModel\Queue::class);
    }
}
