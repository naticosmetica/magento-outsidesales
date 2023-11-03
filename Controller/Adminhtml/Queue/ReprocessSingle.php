<?php

namespace Nati\OutsideSales\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Nati\OutsideSales\Model\QueueFactory;

class ReprocessSingle extends Action
{
    protected $queueFactory;

    public function __construct(
        Context $context,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->queueFactory->create()->load($id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('Este item não existe.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        // Chame sua função para reprocessar o item aqui
        // Exemplo: $this->seuModelo->reprocess($model);

        $this->messageManager->addSuccessMessage(__('O item foi reprocessado com sucesso.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
