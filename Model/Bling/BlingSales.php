<?php

namespace Nati\OutsideSales\Model\Bling;

use Nati\OutsideSales\Services\Bling;

class BlingSales {

    protected $_bling;

    public function __construct(Bling $bling)
    {
        $this->_bling = $bling;
    }

    public function getOrder($id)
    {
        //retorna a order do yampi
        if($id != null) {
            $order = $this->_bling->getByOrder($id);
            if(!empty($order->data[0]->id)) {
                return $order->data[0];
            }
        }

        return null;
    }
}