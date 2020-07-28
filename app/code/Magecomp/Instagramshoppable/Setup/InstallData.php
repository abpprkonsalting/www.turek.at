<?php
namespace Magecomp\Instagramshoppable\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
	private $categorySetupFactory;
	
    public function __construct(EavSetupFactory $eavSetupFactory, CategorySetupFactory $categorySetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
		$this->categorySetupFactory = $categorySetupFactory;
    }
	
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
  		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		
		// Product Attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'instagramshoppable_source',
            [
                'type' => 'varchar',
                'label' => 'Used Instagram Tags',
				'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'input' => 'multiselect',
				'source' => 'Magecomp\Instagramshoppable\Model\Source\Instagramshoppable',
                'required' => false,
                'sort_order' => 6,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'apply_to' => 'simple,configurable,virtual',
                'group' => 'Instagram',
				'searchable'        => false,
				'filterable'        => false
            ]
        );
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'instagramshoppable_source_user',
            [
                'type' => 'varchar',
                'label' => 'Used Instagram Users',
				'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'input' => 'multiselect',
				'source' => 'Magecomp\Instagramshoppable\Model\Source\Instagramshoppable\User',
                'required' => false,
                'sort_order' => 7,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
				'apply_to' => 'simple,configurable,virtual',
                'group' => 'Instagram',
				'searchable'        => false,
				'filterable'        => false
            ]
        );
        $setup->endSetup();
    }
}