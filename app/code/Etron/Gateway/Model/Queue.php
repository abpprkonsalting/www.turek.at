<?php
namespace Etron\Gateway\Model;
class Queue extends \Magento\Framework\Model\AbstractModel implements \Etron\Gateway\Api\Data\QueueInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'etron_gateway_queue';

    protected function _construct()
    {
        $this->_init('Etron\Gateway\Model\ResourceModel\Queue');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

	function getOrderId() {
		return $this->getData(self::ORDER_ID);
	}

	function setOrderId( $orderId ) {
		$this->setData(self::ORDER_ID, $orderId);
		return $this;
	}

	function getStatus() {
		return $this->getData(self::STATUS);
	}

	function setStatus( $status ) {
		$this->setData(self::STATUS, $status);
		return $this;
	}

	function getMessage() {
		return $this->getData(self::MESSAGE);
	}

	function setMessage( $message ) {
		$this->setData(self::MESSAGE, $message);
		return $this;
	}
}
