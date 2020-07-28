<?php

namespace CleverReach\CleverReachIntegration\Observer;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\FilterSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class WebsiteSavedObserver extends BaseObserver
{
    private static $dataForUpdate;
    private $request;
    private $websiteFactory;
    private $customerFactory;
    /**
     * @var InitializerInterface
     */
    private $initializer;

    public function __construct(
        RequestInterface $request,
        CollectionFactory $websiteFactory,
        CustomerCollectionFactory $customerFactory,
        InitializerInterface $initializer
    ) {
        $this->request = $request;
        $this->websiteFactory = $websiteFactory;
        $this->customerFactory = $customerFactory;
        $this->initializer = $initializer;
    }

    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();

        /** @var \Magento\Framework\Event $event */
        $event = $observer->getData()['event'];
        /** @var \Magento\Store\Model\Website $website */
        $website = $observer->getData()['website'];
        $params = $this->request->getParams()['website'];

        /** @var RecipientService $recipientService */
        $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);
        if ($event->getData('name') === 'website_save_before') {
            $this->saveValuesForUpdate($params, $recipientService);
        } else {
            if (!empty($params['website_id'])) {
                $logMessage = 'New customer tag update event detected. Tag id: ' . $params['website_id'];
                $this->enqueueTask(
                    new RecipientSyncTask(
                        self::$dataForUpdate['customersIds'],
                        self::$dataForUpdate['tagForDelete'],
                        false
                    )
                );
            } else {
                $logMessage = 'New customer tag create event detected. Tag id: ' . $website->getId();
            }

            Logger::logInfo($logMessage, 'Integration');
            $this->enqueueTask(new FilterSyncTask());
        }
    }

    /**
     * @param array $params
     * @param RecipientService $recipientService
     */
    private function saveValuesForUpdate($params, $recipientService)
    {
        if (!empty($params['website_id'])) {
            self::$dataForUpdate['customersIds'] = $recipientService->addPrefixToRecipientsIds($recipientService->getCustomersByWebsiteId(
                $this->customerFactory,
                $params['website_id']
            ), $recipientService::CUSTOMER_ID_PREFIX);
            $oldTagName = $this->websiteFactory->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter(
                    'website_id',
                    $params['website_id']
                )
                ->getData()[0]['name'];
            self::$dataForUpdate['tagForDelete'] = $this->formatTagForDelete(
                $oldTagName,
                RecipientService::WEBSITE_TAG
            );
        }
    }
}
