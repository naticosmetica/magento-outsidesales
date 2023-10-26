<?php

namespace Nati\OutsideSales\Model\Marketplace;

use Magento\Framework\App\ObjectManager;

class Sales {

    protected $_connection;
    protected $_resource;

    public function __construct()
    {
        // Inicia conexão com o banco de dados
        $objectManager = ObjectManager::getInstance();
        $this->_resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_connection = $this->_resource->getConnection();
    }

    public function getByOrder($orderId)
    {
        $tableSales = $this->_resource->getTableName('nati_mktplace_sales');
        $result = $this->_connection->fetchAll("SELECT * FROM " . $tableSales . " WHERE order_id = '". $orderId ."' LIMIT 1");

        //Verifica se existe na tabela
        if(count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    public function create($data)
    {
        $tableSales = $this->_resource->getTableName('nati_mktplace_sales');

        // Pega as keys da variavel data e agrupo por virgula
        $keys = implode(',', array_keys($data));

        // Pega as values da variavel data e agrupa por virgula e aspas simples
        $values = "'". implode("','", array_values($data)) ."'";

        // Faz o isert no banco de dados
        $this->_connection->query("INSERT INTO " . $tableSales . " (". $keys .") VALUES (". $values .")");

        // Retorna o ID do registro inserido ou null caso não tenha sido inserido
        return $this->_connection->lastInsertId();
    }

    public function update($saleId, $data)
    {
        $this->_connection->query("UPDATE " . $tableSales . " SET shipping_value = '". $order->freteComprador ."', total_value = '". $order->valorTotalComFrete ."', gateway_value = '". $order->tarifaVenda ."', mkp_value = '". $order->Pagamento->valorComissao ."', picking_value = '". $order->Pagamento->valorPicking ."', tax_value = '". $order->Pagamento->valorTaxa ."' WHERE order_id = '". $order->codigo ."' LIMIT 1");

    }
}