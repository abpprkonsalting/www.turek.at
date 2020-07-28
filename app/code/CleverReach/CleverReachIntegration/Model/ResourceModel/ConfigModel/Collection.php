<?php

namespace CleverReach\CleverReachIntegration\Model\ResourceModel\ConfigModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'CleverReach\CleverReachIntegration\Model\ConfigModel',
            'CleverReach\CleverReachIntegration\Model\ResourceModel\ConfigModel'
        );
    }
}
