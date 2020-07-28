<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Utility\Filter;

/**
 * Class DeletePrefixedFilterSyncTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 */
class DeletePrefixedFilterSyncTask extends BaseSyncTask
{
    const INITIAL_PROGRESS_PERCENT = 10;

    /**
     * Array of tags.
     *
     * @var array $prefixedShopTags
     */
    private $prefixedShopTags;
    /**
     * Current progress in percentage.
     *
     * @var int $progressPercent
     */
    private $progressPercent = self::INITIAL_PROGRESS_PERCENT;
    /**
     * Current progress step.
     *
     * @var int $progressStep
     */
    private $progressStep;

    /**
     * DeletePrefixedFilterSyncTask constructor.
     *
     * @param array|null $prefixedShopTags Array of tags.
     */
    public function __construct($prefixedShopTags)
    {
        $this->prefixedShopTags = $prefixedShopTags;
    }

    /**
     * String representation of object
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this->prefixedShopTags);
    }

    /**
     * Constructs the object.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->prefixedShopTags = unserialize($serialized);
    }

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
        if (empty($this->prefixedShopTags)) {
            $this->reportProgress(100);
            return;
        }

        $this->reportProgress($this->progressPercent);
        $integrationId = $this->getConfigService()->getIntegrationId();
        $this->reportAlive();
        $allCRFilters = $this->getProxy()->getAllFilters($integrationId);
        if (empty($allCRFilters)) {
            $this->reportProgress(100);
            return;
        }

        $this->progressStep = (100 - $this->progressPercent) / count($allCRFilters);
        $this->deleteFilters($allCRFilters, $integrationId);

        $this->reportProgress(100);
    }

    /**
     * Deletes filter on CleverReach side.
     *
     * @param Filter[]|null $allCRFilters List of all filters from CleverReach.
     * @param string $integrationId CleverReach integration ID.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    private function deleteFilters($allCRFilters, $integrationId)
    {
        foreach ($allCRFilters as $filter) {
            if (in_array($filter->getFirstCondition(), $this->prefixedShopTags, true)) {
                $this->getProxy()->deleteFilter($filter->getId(), $integrationId);
            }

            /* @noinspection DisconnectedForeachInstructionInspection */
            $this->incrementProgress();
        }
    }

    /**
     * Increments and report progress for given step.
     */
    private function incrementProgress()
    {
        $this->progressPercent += $this->progressStep;
        $this->reportProgress($this->progressPercent);
    }
}
