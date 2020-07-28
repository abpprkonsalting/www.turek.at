<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\App\Action;

class Edit extends AbstractShoppableindex
{
    protected $_modelInstagramshoppableimageFactory;
	protected $_coreRegistry = null;
	protected $resultPageFactory;

    public function __construct(Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry,
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory)
    {
		$this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;

        parent::__construct($context);
    }
	protected function _isAllowed()
    {
		return $this->_authorization->isAllowed('Magecomp_Instagramshoppable::instagramshoppable');
    }
	
	protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magecomp_Instagramshoppable::instagramshoppable')
            ->addBreadcrumb(__('Instagramshoppable'), __('Instagramshoppable'))
            ->addBreadcrumb(__('Manage Image'), __('Manage Image'));
        return $resultPage;
    }

	public function execute()
    {
        $id = $this->getRequest()->getParam('id');
		$model  = $this->_modelInstagramshoppableimageFactory->create();
        if ($id)
        {
            $model->load($id);
            if (!$model->getId())
            {
                $this->messageManager->addError(__('This image no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data))
        {
            $model->setData($data);
        }
        $this->_coreRegistry->register('instagramshoppable_data', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Image') : __('New Image'),
            $id ? __('Edit Image') : __('New Image')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Image'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getOrderStatus() : __('New Image'));

        return $resultPage;
    }
}
