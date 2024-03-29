<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 01.08.18
 * Time: 23:14
 */

namespace Etron\Gateway\Model\Config\Source;


class AllPaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
	/**
	 * Payment data
	 *
	 * @var \Magento\Payment\Helper\Data
	 */
	protected $_paymentData;

	/**
	 * @param \Magento\Payment\Helper\Data $paymentData
	 */
	public function __construct(\Magento\Payment\Helper\Data $paymentData)
	{
		$this->_paymentData = $paymentData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toOptionArray()
	{
		return $this->_paymentData->getPaymentMethodList(false, true, false);
	}
}
