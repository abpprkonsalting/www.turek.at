<?php
namespace Etron\Gateway\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $jsonResultFactory;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->storeManager = $storeManager;
    }
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
		$result = $this->jsonResultFactory->create();
        
        $categoryFactory = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        
        $stores = $this->storeManager->getStores($withDefault = false);
        $data = [];
        foreach ($stores as $store) {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
            $categories = $categoryFactory->create()
                ->addAttributeToSelect('*')
                ->setStore($store);
            $storedata = ['store' => $store->getCode(), 'items' => []];
            /** @var \Magento\Catalog\Model\Category $c */
            foreach($categories as $c) {
                $storedata['items'][] = [
                    'name' => $c->getName(),
                    'id' => $c->getId(),
                    'parent_id' => $c->getParentId(),
                    'etron_id' => $c->getEtronId(),
                    'position' => $c->getPosition(),
                    'include_in_menu' => $c->getIncludeInMenu()
                ];
            }
            $data[] = $storedata;
        }
        return $result->setData($data);
    }
}
