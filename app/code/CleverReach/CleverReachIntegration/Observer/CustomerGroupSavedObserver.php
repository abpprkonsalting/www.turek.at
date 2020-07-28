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
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;

class CustomerGroupSavedObserver extends BaseObserver
{
    /**
     * @var RequestInterface $request
     */
    private $request;
    /**
     * @var array$dataForUpdate
     */
    private static $dataForUpdate;
    /**
     * @var CollectionFactory $customerFactory
     */
    private $customerFactory;
    /**
     * @var GroupCollectionFactory $groupFactory
     */
    private $groupFactory;

    /**
     * @var InitializerInterface
     */
    private $initializer;

    /**
     * CustomerGroupSavedObserver constructor.
     *
     * @param RequestInterface $request
     * @param CollectionFactory $customerFactory
     * @param GroupCollectionFactory $groupFactory
     * @param InitializerInterface $initializer
     */
    public function __construct(
        RequestInterface $request,
        CollectionFactory $customerFactory,
        GroupCollectionFactory $groupFactory,
        InitializerInterface $initializer
    ) {
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->groupFactory = $groupFactory;
        $this->initializer = $initializer;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();
        $data = $observer->getEvent()->getData();
        $event = $data['name'];
        $params = $this->request->getParams();

        /** @var RecipientService $recipientService */
        $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);

        if ($event === 'customer_group_save_before') {
            //saving old tag name for delete
            $this->saveDataForUpdate($params, $recipientService);
        } else {
            if (!empty($params['id'])) {
                $logMessage = 'New customer tag update event detected. Tag id: ' . $params['id'];
                $this->enqueueTask(
                    new RecipientSyncTask(
                        self::$dataForUpdate['customersIds'],
                        self::$dataForUpdate['tagForDelete'],
                        false
                    )
                );
            } else {
                $logMessage = 'New customer tag create event detected. Tag id: ' . $data['object']->getId();
            }

            Logger::logInfo($logMessage, 'Integration');
            $this->enqueueTask(new FilterSyncTask());
        }
    }

    /**
     * @param array $params
     * @param RecipientService $recipientService
     */
    private function saveDataForUpdate($params, $recipientService)
    {
        if (!empty($params['id'])) {
            self::$dataForUpdate['customersIds'] = $recipientService->addPrefixToRecipientsIds(
                $recipientService->getCustomersByGroupId($this->customerFactory, $params['id']),
                RecipientService::CUSTOMER_ID_PREFIX
            );

            $oldTagName = $this->groupFactory->create()
                ->addFieldToSelect('customer_group_code')
                ->addFieldToFilter('customer_group_id', $params['id'])->getData()[0]['customer_group_code'];
            self::$dataForUpdate['tagForDelete'] = $this->formatTagForDelete(
                $oldTagName,
                RecipientService::CUSTOMER_GROUP_TAG
            );
        }
    }
}
