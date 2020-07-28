<?php

namespace CleverReach\CleverReachIntegration\Block\Adminhtml\Content;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Backend\Block\Template;

class Autoconfigure extends Template
{

    /** @var \Magento\Backend\Model\UrlInterface */
    private $backendUrl;

    private $configService;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    public function getAutoConfigureConfig()
    {
        $autoconfigureState = $this->getConfigService()->getAutoConfigureState();

        return [
            'testServerConfigurationUrl' => $this->backendUrl->getUrl('cleverreach/autoconfigure/startserverconfiguration'),
            'checkStatusUrl' => $this->backendUrl->getUrl('cleverreach/autoconfigure/checkstatus'),
            'autoconfigureFailed' => !empty($autoconfigureState) && $autoconfigureState === 'failed',
            'helpUrl' => 'https://support.cleverreach.de/hc/en-us/requests/new',
        ];
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
