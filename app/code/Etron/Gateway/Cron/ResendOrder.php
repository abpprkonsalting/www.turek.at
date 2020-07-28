<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 23.07.18
 * Time: 00:16
 */

namespace Etron\Gateway\Cron;

use \Etron\Gateway\Model\Queue;

class ResendOrder {
	/** @var \Etron\Gateway\Logger\Logger  */
	protected $logger;
	/** @var \Etron\Gateway\Model\ResourceModel\Queue\CollectionFactory  */
	protected $collectionFactory;
	/** @var \Magento\Sales\Api\OrderRepositoryInterface  */
	protected $orderRepository;
	/** @var \Etron\Gateway\Helper\Data  */
	protected $helper;

	public function __construct
	(
		\Etron\Gateway\Logger\Logger $logger,
		\Etron\Gateway\Model\ResourceModel\Queue\CollectionFactory $collectionFactory,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\Etron\Gateway\Helper\Data $helper
	)
	{
		$this->logger = $logger;
		$this->collectionFactory = $collectionFactory;
		$this->orderRepository = $orderRepository;
		$this->helper = $helper;
	}

	public function execute() {
		$this->logger->info('Resend Order Cron Job Running');
		$collection = $this->collectionFactory->create()->addFieldToFilter(Queue::STATUS, ['neq' => Queue::SEND_STATUS_SUCCESSFUL]);
		/** @var Queue $queueItem */
		foreach($collection->getItems() as $queueItem) {
			$orderId = $queueItem->getOrderId();
			$order = $this->orderRepository->get($orderId);

			if (!$this->helper->isEnabled($order)) {
				$this->logger->info('Gateway is disabled for store.');
				continue;
			}

			if (!$this->helper->canAutomaticResend($order)) {
				$this->logger->info('Order #'.$order->getIncrementId().' cannot be automatically sent.');
				continue;
			}

			if ($order->getStatus() == 'complete'
			    || $order->getStatus() == 'pending_payment'
			    || $order->getStatus() == 'canceled')
			{
				$this->logger->info('Order #'.$order->getIncrementId().' has wrong status: '.$order->getStatus());
				continue;
			}

			$this->logger->info('Resending order #'.$order->getIncrementId().' to gateway');
			$this->helper->sendOrderJSON($order);
		}
		return $this;
	}


}