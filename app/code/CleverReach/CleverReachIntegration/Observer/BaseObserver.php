<?php
namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\Tag;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\TagCollection;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;

abstract class BaseObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var ConfigService $configService
     */
    private $configService;

    /**
     * @param Task $task
     */
    protected function enqueueTask(Task $task)
    {
        if (!$this->isAuthTokenValid()) {
            return;
        }

        try {
            /** @var Queue $queueService */
            $queueService = ServiceRegister::getService(Queue::CLASS_NAME);
            $queueService->enqueue($this->getConfigService()->getQueueName(), $task);
        } catch (QueueStorageUnavailableException $ex) {
            Logger::logDebug(
                json_encode([
                    'Message' => 'Failed to enqueue task ' . $task->getType(),
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                    'ShopData' => serialize($task)
                ]),
                'Integration'
            );
        }
    }

    /**
     * @param string $tag
     * @param string $type
     *
     * @return TagCollection
     */
    protected function formatTagForDelete($tag, $type)
    {
        return new TagCollection([new Tag($tag, $type)]);
    }

    /**
     * @return ConfigService
     */
    protected function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * @return bool
     */
    private function isAuthTokenValid()
    {
        $accessToken = $this->getConfigService()->getAccessToken();

        return $accessToken !== null && $accessToken !== '';
    }
}
