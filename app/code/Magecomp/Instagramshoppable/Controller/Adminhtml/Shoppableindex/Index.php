<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
	public function __construct(\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
	public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magecomp_Instagramshoppable::instagramshoppable');
        $resultPage->addBreadcrumb(__('Magecomp'), __('Magecomp'));
        $resultPage->addBreadcrumb(__('Instagramshoppable'), __('Instagramshoppable'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Images'));
		
		$dataPersistor = $this->_objectManager->get('Magento\Framework\App\Request\DataPersistorInterface');
        $dataPersistor->clear('instagramshoppable_data');
		
        return $resultPage;
    }
	protected function _isAllowed()
    {
		return $this->_authorization->isAllowed('Magecomp_Instagramshoppable::instagramshoppable');
    }
}
