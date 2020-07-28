<?php

namespace CleverReach\CleverReachIntegration\Block\Adminhtml\Content;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy as ProxyInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use Magento\Backend\Block\Template;
use CleverReach\CleverReachIntegration\Helper\Url;

class Welcome extends Template
{

    private $configService;

    private $proxy;
    
    private $authSession;
    
    private $countryFactory;
    
    private $scopeConfig;

    /** @var  Url */
    private $urlHelper;

    /** @var \Magento\Backend\Model\UrlInterface  */
    private $backendUrl;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\Auth\Session\Proxy $authSession,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Url $urlHelper,
        array $data
    ) {
        $this->authSession = $authSession;
        $this->countryFactory = $countryFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->backendUrl = $backendUrl;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    public function getWelcomeConfig()
    {
        $redirectUrl = $this->urlHelper->getFrontUrl('cleverreach/auth/callback/');
        $registerData = $this->getRegisterData();

        $lang = $this->getConfigService()->getAuthenticatedUserLangCode();

        return [
            'checkStatusUrl' => $this->backendUrl->getUrl('cleverreach/auth/checkstatus'),
            'authUrl' => $this->getProxy()->getAuthUrl($redirectUrl, $registerData, ['lang' => $lang]),
        ];
    }
    
    private function getRegisterData()
    {
        $countryCode = $this->scopeConfig->getValue(
            \Magento\Store\Model\Information::XML_PATH_STORE_INFO_COUNTRY_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $country = $countryCode ? $this->countryFactory->create()->loadByCode($countryCode)->getName() : '';

        $user = $this->authSession->getUser();
        $registerData = [
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'company' => $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_NAME,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'street' => $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_STREET_LINE1,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) . $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_STREET_LINE2,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'zip' => $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'city' => $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_CITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'country' => $country,
            'phone' => $this->scopeConfig->getValue(
                \Magento\Store\Model\Information::XML_PATH_STORE_INFO_PHONE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
        ];

        return base64_encode(json_encode($registerData));
    }

    private function getProxy()
    {
        if (empty($this->proxy)) {
            $this->proxy = ServiceRegister::getService(ProxyInterface::CLASS_NAME);
        }

        return $this->proxy;
    }

    /**
     * @return Configuration
     */
    private function getConfigService()
    {
        if (empty($this->configService)) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
