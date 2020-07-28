<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Synctofb;

use Magento\Framework\Controller\ResultFactory;
use Magecomp\Instagramshoppable\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class Pixelajax extends \Magento\Backend\App\Action
{
    const FORMAT_DATE = 'Y-m-d H:i:s';
    protected $dateTimeFactory;
    protected $resultJsonFactory;
    protected $helper;
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper,
        DateTimeFactory $dateTimeFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->dateTimeFactory = $dateTimeFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        if ($this->getRequest()->getParam('isAjax'))
        {
            $old_pixel_id = $this->helper->getPixelId();
            $response = array(
                'success' => false,
                'pixelId' => $old_pixel_id,
                'pixelUsePii' => $this->helper->getPixelUsePii(),
            );
            $pixel_id = $this->getRequest()->getParam('pixelId');
            $pixel_use_pii = $this->getRequest()->getParam('pixelUsePii');
            if ($pixel_id && $this->isPixelIdValid($pixel_id))
            {
                $this->helper->setPixelId($pixel_id);
                $this->helper->setPixelUsePii($pixel_use_pii === 'true'? '1' : '0');

                $response['success'] = true;
                $response['pixelId'] = $pixel_id;
                $response['pixelUsePii'] = $pixel_use_pii;

                if ($old_pixel_id != $pixel_id) {
                    $currDate =  $this->dateTimeFactory->create()->gmtDate(self::FORMAT_DATE);
                    $this->helper->setPixelInstallTime($currDate);
                }
            }
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($response);
            return $resultJson;
        }
    }
    public function isPixelIdValid($pixel_id)
    {
        return preg_match("/^\d{1,20}$/", $pixel_id) !== 0;
    }
}