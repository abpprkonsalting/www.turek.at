<?php

namespace CleverReach\CleverReachIntegration\Block\Adminhtml\Content;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use Magento\Backend\Block\Template;

class Initialsync extends Template
{

    private $configService;


    /** @var \Magento\Backend\Model\UrlInterface  */
    private $backendUrl;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    public function getInitialSyncConfig()
    {
        $statusCheckUrl = $this->backendUrl->getUrl('cleverreach/import/checkstatus');

        return [
            'statusCheckUrl' => $statusCheckUrl,
            'recipientSyncTaskTitle' => sprintf(
                __('Import recipients from %s to CleverReach®'),
                $this->getConfigService()->getIntegrationName()
            ),
        ];
    }

    private function getConfigService()
    {
        if (empty($this->configService)) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
