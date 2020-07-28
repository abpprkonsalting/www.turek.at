<?php

namespace  CleverReach\CleverReachIntegration\Controller\Adminhtml\Dashboard;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\InitialSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Backend\App\Action;

class RetrySync extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    private $queueService;

    private $configService;

    /**
     * RetrySync Controller
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
        $this->getQueueService()->enqueue(
            $this->getConfigService()->getQueueName(),
            new InitialSyncTask()
        );

        return $this->resultJsonFactory->create()->setData([
            'status' => 'success'
        ]);
    }

    /**
     * @return Queue
     */
    private function getQueueService()
    {
        if (empty($this->queueService)) {
            $this->queueService = ServiceRegister::getService(Queue::CLASS_NAME);
        }

        return $this->queueService;
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
