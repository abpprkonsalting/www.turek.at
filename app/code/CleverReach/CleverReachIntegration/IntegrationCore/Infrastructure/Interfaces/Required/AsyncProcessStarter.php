<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\Runnable;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;

/**
 * Interface AsyncProcessStarter
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required
 */
interface AsyncProcessStarter
{
    const CLASS_NAME = __CLASS__;

    /**
     * Starts given runner asynchronously (in new process/web request or similar)
     *
     * @param Runnable $runner Runner that should be started async
     *
     * @throws ProcessStarterSaveException
     */
    public function start(Runnable $runner);
}
