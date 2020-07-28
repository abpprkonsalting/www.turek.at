<?php
/**
 *
 * @package Lillik\PriceDecimal\Model
 *
 * @author  Lilian Codreanu <lilian.codreanu@gmail.com>
 */

namespace Lillik\PriceDecimal\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements ConfigInterface
{

    const XML_PATH_PRICE_PRECISION
        = 'catalog_price_decimal/general/price_precision';

    const XML_PATH_CAN_SHOW_PRICE_DECIMAL
        = 'catalog_price_decimal/general/can_show_decimal';

    const XML_PATH_GENERAL_ENABLE
        = 'catalog_price_decimal/general/enable';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    private $storeManager;

    private $state;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Framework\App\State $state
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Return Config Value by XML Config Path
     * @param $path
     *
     * @return mixed
     */
    public function getValueByPath($path)
    {
    	if ($this->state->getAreaCode() == "adminhtml") {
		    $value = $this->getScopeConfig()->getValue($path);
	    } else {
		    $value = $this->getScopeConfig()->getValue($path,
			    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			     $this->storeManager->getStore()->getId());
	    }
	    return $value;
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getValueByPath(self::XML_PATH_GENERAL_ENABLE);
    }

    /**
     * @return mixed
     */
    public function canShowPriceDecimal()
    {
        return $this->getValueByPath(self::XML_PATH_CAN_SHOW_PRICE_DECIMAL);
    }

    /**
     * Return Price precision from store config
     *
     * @return mixed
     */
    public function getPricePrecision()
    {
        return $this->getValueByPath(self::XML_PATH_PRICE_PRECISION);
    }
}
