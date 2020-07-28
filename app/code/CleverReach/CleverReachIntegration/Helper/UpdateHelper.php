<?php

namespace CleverReach\CleverReachIntegration\Helper;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Store\Model\Website;

class UpdateHelper
{
    const CUSTOMER_GROUP_PREFIX = 'G-';
    const WEBSITE_PREFIX = 'S-';

    /**
     * @var GroupCollectionFactory $customerGroupFactory
     */
    private $customerGroupFactory;
    /**
     * @var WebsiteCollectionFactory $websiteFactory
     */
    private $websiteFactory;

    /**
     * UpdateHelper constructor.
     *
     * @param GroupCollectionFactory $customerGroupFactory
     * @param WebsiteCollectionFactory $websiteFactory
     */
    public function __construct(
        GroupCollectionFactory $customerGroupFactory,
        WebsiteCollectionFactory $websiteFactory
    ) {
        $this->customerGroupFactory = $customerGroupFactory;
        $this->websiteFactory = $websiteFactory;
    }

    /**
     * @return array in format ['MG-G-tagName1', 'MG-S-website1', ...]
     */
    public function getTagsInOldFormat()
    {
        $customerGroups = $this->customerGroupFactory->create();
        $websites = $this->websiteFactory->create();

        return array_merge(
            $this->formatTags($customerGroups),
            $this->formatTags($websites)
        );
    }

    /**
     * Add prefix to group/website name
     *
     * @param AbstractCollection $tags
     * @return array
     */
    private function formatTags($tags)
    {
        $formattedTags = [];

        /** @var Group | Website $tag */
        foreach ($tags as $tag) {
            $tagName = $tag instanceof Group ?
                self::CUSTOMER_GROUP_PREFIX . $tag->getCode() : self::WEBSITE_PREFIX . $tag->getName();
            $formattedTags[] = 'MG-' . str_replace(' ', '_', $tagName);
        }

        return $formattedTags;
    }
}
