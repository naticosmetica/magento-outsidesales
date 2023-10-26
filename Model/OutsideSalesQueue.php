<?php

namespace Nati\OutsideSales\Model;

use Magento\Framework\App\ObjectManager;
use Nati\OutsideSales\Model\Ideris\IderisSales;
use Nati\OutsideSales\Model\Customer\Customer;
use Nati\OutsideSales\Model\Marketplace\Marketplace;
use Nati\OutsideSales\Model\Marketplace\Sales;
use Nati\OutsideSales\Model\Marketplace\Item;

class OutsideSalesQueue {

    protected $_ideris;
    protected $_customer;
    protected $_marketplace;
    protected $_marketplaceSales;
    protected $_marketplaceItem;
    
    protected $_resource;
    protected $_connection;

    public function __construct(
        IderisSales $ideris, 
        Customer $customer,
        Marketplace $marketplace,
        Sales $marketplaceSales,
        Item $marketplaceItem
    ) {
        $this->_ideris = $ideris;
        $this->_customer = $customer;
        $this->_marketplace = $marketplace;
        $this->_marketplaceSales = $marketplaceSales;
        $this->_marketplaceItem = $marketplaceItem;

        // Inicia conexão com o banco de dados
        $objectManager = ObjectManager::getInstance();
        $this->_resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_connection = $this->_resource->getConnection();
    }

    public function updateList($period_init, $period_end)
    {
        // Inicia dados na tabela nati_cron_marketplace_logs
        $tableName = $this->_resource->getTableName('nati_cron_marketplace_logs');

        $this->_connection->query("INSERT INTO " . $tableName . " (period_init, period_end, qty_orders, qty_news, qty_executed) VALUES ('". $period_init ."', '". $period_end ."', 0, 0, 0)");
        $marketplaceLogId = $this->_connection->lastInsertId();

        // Verifica se o insert foi realizado com sucesso
        if($marketplaceLogId == null) {
            throw new \Exception('Não foi possível inserir o log de execução.');
        }

        $qty_news = 0;
        $qty_executed = 0;
        $message = [];

        try {

            // Retorna a array com as vendas
            $sales = [];
            $sales_ideris = $this->_ideris->getList($period_init, $period_end);
            // $sales_yamp = $this->_yamp->getList($period_init, $period_end);
            $sales = array_merge($sales, $sales_ideris); //, $sales_yamp, $sales_b2b, .... adicoinar outras vendas quando houver

            //Atualiza a quantidade de pedidos encontrados
            $this->_connection->query("UPDATE " . $tableName . " SET qty_orders = '". count($sales) ."' WHERE id = ". $marketplaceLogId ." LIMIT 1");

            //Faz o tratamento dos dados e insere na tabela nati_marketplace_queue para serem executados
            foreach($sales as $sale) {
                //Verifica se o pedido já existe na tabela
                $result = $this->_connection->fetchAll("SELECT * FROM nati_marketplace_queue WHERE provider = '". $sale->provider ."' AND provider_id = '". $sale->provider_id ."' LIMIT 1");
                
                //Se não existir cadastra na tabela
                if(count($result) == 0) {
                    $qty_news++;
                    $this->_connection->query("INSERT INTO nati_marketplace_queue (cron_id, provider, provider_id, status) VALUES (". $marketplaceLogId .", '". $sale->provider ."', '". $sale->provider_id ."', 'pending')");
                    
                    //Se houver erro no cadastro, salva no log
                    if($this->_connection->lastInsertId() == null) {
                        $message[] = $sale->provider .' - '. $sale->provider_id;
                    } 
                    else {
                        $qty_executed++;
                    }
                }
            }

            //Verifica se todos os pedidos foram inseridos na tabela
            $status = 'success';
            if($qty_news != $qty_executed) {
                $status = 'error';
            }
        }
        catch(\Exception $e) {
            $status = 'error';
            $message[] = $e->getMessage();
        }

        //Atualiza os dados do log de execução
        $this->_connection->query("UPDATE " . $tableName . " SET status = '". $status ."',  qty_news = ". $qty_news .", qty_executed = ". $qty_executed .", message = '". implode(', ', $message) ."' WHERE id = ". $marketplaceLogId ." LIMIT 1");

        return true;
    }
    
