<?php

namespace Nati\OutsideSales\Model\ResourceModel\Order\Error;

use Nati\OutsideSales\Model\ResourceModel\Queue\CollectionFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Nati\OutsideSales\Model\ResourceModel\Order\Error\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->collection->isLoaded()) {
            $this->collection->load();
        }
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($this->collection->toArray()),
        ];
    }

    /**
     * Add error filter to collection
     */
    protected function addErrorFilter()
    {
        $this->collection->addFieldToFilter('status', ['eq' => 'error']);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() === 'status' && $filter->getValue() === 'error') {
            $this->addErrorFilter();
        } else {
            parent::addFilter($filter);
        }
    }
}
