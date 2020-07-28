<?php

namespace CleverReach\CleverReachIntegration\Services\BusinessLogic;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\Recipient;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\SpecialTag;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\SpecialTagCollection;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\Tag;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\TagCollection;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\OrderItems;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use Magento\Store\Model\Store;

class RecipientService implements Recipients
{
    const CUSTOMER_GROUP_TAG = 'Group';

    const WEBSITE_TAG = 'Website';

    const CUSTOMER_ID_PREFIX = 'C-';

    const SUBSCRIBER_ID_PREFIX = 'S-';

    /** @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory */
    private $customerFactory;

    /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberFactory */
    private $subscriberFactory;

    /** @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory */
    private $customerGroupFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory */
    private $orderFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemFactory */
    private $orderItemFactory;

    /** @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteFactory */
    private $websiteFactory;

    /** @var \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection */
    private $storeCollection;

    /** @var  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /**
     * RecipientService constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemFactory
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemFactory,
        \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->customerFactory = $customerFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->websiteFactory = $websiteFactory;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->storeCollection = $storeCollection;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getAllTags()
    {
        return $this->getAllWebsites()->add($this->getTagsFormatted($this->customerGroupFactory->create()));
    }

    /**
     * @return SpecialTagCollection
     */
    public function getAllSpecialTags()
    {
        return new SpecialTagCollection([SpecialTag::customer(), SpecialTag::subscriber(), SpecialTag::buyer()]);
    }

    /**
     * @param array $batchRecipientIds
     * @param bool $includeOrders
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRecipientsWithTags(array $batchRecipientIds, $includeOrders)
    {
        $recipientsIdsWithoutPrefix = $this->getCustomersAndSubscribersIdsFormatted($batchRecipientIds);
        $subscribers = $this->getFormattedSubscribersForDestination($recipientsIdsWithoutPrefix['subscribers']);
        $customers = $this->getFormattedCustomersForDestination(
            $recipientsIdsWithoutPrefix['customers'],
            $includeOrders
        );

        return array_merge($subscribers, $customers);
    }

    /**
     * @return Recipient[]
     */
    public function getAllRecipientsIds()
    {
        $customerIds = $this->customerFactory->create()->getAllIds();
        $subscriberIds = $this->subscriberFactory->create()->addFieldToFilter('customer_id', 0)->getAllIds();

        return array_merge(
            $this->addPrefixToRecipientsIds($customerIds, self::CUSTOMER_ID_PREFIX),
            $this->addPrefixToRecipientsIds($subscriberIds, self::SUBSCRIBER_ID_PREFIX)
        );
    }

