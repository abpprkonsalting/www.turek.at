<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class RegisterEventHandlerTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 */
class RegisterEventHandlerTask extends BaseSyncTask
{
    const RECEIVER_EVENT = 'receiver';

    /**
     * Runs task logic.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    public function execute()
    {
        $this->reportProgress(5);
        $configService = $this->getConfigService();
        $eventHookParams = [
            'url' => $configService->getCrEventHandlerURL(),
            'event' => self::RECEIVER_EVENT,
            'verify' => $configService->getCrEventHandlerVerificationToken(),
        ];

        if (stripos($eventHookParams['url'], 'https://') === 0) {
            $callToken = $this->getProxy()->registerEventHandler($eventHookParams);
            $configService->setCrEventHandlerCallToken($callToken);
        } else {
            Logger::logWarning('Cannot register CleverReach event hook for non-HTTPS domains.');
        }

        $this->reportProgress(100);
    }
}
