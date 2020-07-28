<?php
namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\CampaignOrderSync;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientDeactivateSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;

class OrderCreatedObserver extends BaseObserver
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\InitializerInterface
     */
    private $initializer;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $subscriberFactory;

    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberFactory
    ) {
        $this->catalogSession = $catalogSession;
        $this->initializer = $initializer;
        $this->subscriberFactory = $subscriberFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->initializer->registerServices();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData()['order'];
        $orderItems = $order->getAllVisibleItems();
        $mailingId = $this->catalogSession->getData('mailingId');
        $dataForSync = [];

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $dataForSync[$orderItem->getId()] = $mailingId;
        }

        $this->enqueueTask(new CampaignOrderSync($dataForSync));

        $customerId = $order->getCustomerId();
        $customerEmail = $order->getCustomerEmail();

        if (!empty($customerId)) {
            $this->enqueueTask(new RecipientSyncTask(
                [RecipientService::CUSTOMER_ID_PREFIX . $customerId]
            ));

            return;
        }

        $subscriberIds = $this->subscriberFactory->create()
            ->addFieldToFilter('subscriber_email', $customerEmail)
            ->getAllIds();

        if (!empty($subscriberIds)) {
            /** @var RecipientService $recipientService */
            $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);
            $this->enqueueTask(new RecipientSyncTask(
                $recipientService->addPrefixToRecipientsIds($subscriberIds, RecipientService::SUBSCRIBER_ID_PREFIX)
            ));

            return;
        }

        // If user is not a customer and not a subscriber then he is guest customer
        // so we need to deactivate him since CampaignOrderSync task will set recipient as active
        $this->enqueueTask(new RecipientDeactivateSyncTask([$customerEmail]));
    }
}