    /**
     * @param array $recipientsIds
     * @param string $prefix
     *
     * @return array
     */
    public function addPrefixToRecipientsIds($recipientsIds, $prefix)
    {
        $formattedRecipientsIds = [];

        foreach ($recipientsIds as $id) {
            $formattedRecipientsIds[] = $prefix . $id;
        }

        return $formattedRecipientsIds;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param $groupId
     * @return array
     */
    public function getCustomersByGroupId($customerFactory, $groupId)
    {
        $customerIds = [];
        $customersForSync = $customerFactory->create()
            ->addFieldToFilter('group_id', $groupId)->getData();

        /** @var \Magento\Customer\Model\Customer $customer */
        foreach ($customersForSync as $customer) {
            $customerIds[] = $customer['entity_id'];
        }

        return $customerIds;
    }

    /**
     * @param array $ids
     * @param string $prefix
     *
     * @return array
     */
    public function formatRecipientsIdsForSync($ids, $prefix)
    {
        $formattedIds = [];
        foreach ($ids as $id) {
            $formattedIds[] = $prefix . $id;
        }

        return $formattedIds;
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param $websiteId
     * @return array
     */
    public function getCustomersByWebsiteId($customerFactory, $websiteId)
    {
        $customerIds = [];
        $customersForSync = $customerFactory->create()
            ->addFieldToFilter('website_id', $websiteId)->getData();

        /** @var \Magento\Customer\Model\Customer $customer */
        foreach ($customersForSync as $customer) {
            $customerIds[] = $customer['entity_id'];
        }

        return $customerIds;
    }

    /**
     * Informs service about completed synchronization of provided recipients (IDs).
     *
     * @param array $recipientIds
     */
    public function recipientSyncCompleted(array $recipientIds)
    {
        // Intentionally left empty. We do not need this functionality
    }

    /**
     * Removes prefix from ids and add recipient to corresponding array depending on prefix
     *
     * @param array $batchRecipientIds
     * @return array
     */
    private function getCustomersAndSubscribersIdsFormatted($batchRecipientIds)
    {
        $formattedIds = [
            'customers' => [],
            'subscribers' => [],
        ];

        foreach ($batchRecipientIds as $recipientId) {
            $firstTwoSigns = substr($recipientId, 0, 2);
            $recipientIdWithoutPrefix = (int)substr($recipientId, 2, strlen($recipientId));

            if (strtolower($firstTwoSigns) === strtolower(self::CUSTOMER_ID_PREFIX) &&
                !in_array($recipientIdWithoutPrefix, $formattedIds['customers'])
            ) {
                $formattedIds['customers'][] = (int)substr($recipientId, 2, strlen($recipientId));
            }

            if (strtolower($firstTwoSigns) === strtolower(self::SUBSCRIBER_ID_PREFIX) &&
                !in_array($recipientIdWithoutPrefix, $formattedIds['subscribers'])
            ) {
                $formattedIds['subscribers'][] = (int)substr($recipientId, 2, strlen($recipientId));
            }
        }

        return $formattedIds;
    }

    /**
     * Returns array of Recipient entities
     *
     * @param array $batchRecipientIds
     * @return Recipient[]
     */
    private function getFormattedSubscribersForDestination($batchRecipientIds)
    {
        if (empty($batchRecipientIds)) {
            return [];
        }

        $formattedSubscribers = [];
        $sourceSubscribers = $this->subscriberFactory->create()
            ->addFieldToFilter(
                'subscriber_id',
                $batchRecipientIds
            )->addFieldToFilter('customer_id', 0);

        /** @var \Magento\Newsletter\Model\Subscriber $sourceSubscriber */
        foreach ($sourceSubscribers as $sourceSubscriber) {
            $formattedSubscriber = new Recipient($sourceSubscriber->getEmail());

            $date = date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s'));

            if (!empty($date)) {
                $formattedSubscriber->setRegistered($date);
            }

            $formattedSubscriber->setCustomerNumber($sourceSubscriber->getId());
            $formattedSubscriber->setNewsletterSubscription($sourceSubscriber->isSubscribed());
            $formattedSubscriber->setActive($sourceSubscriber->isSubscribed());
            /** @var Store $store */
            $store = $this->storeCollection->getItemById($sourceSubscriber->getStoreId());
            if ($store !== null) {
                $formattedSubscriber->setSource($store->getBaseUrl());
                $formattedSubscriber->setShop('Magento - ' . $store->getName());
                $formattedSubscriber->setTags(new TagCollection([
                    new Tag($store->getWebsite()->getName(), self::WEBSITE_TAG),
                ]));
            }

            $ordersCount = $this->getSubscriberOrdersCount($sourceSubscriber->getEmail());
            $this->setSpecialTags($formattedSubscriber, $ordersCount);

            $formattedSubscribers[] = $formattedSubscriber;
        }

        return $formattedSubscribers;
    }

    /**
     * @param array $batchRecipientIds
     * @param bool $includeOrders
     *
     * @return Recipient[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getFormattedCustomersForDestination($batchRecipientIds, $includeOrders)
    {
        if (empty($batchRecipientIds)) {
            return [];
        }

        $formattedCustomers = [];
        $sourceCustomers = $this->customerFactory->create()->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', $batchRecipientIds);

        /** @var \Magento\Customer\Model\Customer $sourceCustomer */
        foreach ($sourceCustomers as $sourceCustomer) {
            $formattedCustomer = new Recipient($sourceCustomer->getEmail());
            $formattedCustomer->setCustomerNumber($sourceCustomer->getEntityId());

            $date = new \DateTime();
            $date->setTimestamp($sourceCustomer->getCreatedAtTimestamp());

            if ($date !== null) {
                $formattedCustomer->setActivated($date);
                $formattedCustomer->setRegistered($date);
            }

            $sourceCustomer->getName();
            $formattedCustomer->setFirstName($sourceCustomer->getFirstname());
            $formattedCustomer->setLastName($sourceCustomer->getLastname());
            $formattedCustomer->setNewsletterSubscription($this->isSubscriber($sourceCustomer->getId()));
            $formattedCustomer->setSource($sourceCustomer->getStore()->getBaseUrl());
            $storeName = $sourceCustomer->getStore()->getName();
            $formattedCustomer->setShop('Magento - ' . $storeName);
            $isCustomerActive = (int)$sourceCustomer->getIsActive() === 1
                && $this->isSubscriber($sourceCustomer->getId());
            $formattedCustomer->setActive($isCustomerActive);

            if ($sourceCustomer->getDob() !== null) {
                $birthday = \DateTime::createFromFormat('Y-m-d', $sourceCustomer->getDob());

                if (!empty($birthday)) {
                    $formattedCustomer->setBirthday($birthday);
                }
            }

            $formattedCustomer->setSalutation($sourceCustomer->getPrefix());

            /** @var \Magento\Customer\Model\Address $address */
            $address = $sourceCustomer->getDefaultBillingAddress();

            if ($address) {
                $formattedCustomer->setZip($address->getPostcode());
                $formattedCustomer->setPhone($address->getTelephone());
                $formattedCustomer->setCity($address->getCity());
                $formattedCustomer->setCountry($address->getCountryModel()->getName());
                $formattedCustomer->setState($address->getRegion());
                $formattedCustomer->setCompany($address->getCompany());
                $formattedCustomer->setStreet(implode(', ', $address->getStreet()));
            }
            $customerGroupId = $sourceCustomer->getGroupId();
            $websiteId = $sourceCustomer->getWebsiteId();
            $formattedCustomer->setTags($this->setCustomerTags($customerGroupId, $websiteId));

            $ordersCount = $this->getCustomerOrdersCount($sourceCustomer->getId());
            if ($includeOrders && $ordersCount > 0) {
                $formattedCustomer->setOrders($this->getOrderItemsByCustomerId($sourceCustomer->getId(), $storeName));
            }

            $this->setSpecialTags($formattedCustomer, $ordersCount, true);

            $formattedCustomers[] = $formattedCustomer;
        }

        return $formattedCustomers;
    }

    /**
     * Check if customer is subscribed
     *
     * @param int $id
     * @return bool
     */
    private function isSubscriber($id)
    {
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber**/
        $subscriber = $this->subscriberFactory->create()->addFieldToFilter('customer_id', $id)->getFirstItem();

        return $subscriber->isSubscribed();
    }

    /**
     * @param string $customerGroup
     * @param string $website
     *
     * @return TagCollection
     */
    private function setCustomerTags($customerGroup, $website)
    {
        $tags = new TagCollection();
        $groupData = $this->customerGroupFactory->create()
            ->addFieldToFilter('customer_group_id', $customerGroup)->getData();

        if (isset($groupData[0]['customer_group_code'])) {
            $tags->addTag(new Tag($groupData[0]['customer_group_code'], self::CUSTOMER_GROUP_TAG));
        }

        $accountSharing = (int)$this->scopeConfig->getValue('customer/account_share/scope');
        if ($accountSharing === 0) {
            // customers are shared global
            $tags->add($this->getAllWebsites());
        } else {
            //customers are shared per website
            $websiteData = $this->websiteFactory->create()
                ->addFieldToFilter('website_id', $website)->getData();

            if (isset($websiteData[0]['name'])) {
                $tags->addTag(new Tag($websiteData[0]['name'], self::WEBSITE_TAG));
            }
        }

        return $tags;
    }

    /**
     * Returns all order items ordered by specific customer
     *
     * @param int $customerId
     * @param string $storeName
     * @return \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\OrderItem[]
     */
    private function getOrderItemsByCustomerId($customerId, $storeName)
    {
        $orders = $this->orderFactory->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('customer_email')
            ->addAttributeToSelect('base_currency_code')
            ->addAttributeToSelect('created_at')
            ->addFieldToFilter('customer_id', $customerId)
            ->getData();

        $ordersData = [];
        $orderIds = [];
        foreach ($orders as $order) {
            $ordersData[$order['entity_id']] = $order;
            $orderIds[] = $order['entity_id'];
        }

        $orderItems = $this->orderItemFactory->create()
            ->addFieldToFilter('order_id', ['in' => $orderIds]);

        $formattedOrders = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($orderItems as $item) {
            $ordersData[$item['order_id']]['store_name'] = $storeName;
            $itemData = $item->getData();

            $product = $item->getProduct();
            /** @var OrderItemsService $orderItemsService */
            $orderItemsService = ServiceRegister::getService(OrderItems::CLASS_NAME);
            $formattedOrderItem = $orderItemsService->formatOrderItem($itemData, $ordersData[$item['order_id']], $product);

            $formattedOrders[] = $formattedOrderItem;
        }

        return $formattedOrders;
    }

    /**
     * @param int $customerId
     *
     * @return int
     */
    private function getCustomerOrdersCount($customerId)
    {
        return $this->orderFactory->create()->addFieldToFilter('customer_id', $customerId)->count();
    }

    /**
     * @param string $email
     *
     * @return int
     */
    private function getSubscriberOrdersCount($email)
    {
        return $this->orderFactory->create()->addFieldToFilter('customer_email', $email)->count();
    }

    /**
     * Retrieves all websites from system
     *
     * @return TagCollection
     */
    private function getAllWebsites()
    {
        return $this->getTagsFormatted($this->websiteFactory->create());
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $sourceTags
     *
     * @return TagCollection
     */
    private function getTagsFormatted($sourceTags)
    {
        $tagCollection = new TagCollection();
        /** @var \Magento\Store\Model\Website|\Magento\Customer\Model\Group $sourceTag */
        foreach ($sourceTags as $sourceTag) {
            $tagName = $sourceTag instanceof \Magento\Customer\Model\Group
                ? $sourceTag->getCode() : $sourceTag->getName();

            if (!empty($tagName)) {
                $tag = $sourceTag instanceof \Magento\Customer\Model\Group
                    ? new Tag($tagName, self::CUSTOMER_GROUP_TAG)
                    : new Tag($tagName, self::WEBSITE_TAG);
                $tagCollection->addTag($tag);
            }
        }

        return $tagCollection;
    }

    /**
     * @param Recipient $formattedCustomer
     * @param int $ordersCount
     * @param bool $isCustomer
     */
    private function setSpecialTags($formattedCustomer, $ordersCount, $isCustomer = false)
    {
        $specialTags = new SpecialTagCollection();
        if ($formattedCustomer->getNewsletterSubscription()) {
            $specialTags->addTag(SpecialTag::subscriber());
        }

        if ($ordersCount > 0) {
            $specialTags->addTag(SpecialTag::buyer());
        }

        if ($isCustomer || $ordersCount > 0) {
            $specialTags->addTag(SpecialTag::customer());
        }

        $formattedCustomer->setSpecialTags($specialTags);
    }
}
