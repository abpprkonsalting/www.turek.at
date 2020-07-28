<?php

namespace CleverReach\CleverReachIntegration\Helper;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Attributes;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\OrderItems;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy as ProxyInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\DefaultLoggerAdapter;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\AsyncProcessStarter;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\ConfigRepositoryInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\HttpClient;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\ShopLoggerAdapter;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\TaskQueueStorage;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\TaskRunnerStatusStorage as TaskRunnerStatusStorageInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\TaskRunnerWakeup as TaskRunnerWakeupInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\DefaultLogger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\TaskRunner;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\TaskRunnerWakeup;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\TaskRunnerStatusStorage;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\GuidProvider;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\TimeProvider;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\AttributesService;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\OrderItemsService;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\AsyncProcessStarterService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigRepositoryService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\HttpClientService;
use CleverReach\CleverReachIntegration\Services\Infrastructure\TaskQueueStorageService;
use InvalidArgumentException;

class Initializer implements InitializerInterface
{
    private static $servicesRegistered = false;

    private $httpClientService;

    private $shopLogger;

    private $asyncProcessStarterService;

    private $configService;

    private $orderItemsService;

    private $recipientService;

    private $taskQueueStorageService;

    private $configRepositoryService;

    public function __construct(
        HttpClientService $httpClientService,
        ShopLoggerAdapter $shopLogger,
        AsyncProcessStarterService $asyncProcessStarterService,
        ConfigService $configService,
        OrderItemsService $orderItemsService,
        RecipientService $recipientService,
        TaskQueueStorageService $taskQueueStorageService,
        ConfigRepositoryService $configRepositoryService
    ) {
        $this->httpClientService = $httpClientService;
        $this->shopLogger = $shopLogger;
        $this->asyncProcessStarterService = $asyncProcessStarterService;
        $this->configService = $configService;
        $this->orderItemsService = $orderItemsService;
        $this->recipientService = $recipientService;
        $this->taskQueueStorageService = $taskQueueStorageService;
        $this->configRepositoryService = $configRepositoryService;
    }

    /**
     * register all services
     */
    public function registerServices()
    {
        if (self::$servicesRegistered) {
            return;
        }

        self::$servicesRegistered = true;
        try {
            ServiceRegister::registerService(
                HttpClient::CLASS_NAME,
                function () {
                    return $this->httpClientService;
                }
            );

            ServiceRegister::registerService(
                AsyncProcessStarter::CLASS_NAME,
                function () {
                    return $this->asyncProcessStarterService;
                }
            );

            ServiceRegister::registerService(
                Configuration::CLASS_NAME,
                function () {
                    return $this->configService;
                }
            );

            ServiceRegister::registerService(
                ConfigRepositoryInterface::CLASS_NAME,
                function () {
                    return $this->configRepositoryService;
                }
            );

            ServiceRegister::registerService(
                ShopLoggerAdapter::CLASS_NAME,
                function () {
                    return $this->shopLogger;
                }
            );

            ServiceRegister::registerService(
                DefaultLoggerAdapter::CLASS_NAME,
                function () {
                    return new DefaultLogger();
                }
            );

            ServiceRegister::registerService(
                TaskRunnerStatusStorageInterface::CLASS_NAME,
                function () {
                    return new TaskRunnerStatusStorage();
                }
            );

            ServiceRegister::registerService(
                TimeProvider::CLASS_NAME,
                function () {
                    return new TimeProvider();
                }
            );

            ServiceRegister::registerService(
                Queue::CLASS_NAME,
                function () {
                    return new Queue();
                }
            );

            ServiceRegister::registerService(
                TaskQueueStorage::CLASS_NAME,
                function () {
                    return $this->taskQueueStorageService;
                }
            );

            ServiceRegister::registerService(
                ProxyInterface::CLASS_NAME,
                function () {
                    return new Proxy();
                }
            );

            ServiceRegister::registerService(
                Recipients::CLASS_NAME,
                function () {
                    return $this->recipientService;
                }
            );

            ServiceRegister::registerService(
                OrderItems::CLASS_NAME,
                function () {
                    return $this->orderItemsService;
                }
            );

            ServiceRegister::registerService(
                TaskRunnerWakeUpInterface::CLASS_NAME,
                function () {
                    return new TaskRunnerWakeup();
                }
            );

            ServiceRegister::registerService(
                TaskRunner::CLASS_NAME,
                function () {
                    return new TaskRunner();
                }
            );

            ServiceRegister::registerService(
                GuidProvider::CLASS_NAME,
                function () {
                    return new GuidProvider();
                }
            );

            ServiceRegister::registerService(
                Attributes::CLASS_NAME,
                function () {
                    return new AttributesService();
                }
            );
        } catch (InvalidArgumentException $exception) {
            // Don't do nothing if service is already register
        }
    }
}
