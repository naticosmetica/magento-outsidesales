<?php

namespace Nati\OutsideSales\Model\Yampi;

use Nati\OutsideSales\Services\Yampi;
use Nati\OutsideSales\Services\FreteRapido;

class YampiSales {

    protected $_yampi;
    protected $_freteRapido;

    public function __construct(Yampi $yampi, FreteRapido $freteRapido)
    {
        $this->_yampi = $yampi;
        $this->_freteRapido = $freteRapido;
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

    public function getOrder($order_id, $type_query = 'order')
    {
        //retorna a order do yampi
        if($order_id != null) {

            // Verifica a forma de consultar o pedido
            if($type_query == 'number') {
                $order = $this->_yampi->orderByNumber($order_id);
            }
            else {
                $order = $this->_yampi->order($order_id);
            }
            
            if(!empty($order->data->id)) {

                $data = $order->data;

                //Consulta frete rapido se houver ID de nota
                $shipping = (object) [
                    'id' => null, 
                    'cost' => null
                ];
                if(!empty($data->services->data[0]->bling->external_id)) {
                    try {
                        $freteRapido = $this->_freteRapido->getByOrder($data->services->data[0]->bling->external_id);
                        $shipping->id = $freteRapido->id_frete ?? null;
                        $shipping->cost = number_format($freteRapido->transportadora->valor_cotado, 2, '.', '');
                    }
                    catch(\Exception $e) {
                        // throw new \Exception(json_encode($e->getMessage()));
                    }
                }

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
                    'idContaMarketplace' => 9000000000,
                    'marketplace' => 'Yampi',
                    'nomeContaMarketplace' => 'LOJA.YAMPI',

                    'codigo' => $data->id,
                    'status' => $data->status->data->name,
                    'data' => str_replace(' ','T',substr($data->created_at->date,0,19)).'-00:00',
                    'numeroRastreio' => $shipping->id,
                    'dataEntregue' => (!empty($data->date_delivery->date)) ? str_replace(' ','T',substr($data->date_delivery->date,0,19)).'-00:00' : '0000-00-00T00:00:00-00:00',
                    'tarifaEnvio' => $shipping->cost,
                    'freteComprador' => $data->value_shipment,
                    'valorTotalComFrete' => number_format($data->value_total - $data->value_shipment, 2, '.', ''), // REVER
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

    public function updateOrder($order_id, $params = [])
    {
        //retorna a order do yampi
        if($order_id != null && !empty($params) && count($params) > 0) {
            return $this->_yampi->updateOrder($order_id, $params);
        }

        return null;
    }

    public function queryStatus($status_id, $provider)
    {   
        // STATUS YAMPI
        // 3 - waiting_payment - Aguardando confirmação de pagamento
        // 4 - paid - O pedido foi pago/capturado
        // 8 - cancelled - O pedido foi cancelado
        // 9 - refused - Pagamento não aprovado
        // 10 - invoiced - O pedido foi faturado

        // 5 - handling_products - Os produtos estão em separação, aguardando postagem
        // 6 - on_carriage - O pedido já foi postado para a transportadora
        // 7 - delivered - O pedido foi entregue ao destinatário
        // 11 - Houve algum problema na entrega do pedido
        // 12 - ready_for_shipping - Pronto para envio

        $status_change = [
            'frete_rapido' => [
                '2' => null, //Em trânsito
                '3' => 7, //Entregue
                '5' => 11, //Entrega não realizada
                '6' => 11, //Reentrega / Primeira tentativa
                '7' => 11, //Devolução / Retorno
                '8' => 11, //Em trânsito para devolução
                '9' => 11, //Devolvido ao remetente
                '11' => 11, //Entrega parcial
                '12' => 11, //Devolução parcial
                '13' => 11, //Em trânsito para devolução parcial
                '14' => 11, //Devolvido parcialmente
                '15' => 6, //Coletado / Postado
                '16' => null, //Em transferência
                '17' => null, //Em rota para entrega
                '18' => 11, //Sinistro / Roubo
                '19' => null, //Disponível para retirada
                '20' => null, //Entrega agendada
                '21' => null, //Material não disponível para coleta
                '22' => 11, //Pedido recusado pelo destinatário
                '23' => null, //Redespacho Correios
                '24' => null, //Em processo de indenização
                '25' => null, //Indenização recusada pelo transportador
                '26' => null, //Em conferência na barreira fiscal
                '27' => 11, //Extravio
                '28' => 11, //Material avariado
                '29' => 11, //Difícil acesso / Área de risco
                '30' => null, //Redespacho transportadora
                '31' => null, //Greve nacional
                '32' => null, //Destinatário ausente
                '33' => null, //Indenização efetuada
                '34' => null, //Entrega não realizada - Feriado local
                '35' => null, //Reentrega / Terceira tentativa
                '36' => null, //Reentrega / Segunda tentativa
                '37' => null, //Chegada na cidade ou filial de destino
                '38' => 11, //Estabelecimento fechado
                '40' => 11, //Carga incompleta
                '41' => 11, //Destinatário mudou-se
                '42' => 11, //Endereço insuficiente
                '43' => 11, //Endereço não localizado
                '44' => 11, //Destinatário não localizado
                '45' => 11, //Destinatário desconhecido
                '97' => 11, //Problemas com endereço de entrega
                '98' => 11, //Problemas com a carga
                '99' => 11, //Cancelado
            ]
        ];

        if(!empty($status_change[$provider][$status_id])) {
            return $status_change[$provider][$status_id];
        }

        return null;
    }
}