<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('CleverReach\CleverReachIntegration\Model\QueueItem', 'CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem');
    }
}
