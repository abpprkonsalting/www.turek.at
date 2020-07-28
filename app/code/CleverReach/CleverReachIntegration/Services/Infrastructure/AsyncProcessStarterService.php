<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\AsyncProcessStarter;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\Runnable;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\HttpClient;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\GuidProvider;

class AsyncProcessStarterService implements AsyncProcessStarter
{

    /**
     * @var HttpClientService
     */
    private $httpClientService;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \CleverReach\CleverReachIntegration\Model\ResourceModel\Process
     */
    private $resourceProcess;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CleverReach\CleverReachIntegration\Model\ResourceModel\Process $resourceProcess
    ) {
        $this->storeManager = $storeManager;
        $this->resourceProcess = $resourceProcess;
    }

    /**
     * @param Runnable $runner
     * @throws HttpRequestException
     * @throws ProcessStarterSaveException
     */
    public function start(Runnable $runner)
    {
        $guidProvider = new GuidProvider();
        $guid = trim($guidProvider->generateGuid());

        $this->saveGuidAndRunner($guid, $runner);
        $this->startRunnerAsynchronously($guid);
    }

    /**
     * @param Runnable $runner
     * @param string $guid
     * @throws ProcessStarterSaveException
     */
    private function saveGuidAndRunner($guid, $runner)
    {
        try {
            // Add transaction callback to make sure that new runner process is started even when process instance is
            // created during event that is part of already started transaction
            $this->resourceProcess->addCommitCallback(function () use ($guid) {
                $this->startRunnerAsynchronously($guid);
            });

            $this->resourceProcess->saveGuidAndRunner($guid, $runner);
        } catch (\Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new ProcessStarterSaveException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $guid
     * @throws HttpRequestException
     */
    private function startRunnerAsynchronously($guid)
    {
        try {
            $this->getHttpClient()->requestAsync('POST', $this->formatAsyncProcessStartUrl($guid));
        } catch (\Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new HttpRequestException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $guid
     * @return string
     */
    private function formatAsyncProcessStartUrl($guid)
    {
        $url = $this->storeManager->getStore()->getBaseUrl()
            . 'cleverreach/async/process/guid/'
            . $guid
            . '/ajax/1';
        
        return str_replace('https:', 'http:', $url);
    }

    private function getHttpClient()
    {
        if (empty($this->httpClientService)) {
            $this->httpClientService = ServiceRegister::getService(HttpClient::CLASS_NAME);
        }

        return $this->httpClientService;
    }
}
