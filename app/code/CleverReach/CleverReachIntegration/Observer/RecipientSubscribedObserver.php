<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;

class RecipientSubscribedObserver extends BaseObserver
{
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;

    /**
     * CustomerGroupSavedObserver constructor.
     *
     * @param \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
     */
    public function __construct(
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
    ) {
        $this->initializer = $initializer;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->initializer->registerServices();

        $subscriber = $observer->getEvent()->getData('subscriber');
        $id = $subscriber->getId();
        $customerId = $subscriber->getCustomerId();

        if (!empty($customerId)) {
            $this->enqueueTask(new RecipientSyncTask([RecipientService::CUSTOMER_ID_PREFIX . $customerId]));

            return;
        }

        Logger::logInfo('New subscribe/unsubscribe event detected. Subscriber id: ' . $id, 'Integration');
        $this->enqueueTask(new RecipientSyncTask([RecipientService::SUBSCRIBER_ID_PREFIX . $id]));
    }
}
