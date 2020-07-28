<?php
namespace Magecomp\Instagramshoppable\Helper;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\ScopeInterface;
use Magecomp\Instagramshoppable\Helper\Image;

class Product extends AbstractHelper
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;

    protected $_modelInstagramshoppableimageFactory;
    protected $_helperData;
    protected $_helperImage;
	protected $_storeManager;

    public function __construct(Context $context, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
        HelperData $helperData,
		Image $helperImage,
		\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
        $this->_helperData = $helperData;
		$this->_helperImage = $helperImage;
		$this->_storeManager = $storeManager; 
        parent::__construct($context);
    }

    public function getInstagramshoppableGalleryImages($product)
    {
        if(!$product->hasData('instagramshoppable_gallery_images')) {
        	
			$images = array();
        	
            $tags = $this->_getProductTags($product);

			if (count($tags)) {
		    	$tagsCollection = $this->_modelInstagramshoppableimageFactory->create()
		    		->getCollection()
		    		->setPageSize($this->_helperData->getProductPageLimit()) 
		    		->addFilter('is_approved', 1)
		    		->addFilter('is_visible', 1)
		    		->addFilter('tag', ['in' => $tags], 'public')
					->setOrder('instagramshoppable_image_id','DESC');

		    	foreach ($tagsCollection as $image) 
				{
					$images[] = $image;
        		}
		    }

            $product->setData('instagramshoppable_gallery_images', $images);
        }

        return $product->getData('instagramshoppable_gallery_images');
    }

    protected function _getProductTags($product)
    {
        $imgconfig = array();
		$updateType = $this->scopeConfig->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$this->_storeManager->getStore()->getId());
		
		switch($updateType)
		{
			case self::UPDATE_TYPE_HASHTAG :
				$taglist = explode(',',$product->getInstagramshoppableSource());
				if (!$taglist) { 
					continue; 
				}
				foreach($taglist as $tagname)
				{
					$imgconfig[] = base64_decode($tagname);
				}
				break;
				
			case self::UPDATE_TYPE_USER :
				$userlist = explode(',',$product->getInstagramshoppableSourceUser());
				if (!$userlist) {
					continue;
				}
				foreach($userlist as $username)
				{
					$imgconfig[] = base64_decode($username);
				}
				break;
		}
        return $imgconfig;
    }
}
