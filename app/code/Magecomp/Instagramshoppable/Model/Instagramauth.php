<?php
namespace Magecomp\Instagramshoppable\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Session;
use Magento\Store\Model\ScopeInterface;

class Instagramauth
{
    protected $_modelSessionFactory;
    protected $_configScopeConfigInterface;

    public function __construct(Session $modelSessionFactory, 
        ScopeConfigInterface $configScopeConfigInterface)
    {
        $this->_modelSessionFactory = $modelSessionFactory;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;

    }

    const INSTAGRAM_SESSION_DATA_KEY = 'instagramshoppable_session_data';
    const INSTAGRAM_CONFIG_DATA_KEY = 'magecomp/instagramshoppable/instagramshoppable_data';
    
    public function getUserData()
    {
		$session = $this->_modelSessionFactory;
        $info = $session->getData('instagramshoppable_session_data');
		if (!$info)
        {
            $configDataKey = self::INSTAGRAM_CONFIG_DATA_KEY;
            $info = unserialize($this->_configScopeConfigInterface->getValue($configDataKey, ScopeInterface::SCOPE_STORE, 0));
        }
        return $info;
    }
    public function isValid()
    {
        $configDataKey = self::INSTAGRAM_CONFIG_DATA_KEY;
        return (!!$this->getUserData() || $this->_configScopeConfigInterface->getValue($configDataKey, ScopeInterface::SCOPE_STORE, 0));
    }
    public function getAccessToken()
    {
        return $this->getUserData()->access_token;
    }

}
