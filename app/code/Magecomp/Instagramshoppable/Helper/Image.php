<?php
namespace Magecomp\Instagramshoppable\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Image extends AbstractHelper
{
    protected $_modelConfigFactory;
	protected $_storeManager;

    public function __construct(Context $context, 
        \Magento\Framework\App\Config\Storage\WriterInterface $modelConfigFactory,
		StoreManagerInterface $storeManager)
    {
        $this->_modelConfigFactory = $modelConfigFactory;
		$this->_storeManager = $storeManager;
        parent::__construct($context);
    }

	public function getFetchImageCount($StoreId)
	{
		return $this->scopeConfig->getValue('instagramshoppable/module_options/imagefatch', ScopeInterface::SCOPE_STORE,$StoreId);
	}
	
	public function getTags($storeid)
	{
		$conTags = $this->scopeConfig->getValue('instagramshoppable/module_options/tags', ScopeInterface::SCOPE_STORE,$storeid);
		$tags = explode(',', $conTags);
		
		return $tags;		
	}
	
	public function getTagsURL($hashid)
	{
		return 'https://www.instagram.com/explore/tags/'.$hashid.'/?__a=1';
	}
	
	public function getVideoURL($videocode)
	{
		return 'https://www.instagram.com/p/'.$videocode.'/?__a=1';
	}
	
	public function getUsers($storeid)
	{
		$conUsers = $this->scopeConfig->getValue('instagramshoppable/module_options/users', ScopeInterface::SCOPE_STORE,$storeid);
		$users = explode(',', $conUsers);
		return $users;		
	}
	
	public function getUsersURL($userid)
	{
		return 'https://www.instagram.com/'.$userid.'/?__a=1';
	}
	
	public function getPopupConfiguration()
	{
		return $this->scopeConfig->getValue('instagramshoppable/module_options/displayproduct', ScopeInterface::SCOPE_STORE,$this->_storeManager->getStore()->getId());
	}
}