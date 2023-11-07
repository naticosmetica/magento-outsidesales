<?php
namespace Nati\OutsideSales\Controller\Adminhtml\Order;

class Error extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Nati_OutsideSales::error_order';

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Lista de pedidos com erro'));
        
        return $resultPage;
    }
}
