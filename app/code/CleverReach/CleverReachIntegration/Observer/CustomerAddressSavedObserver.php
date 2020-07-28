<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Framework\Event\Observer;

class CustomerAddressSavedObserver extends BaseObserver
{
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;
    /**
     * @var array
     */
    private static $processedCustomers;

    /**
     * ProductViewObserver constructor.
     *
     * @param \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
     */
    public function __construct(
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
    ) {
        $this->initializer = $initializer;
    }

    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();

        $customerId = $observer->getCustomerAddress()->getCustomerId();
        if (empty($customerId) || in_array($customerId, self::getProcessedCustomers())) {
            return;
        }

        Logger::logInfo('Customer created/updated event detected. Customer id: ' . $customerId, 'Integration');

        $this->enqueueTask(new RecipientSyncTask([RecipientService::CUSTOMER_ID_PREFIX . $customerId]));
        self::$processedCustomers[] = $customerId;
    }

    /**
     * Getting already processed customers.
     *
     * @return array
     */
    private static function getProcessedCustomers()
    {
        if (self::$processedCustomers === null) {
            self::$processedCustomers = [];
        }

        return self::$processedCustomers;
    }
}
