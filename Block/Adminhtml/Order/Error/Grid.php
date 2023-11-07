<?php
namespace Nati\OutsideSales\Block\Adminhtml\Order\Error;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order_error';
        $this->_blockGroup = 'Nati_OutsideSales';
        $this->_headerText = __('Error Orders');
        parent::_construct();
        $this->removeButton('add');
    }
}
