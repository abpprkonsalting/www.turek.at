<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\ConfigRepositoryInterface;
use CleverReach\CleverReachIntegration\Model\ResourceModel\ConfigModel;

class ConfigRepositoryService implements ConfigRepositoryInterface
{
    /**
     * @var ConfigModel
     */
    private $sourceConfig;

    /**
     * ConfigRepositoryService constructor.
     *
     * @param ConfigModel $sourceConfig
     */
    public function __construct(ConfigModel $sourceConfig)
    {
        $this->sourceConfig = $sourceConfig;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->sourceConfig->getValue($key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return $this->sourceConfig->updateValue($key, $value);
    }
}
