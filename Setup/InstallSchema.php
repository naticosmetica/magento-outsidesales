<?php

namespace Nati\OutsideSales\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup, 
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('nati_mktplaces')) {
            /*
            *
            * id: int(11) NOT NULL AUTO_INCREMENT
            * marketplace_id: varchar(30) NOT NULL
            * marketplace: varchar(255) NOT NULL
            * account_name: varchar(255) NOT NULL
            * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('nati_mktplaces')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 
                    'nullable' => false, 
                    'primary' => true, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'marketplace_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                30,
                [
                    'nullable' => false
                ],
            )
            ->addColumn(
                'marketplace',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
            )
            ->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Criado em'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Atualizado em'
            )
            ->setComment('Tabela de marketplaces');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('nati_mktplace_sales')) {

            /*
            * Cria a tabela nati_mktplace_sales
            *
            * Armazena as vendas realizadas nos marketplaces
            *
            * id: int(11) NOT NULL AUTO_INCREMENT
            * marketplace_id: int(11) NOT NULL
            * customer_id: int(11) NOT NULL
            * order_id: varchar(255) NULL
            * order_status: varchar(50) NULL
            * order_date: datetime NULL
            * provider_type: varchar(50) NULL (ideris, b2w, etc)
            * provider_sale_id: varchar(255) NULL 
            * shipping_id: varchar(255) NULL
            * shipping_date: datetime NULL
            * shipping_value: decimal(10,2) NULL
            * payment_type: varchar(50) NULL
            * total_value: decimal(10,2) NULL
            * gateway_value: decimal(10,2) NULL
            * mkp_value: decimal(10,2) NULL
            * picking_value: decimal(10,2) NULL
            * tax_value: decimal(10,2) NULL
            * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            */
            $table = $installer->getConnection()->newTable(
				$installer->getTable('nati_mktplace_sales')
			)
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 
                    'nullable' => false, 
                    'primary' => true, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'marketplace_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'order_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'order_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'provider_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'provider_sale_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'shipping_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'shipping_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'shipping_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'payment_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'total_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'gateway_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'mkp_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'picking_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'tax_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addForeignKey(
                $installer->getFkName(
                    'nati_mktplace_sales',
                    'marketplace_id',
                    'nati_mktplaces',
                    'id'
                ),
                'marketplace_id',
                $installer->getTable('nati_mktplaces'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'nati_mktplace_sales',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Criado em'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Atualizado em'
            )
            ->setComment('Tabela de vendas dos marketplaces');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('nati_mktplace_sales_items')) {
            /*
            * Cria a tabela nati_mktplace_sales_items
            *
            * Armazena os items de cada venda
            *
            * id: int(11) NOT NULL AUTO_INCREMENT
            * sale_id: int(11) NOT NULL
            * product_id: int(11) NOT NULL
            * sku: varchar(255) NULL
            * name: varchar(255) NULL
            * value: decimal(10,2) NULL
            * quantity: int(11) NULL
            * total_value: decimal(10,2) NULL
            * cost_value: decimal(10,2) NULL
            * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            */
            $table = $installer->getConnection()->newTable(
				$installer->getTable('nati_mktplace_sales_items')
			)
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 
                    'nullable' => false, 
                    'primary' => true, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'sale_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 
                    'unsigned' => true
                ],
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'quantity',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'total_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addColumn(
                'cost_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [
                    'nullable' => true
                ],
            )
            ->addForeignKey(
                $installer->getFkName(
                    'nati_mktplace_sales_items',
                    'sale_id',
                    'nati_mktplace_sales',
                    'id'
                ),
                'sale_id',
                $installer->getTable('nati_mktplace_sales'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'nati_mktplace_sales_items',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Criado em'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Atualizado em'
            )
            ->setComment('Tabela de items das vendas dos marketplaces');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('nati_cron_marketplace_logs')) {
            /*
            * Cria tabela de controle do CRON
            * 
            * id: int(11) NOT NULL AUTO_INCREMENT
            * period_init: datetime NOT NULL
            * period_end: datetime NOT NULL
            * qty_orders: int(11) NOT NULL
            * qty_news: int(11) NOT NULL
            * qty_executed: int(11) NOT NULL
            * status: varchar(50) NULL DEFAULT running (running, error, success)
            * message: text
            * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('nati_cron_marketplace_logs')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ],
                'ID'
            )
            ->addColumn(
                'period_init',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ],
                'Periodo inicial da consulta'
            )
            ->addColumn(
                'period_end',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ],
                'Periodo final da consulta'
            )
            ->addColumn(
                'qty_orders',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Quantidade de pedidos'
            )
            ->addColumn(
                'qty_news',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Quantidade de pedidos novos'
            )
            ->addColumn(
                'qty_executed',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Quantidade de pedidos executados'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false,
                    'default' => 'running'
                ],
                'Status da execucao'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Mensagem de erro'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Criado em'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Atualizado em'
            )
            ->setComment('Tabela de controle do CRON');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('nati_marketplace_queue')) {
            /*
            * Cria tabela da fila de execucoes
            * 
            * id: int(11) NOT NULL AUTO_INCREMENT
            * cron_id: int(11) NOT NULL
            * provider: varchar(255) NOT NULL
            * provider_id: varchar(255) NOT NULL
            * status: varchar(50) NOT NULL
            * message: text
            * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
            * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('nati_marketplace_queue')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ],
                'ID'
            )
            ->addColumn(
                'cron_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'ID do CRON'
            )
            ->addColumn(
                'provider',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Nome do marketplace'
            )
            ->addColumn(
                'provider_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'ID do marketplace'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false
                ],
                'Status da execucao'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Mensagem de erro'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'Criado em'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                ],
                'Atualizado em'
            )
            ->setComment('Tabela da fila de execucoes');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}