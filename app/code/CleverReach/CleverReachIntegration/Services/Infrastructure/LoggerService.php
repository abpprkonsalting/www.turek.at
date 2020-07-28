<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\ShopLoggerAdapter;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\LogData;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;

class LoggerService implements ShopLoggerAdapter
{

    private $logger;
    
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log message in system
     *
     * @param LogData $data
     */
    public function logMessage($data)
    {

        if ($data->getLogLevel() > ServiceRegister::getService(Configuration::CLASS_NAME)->getMinLogLevel()) {
            return;
        }

        switch ($data->getLogLevel()) {
            case Logger::ERROR:
                $level = 'error';
                break;
            case Logger::WARNING:
                $level = 'warning';
                break;
            case Logger::DEBUG:
                $level = 'debug';
                break;
            default:
                $level = 'info';
        }

        if (!method_exists($this->logger, $level)) {
            $level = 'info';
        }

        call_user_func(
            [$this->logger, $level],
            'Date: ' . $data->getTimestamp() .
            ' Message: ' . $data->getMessage()
        );
    }
}
