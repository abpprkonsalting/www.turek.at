<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\AutoConfigure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Backend\App\Action;

class CheckStatus extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    
    private $configService;

    /**
     * BuildFirstEmail Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $status = $this->getStatus();

        return $this->resultJsonFactory->create()->setData([
            'status' => !empty($status) ? $status : 'pending'
        ]);
    }

    private function getStatus()
    {
        $status = $this->getConfigService()->getAutoConfigureState();
        $startTime = $this->getConfigService()->getAutoConfigureStartTime();

        if (($status === 'in_progress') && (($startTime + 120) < time())) {
            $this->getConfigService()->setAutoConfigureState('failed');
            $status = 'failed';
        }

        return $status;
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
