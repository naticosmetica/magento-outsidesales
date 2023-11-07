<?php

namespace Nati\OutsideSales\Model\ResourceModel\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id'; // A chave primária da sua tabela
    protected $_eventPrefix = 'nati_marketplace_queue_collection';
    protected $_eventObject = 'queue_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Nati\OutsideSales\Model\Queue::class,
            \Nati\OutsideSales\Model\ResourceModel\Queue::class
        );
    }
}
