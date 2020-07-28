<?php
namespace Etron\Gateway\Model\ResourceModel\Queue;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Etron\Gateway\Model\Queue','Etron\Gateway\Model\ResourceModel\Queue');
    }
}
