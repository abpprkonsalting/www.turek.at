<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 22.07.18
 * Time: 23:48
 */

namespace Etron\Gateway\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

	public function install( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$setup->startSetup();

		$table = $setup->getConnection()->newTable(
			$setup->getTable( 'etron_gateway_queue' )
		)->addColumn(
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[ 'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true ],
			'Entity Id'
		)->addColumn(
			'order_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			255,
			['nullable' => false],
			'Order Id'
		)->addColumn(
			 'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			null,
			['nullable' => false],
			'Status'
		)->addColumn(
			'message',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			[],
			'Message'
		)->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
			'Created At'
		)
		->addIndex(
			$setup->getIdxName($setup->getTable( 'etron_gateway_queue' ), ['order_id']),
			['order_id']
		)->setComment(
			'Etron Gateway Queue Table'
		);
		$setup->getConnection()->createTable( $table );

		$setup->endSetup();
	}
}