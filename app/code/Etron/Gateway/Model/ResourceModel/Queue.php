<?php
namespace Etron\Gateway\Model\ResourceModel;
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('etron_gateway_queue','entity_id');
    }
}
