<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

/**
 * Class RecipientDeactivateNewsletterStatusSyncTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 * @deprecated
 */
class RecipientDeactivateNewsletterStatusSyncTask extends RecipientStatusUpdateSyncTask
{
    /**
     * Runs task execution.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    public function execute()
    {
        $this->getProxy()->updateNewsletterStatus($this->recipientEmails);
        $this->reportProgress(100);
    }
}
