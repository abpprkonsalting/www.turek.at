<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\AutoConfigure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\HttpClient;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\HttpResponse;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\HttpClientService;
use Magento\Backend\App\Action;
use CleverReach\CleverReachIntegration\Helper\Url;

class StartServerConfiguration extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    private $configService;
    private $httpClientService;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * BuildFirstEmail Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Url $urlHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Url $urlHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $this->setInitialConfiguration();
        $method = 'GET';
        $url = $this->urlHelper->getFrontUrl('cleverreach/autoconfigure/testendpoint/');

        $success = $this->getHttpClientService()->autoConfigure($method, $url);
        if ($success) {
            $this->getConfigService()->setAutoConfigureState('success');
        } else {
            $this->getConfigService()->setAutoConfigureState('failed');
        }
    }
    
    private function setInitialConfiguration()
    {
        $this->getConfigService()->setAutoConfigureState('in_progress');
        $this->getConfigService()->setAutoConfigureStartTime(time());
    }

    /**
     * @return HttpClientService
     */
    private function getHttpClientService()
    {
        if (empty($this->httpClientService)) {
            $this->httpClientService = ServiceRegister::getService(HttpClient::CLASS_NAME);
        }

        return $this->httpClientService;
    }

    /**
     * @return ConfigService
     */
    private function getConfigService()
    {
        if (empty($this->configService)) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
