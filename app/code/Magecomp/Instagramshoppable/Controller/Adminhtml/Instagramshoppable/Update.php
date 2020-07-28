<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Instagramshoppable;
use Magecomp\Instagramshoppable\Helper\Image\User;
use Magecomp\Instagramshoppable\Helper\Image\Hashtag;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Update extends \Magento\Backend\App\Action
{
	const UPDATE_TYPE_USER  = 1;
    const UPDATE_TYPE_HASHTAG   = 0;
    protected $_configScopeConfigInterface;
    protected $_helperImage;
    protected $_imageUser;
	protected $_imageHashtag;

    public function __construct(Context $context, 
        ScopeConfigInterface $configScopeConfigInterface,   
        User $imageUser,
		Hashtag $imageHashtag)
    {
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_imageUser = $imageUser;
		$this->_imageHashtag = $imageHashtag;
		
        parent::__construct($context);
    }
    public function execute()
    {
		$storeId = $this->getRequest()->getParam('store');
        $updateType = $this->_configScopeConfigInterface->getValue('instagramshoppable/module_options/updatetype', ScopeInterface::SCOPE_STORE,$storeId);
        switch($updateType)
		{
            case self::UPDATE_TYPE_HASHTAG :
                $this->_imageHashtag->update($storeId);
                break;

            case self::UPDATE_TYPE_USER :
                $this->_imageUser->update($storeId);
                break;
        }
		$this->_redirect('instagramshoppable/instagramshoppable/new',['store' => $storeId]);
    }
}