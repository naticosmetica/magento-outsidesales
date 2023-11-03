<?php
namespace Nati\OutsideSales\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Nati\OutsideSales\Model\ResourceModel\Queue\CollectionFactory;

class Reprocess extends Action
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
        $count = 0;

        foreach ($collection as $item) {
            // Aqui você vai chamar sua função que reprocessa cada item.
            // Por exemplo: $this->seuModelo->reprocess($item);
            $count++;
        }

        $this->messageManager->addSuccessMessage(__('Um total de %1 registro(s) foram reprocessados.', $count));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
