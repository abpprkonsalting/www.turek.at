<?php

namespace CleverReach\CleverReachIntegration\Block\Adminhtml\Content;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy as ProxyInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Backend\Block\Template;
use CleverReach\CleverReachIntegration\Helper\Url;

class Refresh extends Template
{
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @var ConfigService
     */
    private $configService;

    /** @var  Url */
    private $urlHelper;

    /** @var \Magento\Backend\Model\UrlInterface  */
    private $backendUrl;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Url $urlHelper,
        array $data
    ) {
        $this->backendUrl = $backendUrl;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    public function getRefreshConfig()
    {
        $redirectUrl = $this->urlHelper->getFrontUrl('cleverreach/refresh/callback/');
        $userInfo = $this->getConfigService()->getUserInfo();

        $lang = $this->getConfigService()->getAuthenticatedUserLangCode();

        return [
            'checkStatusUrl' => $this->backendUrl->getUrl('cleverreach/refresh/checkstatus'),
            'authUrl' => $this->getProxy()->getAuthUrl($redirectUrl, [], ['lang' => $lang]),
            'crId' => $userInfo['id'],
        ];
    }

    /**
     * @return Proxy
     */
    private function getProxy()
    {
        if ($this->proxy === null) {
            $this->proxy = ServiceRegister::getService(ProxyInterface::CLASS_NAME);
        }

        return $this->proxy;
    }

    /**
     * @return ConfigService
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
