<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

class ExchangeAccessTokenTask extends BaseSyncTask
{
    /**
     * Refreshes CleverReach tokens.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     */
    public function execute()
    {
        $this->reportProgress(5);

        $configService = $this->getConfigService();
        $configService->setAccessTokenExpirationTime(10000);

        $result = $this->getProxy()->exchangeToken();

        $configService->setAuthInfo($result);

        $this->reportProgress(100);
    }
}
