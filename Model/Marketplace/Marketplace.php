<?php

namespace Nati\OutsideSales\Model\Marketplace;

use Magento\Framework\App\ObjectManager;

class Marketplace {

    protected $_connection;
    protected $_resource;

    public function __construct()
    {
        // Inicia conexão com o banco de dados
        $objectManager = ObjectManager::getInstance();
        $this->_resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_connection = $this->_resource->getConnection();
    }

    public function getOrCreate($marketplaceId, $marketplaceName, $accountName)
    {
        //Consulta marketplace
        $marketplace = $this->get($marketplaceId);

        //Se nao existir cadastra na tabela
        if(empty($marketplace)) {
            $this->create($marketplaceId, $marketplaceName, $accountName);
            $marketplace = $this->get($marketplaceId);
        }
        
        return $marketplace;
    }

    public function get($marketplaceId)
    {
        $tableMkps = $this->_resource->getTableName('nati_mktplaces');
        $result = $this->_connection->fetchAll("SELECT * FROM " . $tableMkps . " WHERE marketplace_id = '". $marketplaceId ."' LIMIT 1");

        //Verifica se existe o marketplace na tabela
        if(count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    public function create($marketplaceId, $marketplaceName, $accountName) 
    {
        $tableMkps = $this->_resource->getTableName('nati_mktplaces');
        $this->_connection->query("INSERT INTO " . $tableMkps . " (marketplace_id, marketplace, account_name) VALUES ('". $marketplaceId ."', '". $marketplaceName ."', '". $accountName ."')");

        return $this->_connection->lastInsertId();
    }
}