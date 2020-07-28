<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableauth;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Config\Storage\WriterInterface;

class Callback extends AbstractShoppableauth
{
	protected $scopeConfig;
    protected $backendSession;
    protected $configWriter;
    public function __construct(Context $context, 
        Url $modelUrl, 
        ScopeConfigInterface $configScopeConfigInterface, 
        HelperData $helperData, Session $backendSession,
        WriterInterface $configWriter)
    {
		$this->scopeConfig = $configScopeConfigInterface;
        $this->backendSession = $backendSession;
        $this->configWriter = $configWriter;
        parent::__construct($context, $modelUrl, $configScopeConfigInterface, $helperData);
    }

    public function execute()
    {	
        $code = $this->getRequest()->getParam('code');
        $response = $this->_getAccessToken($code);
        $responseObject = json_decode($response);

        $this->backendSession->setData(self::INSTAGRAM_SESSION_DATA_KEY, $responseObject);
        $this->configWriter->save(self::INSTAGRAM_CONFIG_DATA_KEY, serialize($responseObject), 'default', 0);

        $redirectUrl = $this->_helperData->getAdminConfigSectionUrl();
		$this->_redirect($redirectUrl);
    }
}