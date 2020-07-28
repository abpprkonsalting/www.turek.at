<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class BaseSyncTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 */
abstract class BaseSyncTask extends Task
{
    /**
     * Instance of proxy class.
     *
     * @var Proxy
     */
    private $proxy;

    /**
     * Gets proxy class instance.
     *
     * @return \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy
     *   Instance of proxy class.
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            $this->proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
        }

        return $this->proxy;
    }
}
