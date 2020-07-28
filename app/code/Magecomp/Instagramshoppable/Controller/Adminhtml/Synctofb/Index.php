<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Synctofb;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Instagramshoppable'));
        $resultPage->getConfig()->getTitle()->prepend(__('Instagram Shoppable Extension'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magecomp_Instagramshoppable::instagramshoppable_facebook_ads');
    }
}