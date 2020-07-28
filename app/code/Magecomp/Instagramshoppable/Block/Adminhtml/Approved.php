<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Container;
use Magento\Store\Model\ScopeInterface;
use Magecomp\Instagramshoppable\Helper\Image;

class Approved extends Container
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;
    protected $_modelInstagramshoppableimageFactory;
    protected $_helperImage;

    public function __construct(Context $context, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
		Image $helperImage,    
        array $data = [])
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
		$this->_helperImage = $helperImage;
        parent::__construct($context, $data);
    }
	public function getApproveUrl()
	{
		return $this->getUrl('*/*/approve/');
	}
	public function getDeleteUrl()
	{
		return $this->getUrl('*/*/delete/');
	}
	public function getImages()
	{
		$imgconfig = array();
		$Storeid = $this->getRequest()->getParam('store');
		$updateType = $this->_scopeConfig->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$Storeid);
		switch($updateType)
		{
			case self::UPDATE_TYPE_HASHTAG :
				$imgconfig = $this->_helperImage->getTags($Storeid);
				break;
				
			case self::UPDATE_TYPE_USER :
				$imgconfig = $this->_helperImage->getUsers($Storeid);
		}
		return $this->_modelInstagramshoppableimageFactory->create()->getCollection()
				->addFilter('is_approved', 1)
				->addFilter('is_visible', 1)
				->addFilter('tag', ['in' => $imgconfig], 'public')
				->setOrder('instagramshoppable_image_id','DESC');
	}
}