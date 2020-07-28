<?php
namespace Magecomp\Instagramshoppable\Model\Source;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Instagramshoppable extends AbstractSource
{
    protected $_configScopeConfigInterface;
    protected $_helperData;
	protected $_storeManager;
    public function __construct(ScopeConfigInterface $configScopeConfigInterface, 
        HelperData $helperData,
		\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_helperData = $helperData;
		$this->_storeManager = $storeManager; 

    }
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
		    $tags  = explode(',', $this->_configScopeConfigInterface->getValue('instagramshoppable/module_options/tags', ScopeInterface::SCOPE_STORE,$this->_storeManager->getStore()->getId()));
		    foreach ($tags as $tag)
            {
		    	$tag = trim($tag);
		    	if (empty($tag)) continue;
		    	$this->_options[] = ['label' => $tag, 'value' => base64_encode($tag)];
		    }
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
