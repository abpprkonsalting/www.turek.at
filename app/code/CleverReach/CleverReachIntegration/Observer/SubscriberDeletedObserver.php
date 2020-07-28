<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientDeactivateSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Framework\Event\Observer;

class SubscriberDeletedObserver extends BaseObserver
{
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * ProductViewObserver constructor.
     *
     * @param \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->initializer = $initializer;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();

        $data = $observer->getEvent()->getData();
        $object = $data['data_object'];

        $this->handleSubscriberDeletedEvent($object);
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriberData
     */
    private function handleSubscriberDeletedEvent(\Magento\Newsletter\Model\Subscriber $subscriberData)
    {
        $subscriberData = $subscriberData->getData();

        try {
            $this->customerRepositoryInterface->getById($subscriberData['customer_id']);
            $this->enqueueTask(new RecipientSyncTask([RecipientService::CUSTOMER_ID_PREFIX . $subscriberData['customer_id']]));
        } catch (\Exception $exception) {
            // customer with given id doesn't exist, so deactivation is required
            $this->enqueueTask(new RecipientDeactivateSyncTask([$subscriberData['subscriber_email']]));
        }

        Logger::logInfo(
            'Subscriber event deleted event detected. Subscriber email: ' . $subscriberData['subscriber_email'],
            'Integration'
        );
    }
}
