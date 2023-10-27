<?php

namespace Nati\OutsideSales\Model\Ideris;

use Nati\OutsideSales\Services\Ideris;

class IderisSales {

    protected $_ideris;

    public function __construct(Ideris $ideris)
    {
        $this->_ideris = $ideris;
    }

    public function getList($period_init, $period_end, $periodType = '')
    {
        $sales = [];
        $offset = 0;
        $loop = true;

        //faz um loop para pegar todos os resultados e paginas
        while($loop === true) {
            //Adiciona os resultados no final do array, acumulando um unico array com todos os resultados
            $list = $this->_ideris->sales($period_init, $period_end, $offset, 50, $periodType);
            if(!empty($list->result)) {
                
                foreach($list->result as $sale) {
                    $sales[] = (object) [
                        'provider' => 'ideris',
                        'provider_id' => $sale->id
                    ];
                }

                $offset += 50;

                if($list->paging->count + $list->paging->offset >= $list->paging->total) {
                    $loop = false;
                }
            }
            else {
                $loop = false;
            }
        }

        return $sales;
    }

    public function getOrder($order_id)
    {
        //retorna a order do ideris
        if($order_id != null) {
            $order = $this->_ideris->order($order_id);
            if(!empty($order->result)) {
                return $order->result[0];
            }
        }

        return null;
    }
}