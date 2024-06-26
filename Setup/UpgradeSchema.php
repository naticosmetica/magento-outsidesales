<?php

namespace Nati\OutsideSales\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade( 
        SchemaSetupInterface $setup, 
        ModuleContextInterface $context
    ) {

		$installer = $setup;
		$installer->startSetup();
        
        // Adiciona tabelas de webhook e refresh tokens
        if(version_compare($context->getVersion(), '1.1.0', '<')) {

            if (!$installer->tableExists('nati_webhook_queue')) {
                /*
                *
                * id: int(11) NOT NULL AUTO_INCREMENT
                * provider: varchar(255) NOT NULL
                * json: mediumtext NULL
                * status: varchar(255) NULL
                * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('nati_webhook_queue')
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
                    'provider',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Provider'
                )
                ->addColumn(
                    'json',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [
                        'nullable' => true
                    ],
                    'Json'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                        'default' => 'generated'
                    ],
                    'Status'
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
                ->setComment('Tabela de filas webhook');
    
                $installer->getConnection()->createTable($table);
            }
            
            if (!$installer->tableExists('nati_refresh_tokens')) {
                /*
                *
                * id: int(11) NOT NULL AUTO_INCREMENT
                * provider: varchar(255) NOT NULL
                * token: varchar(255) NULL
                * refresh_token: varchar(255) NULL
                * cicle: integer(11) NULL
                * next_cicle: timestamp NULL
                * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('nati_refresh_tokens')
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
                    'provider',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Provider'
                )
                ->addColumn(
                    'token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true
                    ],
                    'Refresh Token'
                )
                ->addColumn(
                    'refresh_token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true
                    ],
                    'Refresh Token'
                )
                ->addColumn(
                    'cicle',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => true
                    ],
                    'Ciclo'
                )
                ->addColumn(
                    'next_cicle',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Próximo ciclo'
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
                ->setComment('Tabela de filas webhook');
    
                $installer->getConnection()->createTable($table);
            }

            //Verifica se a coluna message já existe na tabela nati_webhook_queue, se nao existir cria
            $connection = $installer->getConnection();
            $tableName = $installer->getTable('nati_webhook_queue');
            $columnName = 'message';
            $columnExists = $connection->tableColumnExists($tableName, $columnName);
            if (!$columnExists) {
                $connection->addColumn(
                    $tableName,
                    $columnName,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Message'
                    ]
                );
            }
            
        }

        // Adiciona tabela de investimento em mídia
        if(version_compare($context->getVersion(), '1.2.0', '<')) {

            if (!$installer->tableExists('nati_media_investment')) {
                /*
                *
                * id: int(11) NOT NULL AUTO_INCREMENT
                * provider: int(50) NOT VARCHAR
                * category_id: int(11) NOT NULL
                * investment: decimal(12,4) NOT NULL
                * date: date NOT NULL
                * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('nati_media_investment')
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
                    'provider',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ],
                    'Plataforma'
                )
                ->addColumn(
                    'category_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false
                    ],
                    'Categoria'
                )
                ->addColumn(
                    'investment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,2',
                    [
                        'nullable' => false
                    ],
                    'Investimento'
                )
                ->addColumn(
                    'date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Data'
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
                ->setComment('Tabela de investimento em mídia');

                $installer->getConnection()->createTable($table);
            }
        }

        // Adiciona tabela de custo de frete
        if(version_compare($context->getVersion(), '1.3.0', '<')) {

            if (!$installer->tableExists('nati_shipping_coast')) {
                /*
                *
                * id: int(11) NOT NULL AUTO_INCREMENT
                * provider: int(50) NOT VARCHAR
                * category_id: int(11) NOT NULL
                * investment: decimal(12,4) NOT NULL
                * date: date NOT NULL
                * created_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                * updated_at: timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                */
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('nati_shipping_coast')
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
                    'provider',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ],
                    'Plataforma'
                )
                ->addColumn(
                    'total_value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,2',
                    [
                        'nullable' => false
                    ],
                    'Investimento'
                )
                ->addColumn(
                    'value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,2',
                    [
                        'nullable' => false
                    ],
                    'Investimento'
                )
                ->addColumn(
                    'date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Data'
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
                ->setComment('Tabela de valores de Frete');

                $installer->getConnection()->createTable($table);
            }
        }
    }
}