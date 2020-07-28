<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Instagramshoppable;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Backend\App\Action
{
    protected $_modelInstagramshoppableimageFactory;
    protected $_resultRawFactory;
	protected $_logLoggerInterface;

    public function __construct(Context $context, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
		LoggerInterface $logLoggerInterface,
        RawFactory $resultRawFactory)
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
		$this->_logLoggerInterface = $logLoggerInterface;
        $this->_resultRawFactory = $resultRawFactory;

        parent::__construct($context);
    }
    public function execute()
    {
		try
		{
			$imageId = $this->getRequest()->getParam('id');
			$image = $this->_modelInstagramshoppableimageFactory->create()->load($imageId);
			if ($image->getId()) {
				$image->setIsVisible(0)->save();
			}
			echo trim("success");
		}
		catch(Exception $e)
		{
			$this->_logLoggerInterface->debug($e->getMessage());
			echo trim('error');
		}
    }
}