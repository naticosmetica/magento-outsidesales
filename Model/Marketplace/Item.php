<?php

namespace Nati\OutsideSales\Model\Marketplace;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Item {
    
    protected $_productRepository;
    protected $_connection;
    protected $_resource;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->_productRepository = $productRepository;

        // Inicia conexão com o banco de dados
        $objectManager = ObjectManager::getInstance();
        $this->_resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_connection = $this->_resource->getConnection();
    }

    public function getProductBySku($sku) {
        try {
            $product = $this->_productRepository->get($sku);
            return $product;
        } catch (NoSuchEntityException $e) {
            // Produto não encontrado
            return null;
        }
    }

    public function category($sku) {
        try {
            $product = $this->_productRepository->get($sku);
            return $product->getCategoryIds();
        } catch (NoSuchEntityException $e) {
            // Produto não encontrado
            return null;
        }
    }

    public function create($data) {
        $tableItems = $this->_resource->getTableName('nati_mktplace_sales_items');

        // Pega as keys da variavel data e agrupo por virgula
        $keys = implode(',', array_keys($data));

        // Pega as values da variavel data e agrupa por virgula e aspas simples
        $values = "'". implode("','", array_values($data)) ."'";

        // Faz o isert no banco de dados
        $this->_connection->query("INSERT INTO " . $tableItems . " (". $keys .") VALUES (". $values .")");

        // Retorna o ID do registro inserido ou null caso não tenha sido inserido
        return $this->_connection->lastInsertId();
    }
}