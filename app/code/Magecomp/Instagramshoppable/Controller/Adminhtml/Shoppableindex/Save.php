<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Backend\App\Action\Context;

class Save extends AbstractShoppableindex
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
         if ($this->getRequest()->getPost())
         {
		   $postData = $this->getRequest()->getPost();  
		   $storeId = $this->getRequest()->getParam('store');
           try 
		   {
				$customdata = array();
				$customdata['image_id']    	= $postData['image_id'];
				$customdata['image_link']  	= $postData['image_link'];
				$customdata['image_title'] 	= $postData['image_title'];
				$customdata['image_desc']  	= $postData['image_desc'];
				$customdata['title1'] 		= $postData['title1'];
				$customdata['titlelink1'] 	= $postData['titlelink1'];
				$customdata['title1x'] 		= $postData['title1x'];
				$customdata['title1y'] 		= $postData['title1y'];
				$customdata['product_id_1'] = $postData['product_id_1'];
				$customdata['title2'] 		= $postData['title2'];
				$customdata['titlelink2'] 	= $postData['titlelink2'];
				$customdata['title2x'] 		= $postData['title2x'];
				$customdata['title2y'] 		= $postData['title2y'];
				$customdata['product_id_2'] = $postData['product_id_2'];
				$customdata['title3'] 		= $postData['title3'];
				$customdata['titlelink3'] 	= $postData['titlelink3'];
				$customdata['title3x'] 		= $postData['title3x'];
				$customdata['title3y'] 		= $postData['title3y'];
				$customdata['product_id_3'] = $postData['product_id_3'];
				$customdata['title4'] 		= $postData['title4'];
				$customdata['titlelink4'] 	= $postData['titlelink4'];
				$customdata['title4x'] 		= $postData['title4x'];
				$customdata['title4y'] 		= $postData['title4y'];
				$customdata['product_id_4'] = $postData['product_id_4'];
				$customdata['title5'] 		= $postData['title5'];
				$customdata['titlelink5'] 	= $postData['titlelink5'];
				$customdata['title5x'] 		= $postData['title5x'];
				$customdata['title5y'] 		= $postData['title5y'];
				$customdata['product_id_5'] = $postData['product_id_5'];
				$customdata['title6'] 		= $postData['title6'];
				$customdata['titlelink6'] 	= $postData['titlelink6'];
				$customdata['title6x'] 		= $postData['title6x'];
				$customdata['title6y'] 		= $postData['title6y'];
				$customdata['product_id_6'] = $postData['product_id_6'];
				
			   $id = $postData['image_id']; //$this->getRequest()->getParam('id');
			   $InstagramshoppableModel  = $this->_modelInstagramshoppableimageFactory->create()->load($id);
			   $InstagramshoppableModel->setData($customdata);
			   $InstagramshoppableModel->save();
				      
			   $this->messageManager->addSuccess('Image Information successfully saved');
			   $this->_session->setInstagramshoppableData(false);
			   $this->_redirect('*/*/',['store' => $storeId]);
			   return;
          } 
		  catch (\Exception $e)
		  {
			  $this->messageManager->addError($e->getMessage());
			  $this->_session->setInstagramshoppableData($this->getRequest()->getPost());
			  $this->_redirect('*/*/edit',['id' => $postData['image_id'],'store' => $storeId]);
			  return;
          }
      }
      $this->_redirect('*/*/');
    }
}