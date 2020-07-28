<?php
namespace Magecomp\Instagramshoppable\Cron;

use Magento\Backend\App\Action\Context;
use Magecomp\Instagramshoppable\Helper\Image\User;
use Magecomp\Instagramshoppable\Helper\Image\Hashtag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Updatelikes 
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;

    protected $_imageUser;
	protected $_imageHashtag;
	protected $_scopeConfig;
	protected $_storeManager;
	
    public function __construct(Context $context, 
		ScopeConfigInterface $configScopeConfigInterface,  
        User $imageUser,
		Hashtag $imageHashtag,
		\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
	    $this->_scopeConfig = $configScopeConfigInterface;
        $this->_imageUser = $imageUser;
		$this->_imageHashtag = $imageHashtag;
		$this->_storeManager = $storeManager; 
	}
	public function execute() 
	{
		foreach($this->_storeManager->getStores(true) as $curstore)
		{
			$StoreId = $curstore->getStoreId();
			$updateType = $this->_scopeConfig->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$StoreId);
			switch($updateType)
			{
				case self::UPDATE_TYPE_HASHTAG :
					 $this->_imageHashtag->update($StoreId);
					break;
	
				case self::UPDATE_TYPE_USER :
					$this->_imageUser->update($StoreId);
					break;
			}	
		}
	}
}