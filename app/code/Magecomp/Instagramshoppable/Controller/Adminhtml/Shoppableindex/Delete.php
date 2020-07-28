<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\App\Action\Context;

class Delete extends AbstractShoppableindex
{
    protected $_modelInstagramshoppableimageFactory;
    public function __construct(Context $context, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory)
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
		if($this->getRequest()->getParam('id') > 0)
		{
		  	try
		  	{
			  	$instagramshoppableModel = $this->_modelInstagramshoppableimageFactory->create();
			  	$instagramshoppableModel->setId($this->getRequest()->getParam('id'))
							   ->delete();
			  	$this->messageManager->addSuccess('Image successfully deleted');
			  	$this->_redirect('*/*/');
		   	}
		   	catch (\Exception $e)
			{
				$this->messageManager->addError($e->getMessage());
				$this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
			}
	   	}
	  	$this->_redirect('*/*/');
	}
}
