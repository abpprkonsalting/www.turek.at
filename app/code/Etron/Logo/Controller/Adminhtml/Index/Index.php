<?php
namespace Etron\Logo\Controller\Adminhtml\Index;
class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        return parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();  
        $page->setActiveMenu('Etron_Logo::etron_service');
        $page->getConfig()->getTitle()->prepend(__('ETRON Services'));
        return $page;
    }    
    protected function _isAllowed()
    {
        return true;
        #return $this->_authorization->isAllowed('ACL RULE HERE');
    }            

}