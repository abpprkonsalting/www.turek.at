<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in the system.
     *
     * @param LogData|null $data Log data object.
     */
    public function logMessage($data);
}
