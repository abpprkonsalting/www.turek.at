<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Synctofb;

class Mainajax extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $helper;
    protected $resultJsonFactory;
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magecomp\Instagramshoppable\Helper\Data $helper,
        \Psr\Log\LoggerInterface $loggerInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $loggerInterface;
    }
    public function execute()
    {
        try
        {
            $dia_setting_id = $this->getRequest()->getParam('diaSettingId');
            if ($dia_setting_id !== null)
            {
                $this->helper->setDiaSettingId($dia_setting_id);
                $response = [
                    'success' => true,
                ];
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;

            }
            else
            {
                $this->reportFailure($dia_setting_id, null);
            }
        }
        catch (Exception $e)
        {
            $this->reportFailure($dia_setting_id, $e);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magecomp_Instagramshoppable::instagramshoppable_facebook_ads');
    }
    public function reportFailure($dia_setting_id, $e) {
        if ($e) {
            $this->logger->info("Error ".$e);
        }
        throw new \Exception(
            'Set DIA setting ID failed:'.($dia_setting_id ?: 'null')
        );
    }
}