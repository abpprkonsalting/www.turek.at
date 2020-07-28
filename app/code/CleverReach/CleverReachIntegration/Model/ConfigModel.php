<?php

namespace CleverReach\CleverReachIntegration\Model;

use Magento\Framework\Model\AbstractModel;

class ConfigModel extends AbstractModel
{
    /** @noinspection MagicMethodsValidityInspection */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('CleverReach\CleverReachIntegration\Model\ResourceModel\ConfigModel');
    }
}
