<?php

namespace Etron\Gateway\Setup;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;

class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    
    /**
     *
     * @var CustomerSetupFactory
     * */
    private $customerSetupFactory;


	private $salesSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
    	EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
	    SalesSetupFactory $salesSetupFactory
	)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'etron_id',
            [
                'group' => 'General Information',
                'type' => 'varchar',
                'label' => 'ETRON ERP ID',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'user_defined' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );
        
        /**
         * Add customer attribute 
         */
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "etron_id");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "etron_id",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "ETRON ERP ID",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""
        ));

        $field   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "etron_id");

        $field = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'etron_id');
        $used_in_forms[]="adminhtml_customer";
        $field->setData("used_in_forms", $used_in_forms)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $field->save();


        /** @var SalesSetup $salesInstaller */
	    $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

	    $salesInstaller->addAttribute(Order::ENTITY, "gw_send_status",
		    [
			    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			    'label'    => "Gateway Send Status",
			    'nullable'  =>  true,
			    'default'   => 0,
			    'user_defined' => true,
			    'visible'=> true,
		    ]
	    );

	    $setup->getConnection()->addColumn(
		    $setup->getTable('sales_order_grid'),
		    'gw_send_status',
		    [
			    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			    'comment' => 'Gateway Send Status',
		    ]
	    );


	    $salesInstaller->addAttribute(Order::ENTITY, "gw_send_log",
		    [
			    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			    'length' => 255,
			    'label'    => "Gateway Send Log",
			    'nullable'  =>  true,
			    'user_defined' => true,
			    'visible'=> false,
		    ]
	    );

	    $setup->getConnection()->addColumn(
		    $setup->getTable('sales_order_grid'),
		    'gw_send_log',
		    [
			    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			    'length' => 255,
			    'comment'    => "Gateway Send Log",
		    ]
	    );


	    $setup->endSetup();
    }
}