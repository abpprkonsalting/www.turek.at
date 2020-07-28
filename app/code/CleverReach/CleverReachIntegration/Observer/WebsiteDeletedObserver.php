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
use Magento\Store\Model\Website;

class WebsiteDeletedObserver extends BaseObserver
{
    /**
     * @var array $dataForUpdate
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
     * WebsiteDeletedObserver constructor.
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

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();

        $data = $observer->getEvent()->getData();
        $event = $data['name'];
        $object = $data['data_object'];

        $this->handleShopTagDeleted($event, $object);
    }

    /**
     * @param $event
     * @param Website $website
     */
    private function handleShopTagDeleted($event, Website $website)
    {
        if ($event === 'website_delete_before') {
            $this->saveValuesFromWebsite($website);
        } else {
            Logger::logInfo(
                'Shop tag delete event detected. Tag id: ' . $website->getData()['website_id'],
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
     * @param Website $website
     */
    private function saveValuesFromWebsite(Website $website)
    {
        /** @var RecipientService $recipientService */
        $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);

        self::$dataForUpdate['customersIds'] = $recipientService->addPrefixToRecipientsIds(
            $recipientService->getCustomersByWebsiteId($this->customerFactory, $website->getData()['website_id']),
            RecipientService::CUSTOMER_ID_PREFIX
        );

        self::$dataForUpdate['tagForDelete'] = $this->formatTagForDelete(
            $website->getData()['name'],
            RecipientService::WEBSITE_TAG
        );
    }
}
