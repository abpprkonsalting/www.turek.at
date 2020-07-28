<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Framework\Event\Observer;

class CustomerRegisteredObserver extends BaseObserver
{
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;

    /**
     * ProductViewObserver constructor.
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

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getEvent()->getData('customer');
        $id = $customer->getId();

        Logger::logInfo('New customer register event detected. Customer id: ' . $id, 'Integration');

        $this->enqueueTask(new RecipientSyncTask([RecipientService::CUSTOMER_ID_PREFIX . $id]));
    }
}
