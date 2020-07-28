<?php

namespace CleverReach\CleverReachIntegration\Controller\Webhook;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy as ProxyInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Framework\App\Action\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

class CleverReachWebhooks extends \Magento\Framework\App\Action\Action
{
    const RECIPIENT_SUBSCRIBED = 'receiver.subscribed';
    const RECIPIENT_UNSUBSCRIBED = 'receiver.unsubscribed';

    /**
     * @var ConfigService
     */
    private $configService;
    /**
     * @var Proxy
     */
    private $proxy;
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $subscriberCollection;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollection;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    private $subscriberResource;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * CleverReachWebhooks constructor.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->subscriberFactory = $subscriberFactory;
        $this->subscriberCollection = $subscriberCollection;
        $this->customerCollection = $customerCollection;
        $this->subscriberResource = $subscriberResource;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(200);

        if ($request->getMethod() === 'GET') {
            return $this->handleEndpointVerification($request, $result);
        }

        // Handling post request
        $requestCallToken = $request->getHeader('x-cr-calltoken');
        if ($requestCallToken !== $this->getConfigService()->getCrEventHandlerCallToken()) {
            $result->setHttpResponseCode(401);

            return $result;
        }

        $this->handleSubscriptionEvents($request);

        return $result;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\Raw $result
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    private function handleEndpointVerification($request, $result)
    {
        $plainText = $this->getConfigService()->getCrEventHandlerVerificationToken() . ' ' . $request->get('secret');
        $result->setHeader('Content-Type', 'text/plain')->setContents($plainText);

        return $result;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function handleSubscriptionEvents($request)
    {
        $requestBody = json_decode($request->getContent(), true);
        if (!empty($requestBody['payload'])
            && $requestBody['payload']['group_id'] === $this->getConfigService()->getIntegrationId()
        ) {
            $recipient = $this->getProxy()
                ->getRecipient($requestBody['payload']['group_id'], $requestBody['payload']['pool_id']);

            /** @var Subscriber\Interceptor $item */
            $subscriber = $this->subscriberCollection
                ->create()
                ->addFieldToFilter('subscriber_email', $recipient->getEmail())
                ->fetchItem();

            if (empty($subscriber)) {
                if ($requestBody['event'] === self::RECIPIENT_UNSUBSCRIBED) {
                    return;
                }

                $subscriber = $this->subscriberFactory->create();
                $subscriber->setEmail($recipient->getEmail());
                $subscriber->setStoreId($this->storeManager->getStore()->getId());
            }

            $this->updateSubscriber($requestBody, $subscriber);
        }
    }

    /**
     * @param array $requestBody
     * @param Subscriber $subscriber
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function updateSubscriber($requestBody, $subscriber)
    {
        $status = $requestBody['event'] === self::RECIPIENT_SUBSCRIBED ? 1 : 3;
        $subscriber->setSubscriberStatus($status);
        $subscriber->setStatus($status);
        if (empty($subscriber->getCustomerId())) {
            $this->setCustomerId($subscriber);
        }

        $this->subscriberResource->save($subscriber);
    }

    /**
     * @param Subscriber $subscriber
     */
    private function setCustomerId($subscriber)
    {
        $customer = $this->customerCollection
            ->create()
            ->addFieldToFilter('email', $subscriber->getEmail())
            ->fetchItem();

        if (!empty($customer)) {
            $subscriber->setCustomerId($customer->getId());
        }
    }

    /**
     * @return ConfigService
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * @return Proxy
     */
    private function getProxy()
    {
        if ($this->proxy === null) {
            $this->proxy = ServiceRegister::getService(ProxyInterface::CLASS_NAME);
        }

        return $this->proxy;
    }
}
