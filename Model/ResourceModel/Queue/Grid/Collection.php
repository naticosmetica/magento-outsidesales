<?php

namespace Nati\OutsideSales\Model\ResourceModel\Queue\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Nati\OutsideSales\Model\Queue;
use Nati\OutsideSales\Model\ResourceModel\Queue as QueueResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id'; // Substitua 'id' pelo campo primário da sua tabela
    protected $_eventPrefix = 'nati_marketplace_queue_collection';

    protected function _construct()
    {
        $this->_init(Queue::class, QueueResource::class);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // Selecione apenas os registros com status "error"
        $this->addFieldToFilter('status', ['eq' => 'error']);

        return $this;
    }
}
