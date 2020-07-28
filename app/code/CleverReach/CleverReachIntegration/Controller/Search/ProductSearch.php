<?php

namespace CleverReach\CleverReachIntegration\Controller\Search;

use Magento\Framework\Data\Collection;

class ProductSearch extends \Magento\Framework\App\Action\Action
{

    const NO_PRODUCT = 8;

    /** @var  \Magento\Catalog\Model\Product */
    private $productFactory;

    /** @var  \Magento\Catalog\Helper\Product\ */
    private $catalogProductHelper;

    private $resultJsonFactory;

    /** @var \Magento\Framework\Pricing\Helper\Data  */
    private $priceHelper;

    /** @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory  */
    private $storeFactory;

    /**
     * ProductSearch constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->priceHelper = $priceHelper;
        $this->storeFactory = $storeFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $param = $this->getRequest()->getParam('get');

        if ($param === 'search') {
            return $this->getSearchProducts();
        }

        return $this->getFilterSelect();
    }

    private function getFilterSelect()
    {
        $filterInput = [
            'name' => 'Product',
            'description' => 'Product SKU or ID',
            'required' => true,
            'query_key' => 'sku',
            'type' => 'input',
        ];
        $filterSelect = [
            'name' => 'Store View',
            'description' => '',
            'required' => true,
            'query_key' => 'store_id',
            'type' => 'dropdown'
        ];

        $stores = $this->storeFactory->create()->setOrder('website_id', Collection::SORT_ORDER_ASC)->setOrder('group_id')->getItems();

        $filterSelect['values'] = [
            [
                'text' => 'Please select shop',
                'value' => 0,
            ]
        ];

        /** @var \Magento\Store\Model\Store $store */
        foreach ($stores as $store) {
            $website = $store->getWebsite();
            $storeGroup = $store->getGroup();
            $filterSelect['values'][] = [
                'text' => $website->getName().' > '.$storeGroup->getName().' > '.$store->getName(),
                'value' => $store->getId()
            ];
        }

        $filters = [$filterSelect, $filterInput];

        return $this->resultJsonFactory->create()->setData($filters);
    }

    private function getSearchProducts()
    {
        $skuOrId = $this->getRequest()->getParam('sku');
        $storeId = $this->getRequest()->getParam('store_id');

        if (empty($skuOrId) || empty($storeId)) {
            return $this->resultJsonFactory->create()->setData([
                'status' => self::NO_PRODUCT,
                'message' => __('Both parameters required!'),
            ]);
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->getProductCollection($skuOrId, $storeId);

        if ($productCollection->getSize() === 0) {
            return $this->resultJsonFactory->create()->setData([
                'status' => self::NO_PRODUCT,
                'message' => __('There is no product with given parameters'),
            ]);
        }

        $items = $this->initItems();
        /** @var  \Magento\Catalog\Model\Product $product */
        foreach ($productCollection as $product) {
            if ($skuOrId !== $product->getId() && $skuOrId !== $product->getSku()) {
                continue;
            }

            $items['items'][] = [
                'title' => $product->getName(),
                'description' => $product->getDescription(),
                'image' => $this->catalogProductHelper->getImageUrl($product),
                'price' => $this->priceHelper->currency($product->getFinalPrice(), true, false),
                'url' => $product->setStoreId($storeId)->getUrlModel()->getUrlInStore($product, ['_escape' => true])
            ];
        }
        $items = (object)$items;

        return $this->resultJsonFactory->create()->setData($items);
    }

    /**
     * @param $skuOrId
     * @param $storeId
     * @return mixed
     */
    private function getProductCollection($skuOrId, $storeId)
    {
        return $this->productFactory->create()
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter([
                [
                    'attribute' => 'sku',
                    'eq' => $skuOrId
                ],
                [
                    'attribute' => 'entity_id',
                    'eq' => $skuOrId
                ]
            ]);
    }

    /**
     * @return array
     */
    private function initItems()
    {
        return [
            'settings' => [
                'type' => 'product',
                'link_editable' => false,
                'link_text_editable' => false,
                'image_size_editable' => false,
            ],
            'items' => [],
        ];
    }
}
