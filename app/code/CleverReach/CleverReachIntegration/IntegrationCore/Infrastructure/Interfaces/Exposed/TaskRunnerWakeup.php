<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed;

/**
 * Interface TaskRunnerWakeup
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed
 */
interface TaskRunnerWakeup
{
    const CLASS_NAME = __CLASS__;

    /**
     * Wakes up TaskRunner instance asynchronously.
     *
     * If active instance is already running do nothing.
     */
    public function wakeup();
}
