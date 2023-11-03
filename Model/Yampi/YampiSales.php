<?php

namespace Nati\OutsideSales\Model\Yampi;

use Nati\OutsideSales\Services\Yampi;

class YampiSales {

    protected $_yampi;

    public function __construct(Yampi $yampi)
    {
        $this->_yampi = $yampi;
    }

    public function getList($period_init, $period_end, $periodType = '')
    {
        $sales = [];
        $page = 1;
        $loop = true;

        //faz um loop para pegar todos os resultados e paginas
        while($loop === true) {
            //Adiciona os resultados no final do array, acumulando um unico array com todos os resultados
            $list = $this->_yampi->sales($period_init, $period_end, $page, 50, $periodType);
            if(!empty($list->data) && count($list->data) > 0) {
                
                foreach($list->data as $sale) {
                    $sales[] = (object) [
                        'provider' => 'yampi',
                        'provider_id' => $sale->id,
                        'status' => $sale->status->data->name
                    ];
                }

                $page++;

                if($list->meta->pagination->current_page >= $list->meta->pagination->total_pages) {
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
        //retorna a order do yampi
        if($order_id != null) {

            $order = $this->_yampi->order($order_id);

            if(!empty($order->data->id)) {

                $data = $order->data;

                //Redireciona para valores semelhantes aos utilizados no Ideris
                $replace = (object) [
                    'id' => $data->id, 
                    'compradorPrimeiroNome' => $data->customer->data->first_name,
                    'compradorSobrenome' => $data->customer->data->last_name,
                    'compradorDocumento' => $data->customer->data->cpf,
                    'compradorEmail' => $data->customer->data->email,

                    'enderecoEntregaCep' => $data->shipping_address->data->zipcode,
                    'enderecoEntregaCidade' => $data->shipping_address->data->city,
                    'enderecoEntregaCompleto' => $data->shipping_address->data->full_address,
                    'enderecoEntregaEstado' => $data->shipping_address->data->state,

                    // Por tudo como Yampi
                    'idContaMarketplace' => 1,
                    'marketplace' => 'Yampi',
                    'nomeContaMarketplace' => 'LOJA.YAMPI',

                    'codigo' => $data->id,
                    'status' => $data->status->data->name,
                    'data' => str_replace(' ','T'substr($data->created_at->date,0,19)).'-03:00',
                    'numeroRastreio' => $data->track_code, // REVER
                    'dataEntregue' => (!empty($data->date_delivery->date)) ? str_replace(' ','T'substr($data->date_delivery->date,0,19)).'-03:00' : '0000-00-00T00:00:00-03:00',
                    'tarifaEnvio' => $data->shipment_cost,
                    'freteComprador' => $data->value_shipment,
                    'valorTotalComFrete' => $data->value_total,
                    'tarifaVenda' => number_format($data->value_total * .015, 2, '.', ''), //1.5% média passada pelo Daniel
                    'tarifaGateway' => number_format($data->value_total * .06, 2, '.', ''), //6% média passada pelo Daniel
                    'Pagamento' => [
                        (object) [
                            'formaPagamento' => $data->transactions->data[0]->payment->data->alias,
                        ]
                    ],
                    'Item' => []
                ];

                // Retorna os pedidos do yampi
                foreach($data->items->data as $item) {
                    $replace->Item[] = (object) [
                        'skuProdutoItem' => $item->sku->data->sku,
                        'tituloProdutoItem' => $item->sku->data->title,
                        'precoUnitarioItem' => $item->price,
                        'quantidadeItem' => $item->quantity,
                    ];
                }

                return $replace;
            }
        }

        return null;
    }
}