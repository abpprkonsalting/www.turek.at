<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Store\Model\ScopeInterface;
use Magecomp\Instagramshoppable\Helper\Image;

class Newadminhtml extends Container
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;
    protected $_helperImage;
    protected $_modelInstagramshoppableimageFactory;
	
    public function __construct(Context $context, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
		Image $helperImage, 
        array $data = [])
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
		$this->_helperImage = $helperImage;
		
        parent::__construct($context, $data);
    }
    protected function _prepareLayout()
    {
		$Storeid = $this->getRequest()->getParam('store');
        $this->buttonList->add('add_new', [
            'label'   => __('Update Images List'),
            'onclick' => "setLocation('{$this->getUrl('*/*/update',['store' => $Storeid])}')",
            'class'   => 'primary'
        ]);
        return parent::_prepareLayout();
    }
	public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
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
				->addFilter('is_approved', 0)
				->addFilter('is_visible', 1)
				->addFilter('tag', ['in' => $imgconfig], 'public')
				->setOrder('instagramshoppable_image_id','DESC');
	}
}