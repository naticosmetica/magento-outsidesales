<?php

namespace Nati\OutsideSales\Model\ResourceModel;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('nati_marketplace_queue', 'id'); // Assumindo que 'entity_id' é a chave primária
    }
}
