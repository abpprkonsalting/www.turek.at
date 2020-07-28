<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\Auth;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use Magento\Backend\App\Action;

class CheckStatus extends Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

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
        $status = 'finished';
        $queue = ServiceRegister::getService(
            Queue::CLASS_NAME
        );
        /** @var QueueItem $queueItem */
        $queueItem = $queue->findLatestByType('RefreshUserInfoTask');

        if ($queueItem !== null) {
            $queueStatus = $queueItem->getStatus();
            if ($queueStatus !== QueueItem::FAILED
                && $queueStatus !== QueueItem::COMPLETED) {
                $status = 'in_progress';
            }
        }

        return $this->resultJsonFactory->create()->setData([
            'status' => $status
        ]);
    }
}
