<?php

namespace Nati\OutsideSales\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Nati\OutsideSales\Model\ResourceModel\Queue\CollectionFactory;

class MassReprocess extends Action
{
    protected $filter;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $reprocessedCount = 0;
        foreach ($collection as $item) {
            try {
                // Aqui, você deve adicionar a lógica para reprocessar cada item da fila
                // $item->reprocess(); // Exemplo

                $reprocessedCount++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if ($reprocessedCount) {
            $this->messageManager->addSuccessMessage(__('Um total de %1 registro(s) foram reprocessados.', $reprocessedCount));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }
}
