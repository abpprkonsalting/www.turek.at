<?php
namespace Magecomp\Instagramshoppable\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
   
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
        $installer->startSetup();

		$table = $installer->getConnection()
            ->newTable($installer->getTable('instagramshoppable_image'))
			->addColumn(
                'instagramshoppable_image_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true],
                'ID'
			)
			->addColumn(
                'image_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false,'primary' => true],
                'Image Id'
            )
			->addColumn(
                'username',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Username'
            )
			->addColumn(
                'caption_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Caption Text'
            )
			->addColumn(
                'media_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Media Type'
            )
            ->addColumn(
                'standard_resolution_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Standard Resolution Url'
            )
			->addColumn(
                'thumbnail_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Thumbnail url'
            )
			->addColumn(
                'tag',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Tag Value'
            )
			->addColumn(
                'is_approved',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => 0],
                'Is Approved'
            )
			->addColumn(
                'is_visible',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => 1],
                'Is Visible on Backend and Frontend'
            )
			->addColumn(
                'image_link',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Link Url'
            )
			->addColumn(
                'image_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title'
            )
			->addColumn(
                'image_desc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Image Description'
            )
			
			->addColumn(
                'title1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 1'
            )
			->addColumn(
                'titlelink1',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 1 Link Url'
            )
			
			->addColumn(
                'title2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 2'
            )
			->addColumn(
                'titlelink2',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 2 Link Url'
            )
			
			->addColumn(
                'title3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 3'
            )
			->addColumn(
                'titlelink3',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 3 Link Url'
            )
			
			->addColumn(
                'title4',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 4'
            )
			->addColumn(
                'titlelink4',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 4 Link Url'
            )
			
			->addColumn(
                'title5',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 5'
            )
			->addColumn(
                'titlelink5',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image Title 5 Link Url'
            );

        $installer->getConnection()->createTable($table);
        $tableAdmins = $installer->getTable('instagramshoppable_image');
        if($setup->getConnection()->isTableExists($tableAdmins) == true)
        {
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title1x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 1x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title1y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 1y'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title2x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 2x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title2y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 2y'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title3x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 3x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title3y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 3y'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title4x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 4x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title4y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 4y'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title5x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 5x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title5y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 5y'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'image_likes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Image Likes'
                ]
            );
        }
        if($setup->getConnection()->isTableExists($tableAdmins) == true)
        {
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title6',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'comment' => 'Image Title 6'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'titlelink6',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'comment' => 'Image Title 6 Link Url'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title6x',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 6x'
                ]
            );
            $installer->getConnection()->addColumn(
                $tableAdmins,
                'title6y',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Title 6y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_1',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 1',
                    'after'   => 'title1y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_2',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 2',
                    'after'   => 'title2y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_3',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 3',
                    'after'   => 'title3y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_4',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 4',
                    'after'   => 'title4y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_5',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 5',
                    'after'   => 'title5y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'product_id_6',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Product Id 6',
                    'after'   => 'title6y'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'image_comments',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Image Comments',
                    'after'   => 'image_likes'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableAdmins,
                'link',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'comment' => 'Image Link'
                ]
            );

        }
        $installer->endSetup();
    }
}