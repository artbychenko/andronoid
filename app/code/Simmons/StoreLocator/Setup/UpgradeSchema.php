<?php

namespace Simmons\StoreLocator\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('cmsmart_localtor'),
                'store_rank',
                [
                    'type'=> \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    '6',
                    'default' => 1,
                    'nullable' => false,
                    'comment'=> 'Store Rank'
                ]
            );
        $setup->endSetup();
    }
}