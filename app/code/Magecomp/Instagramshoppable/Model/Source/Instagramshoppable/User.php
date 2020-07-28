<?php
namespace Magecomp\Instagramshoppable\Model\Source\Instagramshoppable;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class User extends AbstractSource
{
    protected $_configScopeConfigInterface;
	protected $_storeManager;
    public function __construct(ScopeConfigInterface $configScopeConfigInterface,
				\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_storeManager = $storeManager; 
    }

    public function getAllOptions()
    {	
        if (is_null($this->_options)) {
            $users  = explode(',', $this->_configScopeConfigInterface->getValue('instagramshoppable/module_options/users', ScopeInterface::SCOPE_STORE,$this->_storeManager->getStore()->getId()));

            foreach ($users as $user) {
                $user = trim($user);
                if (empty($user)) continue;
                $this->_options[] = ['label' => $user, 'value' => base64_encode($user)];
            }
            // No show images
            $this->_options[] = ['label' => __('Do not show') , 'value' => 0];	
        }
		
        return $this->_options;
    }
	
	public function getOptionArray()
	{
    	$_options = array();
    	foreach ($this->getAllOptions() as $option) 
		{
        	$_options[$option["value"]] = $option["label"];
    	}
    	return $_options;
	}
}