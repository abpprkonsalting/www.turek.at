<?php
namespace Magecomp\Instagramshoppable\Block;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Helper\Image;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Homepage extends Template
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;
    protected $_helperData;
    protected $_helperImage;
    protected $_modelInstagramshoppableimageFactory;

    public function __construct(Context $context, 
        HelperData $helperData, 
        Image $helperImage, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
        array $data = [])
    {
        $this->_helperData = $helperData;
        $this->_helperImage = $helperImage;
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
        parent::__construct($context, $data);
    }
	public function _prepareLayout()
 	{
  		return parent::_prepareLayout();
 	}
	public function showInstagramshoppableImages()
	{
		$helper = $this->_helperData;
		return ($helper->isEnabled() && $helper->showImagesOnHomePage());
	}
	public function getCurrentImageListConfig()
	{
		$curlist = array();
		$StoreId = $this->_storeManager->getStore()->getId();
		$updateType = $this->_scopeConfig->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$StoreId);
		switch($updateType)
		{
			case self::UPDATE_TYPE_HASHTAG :
				$curlist = $this->_helperImage->getTags($StoreId);
				break;
			case self::UPDATE_TYPE_USER :
				$curlist = $this->_helperImage->getUsers($StoreId);
		}
		return $curlist;
	}
    public function getInstagramshoppableGalleryImages()
    {
		$images = array();
        $imgconfig = $this->getCurrentImageListConfig();
        if (count($imgconfig)) 
		{
		    	$imagesCollection = $this->_modelInstagramshoppableimageFactory->create()
		    		->getCollection()
		    		->setPageSize($this->_helperData->getHomePageLimit()) 
		    		->addFilter('is_approved', 1)
                    ->addFilter('is_visible', 1)
		    		->addFilter('tag', ['in' => $imgconfig], 'public')
					->setOrder('instagramshoppable_image_id','DESC');

		    	foreach ($imagesCollection as $image) 
				{
					$images[] = $image;
        		}
		}
        return $images;
    }
	public function getInstagramshoppablepageGalleryImages()
    {
		$images = array();
		$imgconfig = $this->getCurrentImageListConfig();
		$helper = $this->_helperData;
        if(count($imgconfig) && $helper->isEnabled()) 
		{
			$imagesCollection = $this->_modelInstagramshoppableimageFactory->create()
				->getCollection()
				->setPageSize($this->_helperData->getGalleryPageLimit())
				->addFilter('is_approved', 1)
				->addFilter('is_visible', 1)
				->addFilter('tag', ['in' => $imgconfig], 'public')
				->setOrder('instagramshoppable_image_id','DESC');

			foreach ($imagesCollection as $image)
			{
				$images[] = $image;
			}
		}
        return $images;
    }
	public function getPopupUrl()
	{
		return $this->getUrl('instagramshoppable/gallery/popuphtml',['_secure'  => true]);
	}
	public function showProductInPopup()
	{
		return $this->_helperImage->getPopupConfiguration();
	}
}