    public function validateList($ids = [])
    {
        $tableMkpQueue = $this->_resource->getTableName('nati_marketplace_queue');
        $tableMkps = $this->_resource->getTableName('nati_mktplaces');

        // Verifica se foi passado algum ID para validar
        $where = "WHERE status = 'pending'";
        if(!empty($ids) && count($ids) > 0) {
            $where = "WHERE id IN (". implode(',', $ids) .") AND status = 'error'";
        }

        $result = $this->_connection->fetchAll("SELECT * FROM " . $tableMkpQueue . " " . $where);

        // Verifica se existem registros para serem validados
        if(count($result) == 0) {
            throw new \Exception('Não existem registros para serem validados.');
        }

        // Se houver registros, faz um loop para pegar o ideris_id de cada um e fazer a validação
        foreach($result as $item) {

            // Atualiza o status para validando
            $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'validating' WHERE id = ". $item['id'] ." LIMIT 1");

            $order = $this->_ideris->getOrder($item['provider_id']);

            $error = [];
            if(!empty($order)) {

                try {
                    $customerId = $this->_customer->getIdCustomerForDocument($order->compradorDocumento);
                    
                    //Cria usuario caso nao exista
                    if(empty($customerId)) {
                        $customerId = $this->_customer->createCustomer([
                            'firstname' => $order->compradorPrimeiroNome,
                            'lastname' => $order->compradorSobrenome,
                            'document' => $order->compradorDocumento
                        ]);
                    }

                    //Verifica se nao existe / nao criou e trava para salvar o erro
                    if(empty($customerId)) {
                        $error[] = 'Erro ao vincular um cliente';
                    }
                    
                    //Adiciona endereço ao cliente
                    $this->_customer->addAddressesToCustomer($customerId, [
                        'firstname' => $order->compradorPrimeiroNome,
                        'lastname' => $order->compradorSobrenome,
                        'postcode' => $order->enderecoEntregaCep,
                        'city' => $order->enderecoEntregaCidade,
                        'street' => $order->enderecoEntregaCompleto,
                        'region' => $order->enderecoEntregaEstado,
                    ]);

                    //Cadastra marketplace
                    $marketplace = $this->_marketplace->getOrCreate($order->idContaMarketplace, $order->marketplace, $order->nomeContaMarketplace);
                    if(empty($marketplace)) {
                        $error[] = 'Erro ao vincular um marketplace';
                    }

                    //Verifica se o pedido não esta duplicado
                    $sale = $this->_marketplaceSales->getByOrder($order->codigo);
                    if(!empty($sale)) {
                        $error[] = 'Código '. $order->codigo .' duplicado';
                    }

                    //Verifica se possui o ID da entrafa
                    if(empty($order->numeroRastreio)) {
                        $error[] = 'Não possui número de rastreio definido';
                    }
                    
                    //Verifica se possui forma de pagamento
                    if(empty($order->Pagamento->formaPagamento)) {
                        $error[] = 'Não possui código de rastreio definido';
                    }
                
                    //Varre os items para verificar se encontra o SKU e categoria cadastrada
                    foreach($order->Item as $orderItem) {
                        if(empty($orderItem->skuProdutoItem)) {
                            $error[] = 'Produto '. $orderItem->tituloProdutoItem .', não possui SKU definido';
                            continue;
                        }

                        if(empty($product)) {
                            $error[] = 'SKU '. $orderItem->skuProdutoItem .' não encontrado no magento';
                            continue;
                        }

                        if(empty($product->getCost())) {
                            $error[] = 'SKU '. $orderItem->skuProdutoItem .' não possui valor de custo definido';
                        }

                        $categoryIds = $this->_marketplaceItem->category();
                        if(empty($categoryIds)) {
                            $error[] = 'SKU '. $orderItem->skuProdutoItem .' não possui categoria deifnida';
                        }
                    }
                }
                catch(\Exception $e) {
                    // Adiciona o erro e a linha em que o erro foi gerado
                    $error[] = $e->getLine() .' - '. $e->getFile() .' - '.$e->getMessage();
                }
            }
            else {
                $error[] = 'Pedido '. $item['provider_id'] .' não encontrado no '. $item['provider'];
            }

            if(count($error) > 0) {
                //Atualiza o status para erro
                $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'error', message = '". implode(';', $error) ."' WHERE id = ". $item['id'] ." LIMIT 1");
            }
            else {
                //Atualiza o status para validado
                $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'validated', message = NULL WHERE id = ". $item['id'] ." LIMIT 1");
            }
        }
    }

