<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\Import;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\InitialSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use Magento\Backend\App\Action;

class CheckStatus extends Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /** @var  Configuration */
    private $configService;

    /** @var  Queue */
    private $queue;

    /**
     * CheckStatus Controller
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

    /**
     * Check status execution
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $syncTaskQueueItem = $this->getQueueService()->findLatestByType('InitialSyncTask');
        if ($syncTaskQueueItem === null) {
            return $this->resultJsonFactory->create()->setData([
                'status' => QueueItem::FAILED,
            ]);
        }

        /** @var InitialSyncTask $initialSyncTask */
        $initialSyncTask = $syncTaskQueueItem->getTask();
        $initialSyncTaskProgress = $initialSyncTask->getProgressByTask();

        return $this->resultJsonFactory->create()->setData([
            'status' => $syncTaskQueueItem->getStatus(),
            'statistics' => [
                'recipients_count' => $initialSyncTask->getSyncedRecipientsCount(),
                'group_name' => $this->getConfigService()->getIntegrationName(),
            ],
            'taskStatuses' => [
                'subscriberlist' => [
                    'status' => $this->getStatus($initialSyncTaskProgress['subscriberList']),
                    'progress' => $initialSyncTaskProgress['subscriberList'],
                ],
                'add_fields' => [
                    'status' => $this->getStatus($initialSyncTaskProgress['fields']),
                    'progress' => $initialSyncTaskProgress['fields'],
                ],
                'recipient_sync' => [
                    'status' => $this->getStatus($initialSyncTaskProgress['recipients']),
                    'progress' => $initialSyncTaskProgress['recipients'],
                ],
            ],
        ]);
    }

    private function getStatus($progress)
    {
        $status = QueueItem::QUEUED;
        if (0 < $progress && $progress < 100) {
            $status = QueueItem::IN_PROGRESS;
        } elseif ($progress >= 100) {
            $status = QueueItem::COMPLETED;
        }

        return $status;
    }

    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    private function getQueueService()
    {
        if ($this->queue === null) {
            $this->queue = ServiceRegister::getService(Queue::CLASS_NAME);
        }

        return $this->queue;
    }
}
