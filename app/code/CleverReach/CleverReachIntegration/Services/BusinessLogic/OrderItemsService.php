<?php

namespace CleverReach\CleverReachIntegration\Services\BusinessLogic;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\OrderItems;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\OrderItem;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;

class OrderItemsService implements OrderItems
{
    /** @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory  */
    private $orderItemRepository;

    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory  */
    private $orderRepository;

    /** @var  \Magento\Catalog\Model\CategoryFactory */
    private $categoryFactory;

    /** @var  \Magento\Store\Model\StoreFactory */
    private $storeFactory;

    /**
     * OrderItemsService constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderRepository
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreFactory $storeFactory
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->categoryFactory = $categoryFactory;
        $this->storeFactory = $storeFactory;
    }

    /**
     * @param $orderItemsIds
     * @return OrderItem[]
     */
    public function getOrderItems($orderItemsIds)
    {
        $orderItems = $this->orderItemRepository->create()
            ->addFieldToFilter('item_id', ['in' => $orderItemsIds]);

        $orderItemsData = [];
        $orderIds = [];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $orderId = $orderItem->getOrderId();
            $orderItemsData[$orderId][] = $orderItem;
            $orderIds[] = $orderId;
        }

        $orders = $this->orderRepository->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('customer_email')
            ->addAttributeToSelect('base_currency_code')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('store_id')
            ->addFieldToFilter('entity_id', $orderIds)
            ->getData();

        $formattedOrderItems = [];

        foreach ($orders as $order) {
            if (empty($order['customer_email'])) {
                continue;
            }

            $order['store_name'] = $this->storeFactory->create()->load($order['store_id'])->getName();

            $orderItemData = $orderItemsData[$order['entity_id']];
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($orderItemData as $item) {
                $productModel = $item->getProduct();
                $formattedOrderItems[] = $this->formatOrderItem($item->getData(), $order, $productModel);
            }
        }

        return $formattedOrderItems;
    }

    /**
     * @param array $orderItem
     * @param array $order
     * @param \Magento\Catalog\Model\Product $productModel
     * @return OrderItem
     */
    public function formatOrderItem($orderItem, $order, $productModel)
    {
        $formattedOrderItem = new OrderItem($orderItem['item_id'], $orderItem['name']);
        $formattedOrderItem->setRecipientEmail($order['customer_email']);

        if (!empty($orderItem['sku'])) {
            $formattedOrderItem->setProductId($orderItem['sku']);
        }

        if (!empty($order['base_currency_code'])) {
            $formattedOrderItem->setCurrency($order['base_currency_code']);
        }

        if (!empty($orderItem['qty_ordered'])) {
            $pos = strpos($orderItem['qty_ordered'], '.');
            $formattedOrderItem->setAmount((int)substr($orderItem['qty_ordered'], 0, $pos));
        }

        if (!empty($orderItem['price'])) {
            $formattedOrderItem->setPrice($orderItem['price']);
        }

        if (!empty($orderItem['product_options']['attributes_info'])) {
            $formattedOrderItem->setAttributes($this->formatOrderItemAttributes($orderItem['product_options']['attributes_info']));
        }

        if (isset($productModel)) {
            if (!empty($productModel->getCategoryIds())) {
                $formattedOrderItem->setProductCategory($this->formatOrderItemCategories($productModel->getCategoryIds()));
            }

            if (!empty($productModel->getCustomAttribute('manufacturer'))) {
                $formattedOrderItem->setBrand($productModel->getAttributeText('manufacturer'));
            }
        }

        if (!empty($order['created_at'])) {
            $date = date_create_from_format('Y-m-d H:i:s', $order['created_at']);

            if (!empty($date)) {
                $formattedOrderItem->setStamp($date);
            }
        }

        if (!empty($order['store_name'])) {
            $systemName = ServiceRegister::getService(Configuration::CLASS_NAME)->getIntegrationName();
            $formattedOrderItem->setProductSource($systemName . ' - ' . $order['store_name']);
        }

        return $formattedOrderItem;
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function formatOrderItemAttributes($attributes)
    {
        $formattedAttributes = [];

        foreach ($attributes as $attribute) {
            $formattedAttributes[$attribute['label']] = $attribute['value'];
        }

        return $formattedAttributes;
    }

    /**
     * @param array $ids
     * @return array
     */
    private function formatOrderItemCategories($ids)
    {
        $formattedOrderItemCategories = [];

        foreach ($ids as $id) {
            /** @var \Magento\Catalog\Model\Category $categoryModel */
            $categoryName = $this->categoryFactory->create()->load($id)->getName();
            $formattedOrderItemCategories[] = $categoryName;
        }

        return $formattedOrderItemCategories;
    }
}