    public function executeList()
    {
        $tableMkpQueue = $this->_resource->getTableName('nati_marketplace_queue');
        $tableSales = $this->_resource->getTableName('nati_marketplace_sales');

        $result = $this->_connection->fetchAll("SELECT * FROM " . $tableMkpQueue . " WHERE status = 'validated'");

        // Verifica se existem registros para serem validados
        if(count($result) == 0) {
            throw new \Exception('Não existem registros para serem executados.');
        }

        // Se houver registros, faz um loop para pegar o ideris_id de cada um e fazer a validação
        foreach($result as $item) {

            try {

                // Atualiza o status para executando
                $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'executing' WHERE id = ". $item['id'] ." LIMIT 1");

                $order = $this->_ideris->getOrder($item['provider_id']);

                $customerId = $this->_customer->getIdCustomerForDocument($order->compradorDocumento);

                $marketplace = $this->_marketplace->get($order->idContaMarketplace);

                //Salva os dados do pedido em nati_mktplace_sales
                $saleId = $this->_marketplaceSales->create([
                    'marketplace_id' => $marketplace['id'],
                    'customer_id' => $customerId,
                    'order_id' => $order->codigo,
                    'order_status' => $order->status,
                    'order_date' => $order->data,
                    'provider_type' => $item['provider'],
                    'provider_sale_id' => $order->id,
                    'shipping_id' => $order->numeroRastreio,
                    'shipping_date' => $order->dataEntregue,
                    'shipping_value' => number_format($order->tarifaEnvio - $order->freteComprador, 2, '.', ''),
                    'payment_type' => $order->Pagamento[0]->formaPagamento,
                    'total_value' => $order->valorTotalComFrete,
                    'gateway_value' => 0, //Valor que apenas a yamp irá trazer
                    'mkp_value' => $order->tarifaVenda,
                    'picking_value' => number_format($order->valorTotalComFrete * .01, 2, '.', ''), // Calcular (1% do valor total)
                    'tax_value' => number_format($order->valorTotalComFrete * 0.1528, 2, '.', ''), // Calcular (15,28% do valor total)
                ]);

                //Salva os items do pedido
                $gatewayValue = 0;
                foreach($order->Item as $orderItem) {
                    $product = $this->_marketplaceItem->getProduct($orderItem->skuProdutoItem);

                    $this->_marketplaceItem->create([
                        'sale_id' => $saleId,
                        'product_id' => $product->getId(),
                        'sku' => $orderItem->skuProdutoItem,
                        'name' => $orderItem->tituloProdutoItem,
                        'value' => $orderItem->precoUnitarioItem,
                        'quantity' => $orderItem->quantidadeItem,
                        'total_value' => number_format($orderItem->quantidadeItem * $orderItem->precoUnitarioItem, 2, '.', ''),
                        'cost_value' => $product->getCost()
                    ]);
                }

                //Atualiza o status para completed
                $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'completed' WHERE id = ". $item['id'] ." LIMIT 1");
            }
            catch(\Exception $e) {

                //Verifica se houve cadastro de item e remove (rollback)
                if(!empty($saleId)) {
                    $this->_connection->query("DELETE FROM " . $tableSales . " WHERE id = ". $saleId);
                }

                //Verifica se houve cadastro de pedido e remove (rollback)
                if(!empty($saleId)) {
                    $this->_connection->query("DELETE FROM " . $tableSales . " WHERE id = ". $saleId ." LIMIT 1");
                }

                //Atualiza o status para error
                $this->_connection->query("UPDATE " . $tableMkpQueue . " SET status = 'error', message = '". $e->getMessage() ."' WHERE id = ". $item['id'] ." LIMIT 1");
            }
        }
                
        /*
        - Fazer uma funcao no console, para que adicione na fila novamente todos os itens que estao com erro e reprocesse
            - Fazer a opcao também de passar o parametro do ID do item para que seja reprocessado apenas um item
        - Ver se consigo retornar a exibicao de cada item do pedido sendo cadastrado no console
        - Salvar tudo e subir como versao 1.0.0 do módulo e testar em homologacao
        */
    }

    public function revalidateList($id = null)
    {
        // Consulta a tabela nati_marketplace_queue com status error e executa novamente
        $tableMkpQueue = $this->_resource->getTableName('nati_marketplace_queue');

        $where = "WHERE status = 'error'";
        if(!empty($id)) {
            $where .= " AND id IN (". $id .")";
        }

        $result = $this->_connection->fetchAll("SELECT * FROM " . $tableMkpQueue . " ". $where);

        // Verifica se existem registros para serem validados
        if(count($result) == 0) {
            throw new \Exception('Não existem registros com erros para serem reexecutados.');
        }

        // Se houver registros, volta o status para pending e executa novamente a funcao validateList
        $ids = [];
        foreach($result as $item) {
            $ids[] = $item['id'];
        }

        // Verifica se existe lista de IDs para executar
        if(count($ids) == 0) {
            throw new \Exception('Não encontrado registros com erros para serem reexecutados.');
        }

        $this->validateList($ids);
    }
}