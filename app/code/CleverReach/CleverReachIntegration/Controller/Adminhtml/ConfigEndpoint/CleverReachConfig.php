<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\ConfigEndpoint;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\TaskRunnerWakeup;

class CleverReachConfig extends \Magento\Backend\App\Action
{

    protected $_publicActions = ['cleverreachconfig'];

    /** @var ConfigService */
    private $configService;

    /** @var \CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem */
    private $queueItem;

    private $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem $queueItem
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->queueItem = $queueItem;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $this->resultJsonFactory->create()->setHeader('Content-type', 'application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return  $this->resultJsonFactory->create()->setData($this->post());
        }

        return $this->resultJsonFactory->create()->setData($this->getConfigParameters());
    }

    private function getConfigParameters()
    {
        return [
            'integrationId' => $this->configService->getIntegrationId(),
            'integrationName' => $this->configService->getIntegrationName(),
            'minLogLevel' => $this->configService->getMinLogLevel(),
            'isProductSearchEnabled' => $this->configService->isProductSearchEnabled(),
            'productSearchParameters' => $this->configService->getProductSearchParameters(),
            'recipientsSynchronizationBatchSize' => $this->configService->getRecipientsSynchronizationBatchSize(),
            'maxStartedTasksLimit' => $this->configService->getMaxStartedTasksLimit(),
            'maxTaskExecutionRetries' => $this->configService->getMaxTaskExecutionRetries(),
            'maxTaskInactivityPeriod' => $this->configService->getMaxTaskInactivityPeriod(),
            'taskRunnerMaxAliveTime' => $this->configService->getTaskRunnerMaxAliveTime(),
            'taskRunnerStatus' => $this->configService->getTaskRunnerStatus(),
            'taskRunnerWakeupDelay' => $this->configService->getTaskRunnerWakeupDelay(),
            'queueName' => $this->configService->getQueueName(),
            'clientId' => $this->configService->getClientId(),
            'clientSecret' => $this->configService->getClientSecret(),
            'asyncProcessRequestTimeout' => $this->configService->getAsyncProcessRequestTimeout(),
            'webhookUrl' => $this->configService->getCrEventHandlerURL(),
        ];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function post()
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        if (isset($payload['minLogLevel'])) {
            $this->configService->saveMinLogLevel($payload['minLogLevel']);
        }

        if (isset($payload['defaultLoggerStatus'])) {
            $this->configService->setDefaultLoggerEnabled($payload['defaultLoggerStatus']);
        }

        if (!empty($payload['maxStartedTasksLimit'])) {
            $this->configService->setMaxStartedTaskLimit($payload['maxStartedTasksLimit']);
        }

        if (!empty($payload['taskRunnerWakeUpDelay'])) {
            $this->configService->setTaskRunnerWakeUpDelay($payload['taskRunnerWakeUpDelay']);
        }

        if (!empty($payload['taskRunnerMaxAliveTime'])) {
            $this->configService->setTaskRunnerMaxAliveTime($payload['taskRunnerMaxAliveTime']);
        }

        if (!empty($payload['maxTaskExecutionRetries'])) {
            $this->configService->setMaxTaskExecutionRetries($payload['maxTaskExecutionRetries']);
        }

        if (!empty($payload['maxTaskInactivityPeriod'])) {
            $this->configService->setMaxTaskInactivityPeriod($payload['maxTaskInactivityPeriod']);
        }

        if (!empty($payload['productSearchEndpointPassword'])) {
            $this->configService->setProductSearchEndpointPassword($payload['productSearchEndpointPassword']);
        }

        if (!empty($payload['asyncProcessRequestTimeout'])) {
            $this->configService->setAsyncProcessRequestTimeout($payload['asyncProcessRequestTimeout']);
        }

        if (isset($payload['sendWakeupSignal'])) {
            /** @var TaskRunnerWakeup $taskRunnerWakeup */
            $taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
            $taskRunnerWakeup->wakeup();
        }

        if (isset($payload['resetToken'])) {
            $this->configService->setAccessToken(null);
            $this->configService->setUserInfo(null);
            $this->queueItem->deleteQueueItemByWhereCondition('type = "InitialSyncTask"');
        }

        return ['message' => 'Successfully updated config values'];
    }
}
