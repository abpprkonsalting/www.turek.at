<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 23.04.18
 * Time: 12:54
 */

namespace Etron\Gateway\Plugin;


use Etron\Gateway\Api\QueueRepositoryInterface;
use Etron\Gateway\Logger\Logger;
use Etron\Gateway\Model\Queue;
use Etron\Gateway\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

class SendStatusPlugin {

	/** @var \Magento\Sales\Model\OrderRepository  */
	protected $orderRepository;

	/** @var Logger  */
	protected $logger;

	/** @var QueueCollectionFactory  */
	protected $collectionFactory;

	protected $queueRepository;

	public function __construct(
		\Magento\Sales\Model\OrderRepository $orderRepository,
		QueueCollectionFactory $collectionFactory,
		QueueRepositoryInterface $queueRepository,
		Logger $logger
	)
	{
		$this->orderRepository = $orderRepository;
		$this->collectionFactory = $collectionFactory;
		$this->queueRepository = $queueRepository;
		$this->logger = $logger;
	}

	/**
	 * @param \Etron\Gateway\Helper\Data $subject
	 * @param callable $proceed
	 * @param \Magento\Sales\Api\Data\OrderInterface $order
	 */
	public function aroundSendOrderJSON(\Etron\Gateway\Helper\Data $subject, callable $proceed, $order) {
		$orderData = $this->orderRepository->get($order->getId());

		// find QueueItem
		/** @var Queue $queueItem */
		$queueItem = $this->collectionFactory->create()
		                                     ->addFieldToFilter(Queue::ORDER_ID, ['eq'=>$order->getId()])
		                                     ->getFirstItem();

		if ($status = $order->getData('gw_send_status')) {
			// recover log
			$log = $order->getData('gw_send_log');
			try {
				$log = json_decode($log, TRUE);
				if ($log == null) $log = [];
			} catch (\Exception $ex) {
				// ignore, as we reset log
				$log = [];
			}
		} else {
			$log = [];
		}
		$log[]=['status'=>'Sending', 'timestamp'=>date('d.m.Y H:i:s')];
		$orderData->setData('gw_send_status', Queue::SEND_STATUS_SENDING);
		$orderData->setData('gw_send_log', json_encode($log));
		$this->orderRepository->save($orderData);

		$queueItem->setStatus(Queue::SEND_STATUS_SENDING);
		$this->queueRepository->save($queueItem);

		$result = ['code'=>'Error', 'response'=>'Unexpected error'];

		try {
			$result = $proceed($order);
			if (is_array($result)) {
				if ($result['code'] == 200) {
					$orderData->setData('gw_send_status', Queue::SEND_STATUS_SUCCESSFUL);
					$log[]=['status'=>'Successful', 'message'=>$result['code'].': '.$result['response'], 'timestamp'=>date('d.m.Y H:i:s')];
					$orderData->setData('gw_send_log', json_encode($log));
				} else {
					$orderData->setData('gw_send_status', Queue::SEND_STATUS_ERROR);
					$log[]=['status'=>'Failed', 'message'=>$result['code'].': '.$result['response'], 'timestamp'=>date('d.m.Y H:i:s')];
					$orderData->setData('gw_send_log', json_encode($log));
				}

				$queueItem->setStatus( $orderData->getData('gw_send_status'));
				$queueItem->setMessage( $result['code'].': '.$result['response'] );

			} else {
				$orderData->setData('gw_send_status', Queue::SEND_STATUS_ERROR);
				$log[]=['status'=>'Error', 'message'=>'Unexpected error', 'timestamp'=>date('d.m.Y H:i:s')];
				$orderData->setData('gw_send_log', json_encode($log));

				$queueItem->setStatus( Queue::SEND_STATUS_ERROR);
				$queueItem->setMessage('Unexpected error');
			}

		} catch (\Exception $ex) {
			$orderData->setData('gw_send_status', Queue::SEND_STATUS_ERROR);
			$log[]=['status'=>'Error', 'message'=>$ex->getMessage(), 'timestamp'=>date('d.m.Y H:i:s')];
			$orderData->setData('gw_send_log', json_encode($log));

			$queueItem->setStatus( Queue::SEND_STATUS_ERROR);
			$queueItem->setMessage($ex->getMessage());
		}

		try {
			$this->orderRepository->save($orderData);
			$this->queueRepository->save($queueItem);
		} catch (\Exception $ex) {

		}


		return $result;
	}
}