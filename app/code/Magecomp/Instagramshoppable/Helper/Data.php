<?php
namespace Magecomp\Instagramshoppable\Helper;

use Magento\Backend\Model\UrlFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DirectoryList;

class Data extends AbstractHelper
{
	const PATH_FB_ADSEXT_FEED_GENERATION_ENABLED = 'facebook_adstoolbox/feed/generation/enabled';
	const PATH_FB_ADSEXT_FEED_GENERATION_FORMAT = 'facebook_adstoolbox/feed/generation/format';
	const PATH_FB_ADSEXT_FEED_RUNTIME_AVERAGE = "facebook_ads_toolbox/dia/feed/runtime_avg";

    protected $_modelUrlFactory;
	protected $_storeManager;
	protected $configWriter;
	protected $directoryList;
	
    public function __construct(Context $context,  
        UrlFactory $modelUrlFactory,
		WriterInterface $configWriter,
		DirectoryList $directoryList,
		\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_modelUrlFactory = $modelUrlFactory;
		$this->_storeManager = $storeManager;
		$this->configWriter = $configWriter;
		$this->directoryList = $directoryList;

        parent::__construct($context);
    }
	public function getCurrentStoreInfo()
	{
		return $this->_storeManager->getStore()->getId();
	}
	public function isEnabled()
	{
		return $this->scopeConfig->getValue('instagramshoppable/module_options/enabled', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function showImagesOnProductPage()
	{
		return (bool) $this->scopeConfig->getValue('instagramshoppable/module_options/product', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function showImagesOnHomePage()
	{
		return (bool) $this->scopeConfig->getValue('instagramshoppable/module_options/homepage', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getHomePageLimit()
	{
		return (int) $this->scopeConfig->getValue('instagramshoppable/module_options/homepage_limit', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getProductPageLimit()
	{
		return (int) $this->scopeConfig->getValue('instagramshoppable/module_options/product_limit', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getImageOnProductsection()
	{
		if($this->showImagesOnProductPage())
		{
			return (bool) $this->scopeConfig->getValue('instagramshoppable/module_options/product_detail', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
		}
	}
	public function getImageOnProductMoreViewsection()
	{
		if($this->showImagesOnProductPage())
		{
			return (bool) $this->scopeConfig->getValue('instagramshoppable/module_options/product_more', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
		}
	}
	public function shownpInPopup()
	{
		return (int) $this->scopeConfig->getValue('instagramshoppable/module_options/shownp', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getGalleryPageLimit()
	{
		return (int) $this->scopeConfig->getValue('instagramshoppable/module_options/imagecount', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getBaseMediaUrl()
	{
		return $this->directoryList->getPath('media');
	}
	public function buildUrl($url, $params){

        $strParams = [];
        foreach($params as $key => $value){
            $strParams[] = $key . '=' . $value;
        }
        $buildedUrl = is_null($url) ? '' : $url . '?';
        return $buildedUrl . implode('&', $strParams);
    }
	
	public function getAdminConfigSectionUrl()
    {
        $url = $this->_modelUrlFactory->create();
        return $url->getUrl('adminhtml/system_config/edit', [
            '_current'  => true,
            'section'   => 'instagram'
        ]);
    }
	public function getPixelId()
	{
		return $this->scopeConfig->getValue('instagramshoppable/fbpixel/id', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function setPixelId($pixelId)
	{
		$this->configWriter->save('instagramshoppable/fbpixel/id',$pixelId,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
	}
	public function getPixelUsePii()
	{
		return $this->scopeConfig->getValue('instagramshoppable/fbpixel/pixel_use_pii', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function setPixelUsePii($pixelpii)
	{
		$this->configWriter->save('instagramshoppable/fbpixel/pixel_use_pii',$pixelpii,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
	}
	public function getPixelInstallTime()
	{
		return $this->scopeConfig->getValue('instagramshoppable/fbpixel/install_time', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getStoreTimezone()
	{
		return $this->scopeConfig->getValue('general/locale/timezone', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function isFeedGenerationEnabled()
	{
		return $this->scopeConfig->getValue(self::PATH_FB_ADSEXT_FEED_GENERATION_ENABLED, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getFeedGenerationFormat()
	{
		return $this->scopeConfig->getValue(self::PATH_FB_ADSEXT_FEED_GENERATION_FORMAT, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function getCurrentSetup() {
		return array(
			'format' => $this->getFeedGenerationFormat()?: 'TSV',
			'enabled' => $this->isFeedGenerationEnabled()?: false
		);
	}
	public function getFeedRuntimeAverage()
	{
		return $this->scopeConfig->getValue(self::PATH_FB_ADSEXT_FEED_RUNTIME_AVERAGE, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
	public function setFeedRuntimeAverage($avgtime)
	{
		$this->configWriter->save(self::PATH_FB_ADSEXT_FEED_RUNTIME_AVERAGE,$avgtime,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
	}
	public function	setDiaSettingId($id)
	{
		$this->configWriter->save('instagramshoppable/dia/setting/id',$id,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
	}
	public function setPixelInstallTime($dateTime)
	{
		$this->configWriter->save('instagramshoppable/fbpixel/install_time',$dateTime,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,0);
	}
	public function getDiaSettingId()
	{
		return $this->scopeConfig->getValue('instagramshoppable/dia/setting/id', ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
	}
}
