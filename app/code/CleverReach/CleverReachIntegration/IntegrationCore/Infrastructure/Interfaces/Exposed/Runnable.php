<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed;

/**
 * Interface Runnable
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed
 */
interface Runnable extends \Serializable
{
    /**
     * Starts runnable run logic.
     */
    public function run();
}
