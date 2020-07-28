<?php
namespace Magecomp\Instagramshoppable\Model\ResourceModel\Instagramshoppableimage\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magecomp\Instagramshoppable\Helper\Image;

class Collection extends \Magecomp\Instagramshoppable\Model\ResourceModel\Instagramshoppableimage\Collection implements SearchResultInterface
{
	const UPDATE_TYPE_USER = 1;
    const UPDATE_TYPE_HASHTAG = 0;
	
	protected $aggregations;
    protected $_helperImage;
    protected $scopeConfig;
	protected $curstoreid;
	 
	public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		ScopeConfigInterface $configScopeConfigInterface,
		Image $helperImage,
		\Magento\Framework\App\Request\Http $request,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
		
		$this->scopeConfig = $configScopeConfigInterface;
		$this->_helperImage = $helperImage;
		
		$om = \Magento\Framework\App\ObjectManager::getInstance();	
		$urlInterface = $om->get('Magento\Framework\UrlInterface');
		$cstsession = $om->get('Magento\Backend\Model\Session');
		
		if(sizeof($_GET) == 0) 
		{
			$urlarray = explode('/',$urlInterface->getCurrentUrl());
			if(in_array('store',$urlarray) && sizeof($_GET) == 0)
			{
				$key = array_search('store', $urlarray);
				$Storeid = $urlarray[$key+1];
				$cstsession->setMyStore($Storeid);		
			}
			else
			{
				$cstsession->setMyStore(0);	
			}
		}
		$Storeid = $cstsession->getMyStore();
		$imgconfig = array();
		$updateType = $this->scopeConfig->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$Storeid);
		switch($updateType)
		{
			case self::UPDATE_TYPE_HASHTAG :
				$imgconfig = $this->_helperImage->getTags($Storeid);
				break;
				
			case self::UPDATE_TYPE_USER :
				$imgconfig = $this->_helperImage->getUsers($Storeid);
		}
		
		$this->addFieldToFilter('is_approved',1);
		$this->addFieldToFilter('is_visible',1);
		$this->addFilter('tag', ['in' => $imgconfig], 'public');
    }
	
	public function getAggregations()
    {
        return $this->aggregations;
    }
	
	public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }
	
	public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }
	
	public function getSearchCriteria()
    {
        return null;
    }
	
	public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
	
	public function getTotalCount()
    {
        return $this->getSize();
    }
	
	public function setTotalCount($totalCount)
    {
        return $this;
    }
	
	public function setItems(array $items = null)
    {
        return $this;
    }
}