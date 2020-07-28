<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Model;

class QueueItem extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem');
    }
}
