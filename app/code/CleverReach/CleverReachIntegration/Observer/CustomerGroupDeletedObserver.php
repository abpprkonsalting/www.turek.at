<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\FilterSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Event\Observer;
use \Magento\Customer\Model\Group;

class CustomerGroupDeletedObserver extends BaseObserver
{
    /**
     * @var $dataForUpdate
     */
    private static $dataForUpdate;
    /**
     * @var CollectionFactory $customerFactory
     */
    private $customerFactory;
    /**
     * @var InitializerInterface $initializer
     */
    private $initializer;

    /**
     * CustomerGroupDeletedObserver constructor.
     *
     * @param CollectionFactory $customerFactory
     * @param InitializerInterface $initializer
     */
    public function __construct(
        CollectionFactory $customerFactory,
        InitializerInterface $initializer
    ) {
        $this->customerFactory = $customerFactory;
        $this->initializer = $initializer;
    }


    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();
        $data = $observer->getEvent()->getData();
        $event = $data['name'];
        $object = $data['object'];
        $this->handleCustomerTagDeleted($event, $object);
    }

    /**
     * @param string $event
     * @param Group $group
     */
    private function handleCustomerTagDeleted($event, Group $group)
    {
        if ($event === 'customer_group_delete_before') {
            $this->saveValuesForUpdate($group);
        } else {
            Logger::logInfo(
                'Customer tag delete event detected. Tag id: ' . $group->getData()['customer_group_id'],
                'Integration'
            );

            $this->enqueueTask(
                new RecipientSyncTask(
                    self::$dataForUpdate['customersIds'],
                    self::$dataForUpdate['tagForDelete'],
                    false
                )
            );
            $this->enqueueTask(new FilterSyncTask());
        }
    }

    /**
     * @param Group $group
     */
    private function saveValuesForUpdate(Group $group)
    {
        /** @var RecipientService $recipientService */
        $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);

        self::$dataForUpdate['customersIds'] = $recipientService->addPrefixToRecipientsIds(
            $recipientService->getCustomersByGroupId($this->customerFactory, $group->getData()['customer_group_id']),
            $recipientService::CUSTOMER_ID_PREFIX
        );

        self::$dataForUpdate['tagForDelete'] = $this->formatTagForDelete(
            $group->getData()['customer_group_code'],
            RecipientService::CUSTOMER_GROUP_TAG
        );
    }
}
