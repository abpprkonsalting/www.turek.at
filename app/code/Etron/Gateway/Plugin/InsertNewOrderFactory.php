<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 23.07.18
 * Time: 08:37
 */

namespace Etron\Gateway\Plugin;

use Etron\Gateway\Api\QueueRepositoryInterface;
use Etron\Gateway\Model\Queue;
use Etron\Gateway\Model\QueueFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface as SalesOrderRepositoryInterface;
use Etron\Gateway\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;


class InsertNewOrderFactory {

	/** @var QueueCollectionFactory  */
	protected $collectionFactory;

	/** @var QueueFactory  */
	protected $queueFactory;

	/** @var QueueRepository */
	protected $queueRepository;

	/** @var \Etron\Gateway\Logger\Logger  */
	private $logger;

    /**
     * InsertNewOrderPlugin constructor.
     * @param QueueCollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
	    QueueCollectionFactory $collectionFactory,
	    QueueRepositoryInterface $queueRepository,
	    QueueFactory $queueFactory,
	    \Etron\Gateway\Logger\Logger $logger
    ) {
	    $this->collectionFactory = $collectionFactory;
	    $this->queueFactory = $queueFactory;
	    $this->queueRepository = $queueRepository;
	    $this->logger = $logger;
    }

    /**
     * Insert new order into queue
     *
     * Observers don't work:
     * - `sales_order_save_commit_after` is no longer triggered in guest checkout
     * - `sales_order_save_after` does not have related entities (addresses) persisted yet
     *
     * Other promising events like `sales_order_place_after`, `checkout_submit_all_after`,
     * `sales_model_service_quote_submit_success` are
     * - triggered before the order was saved or
     * - not triggered at all in multi address checkout or some payment providers'
     *   custom checkout implementations (paypal express, sagepay, …).
     *
     * @param SalesOrderRepositoryInterface $subject
     * @param OrderInterface|\Magento\Sales\Model\Order $salesOrder
     * @return OrderInterface
     */
    public function afterSave(SalesOrderRepositoryInterface $subject, OrderInterface $salesOrder)
    {
	    $this->logger->info('Plugin Sales Order afterSave for order '.$salesOrder->getId());
    	try {
	        $orderCount = $this->collectionFactory->create()->addFieldToFilter(Queue::ORDER_ID, ['eq' => $salesOrder->getId()])->count();
	        if ($orderCount == 0) {
				$queueEntry = $this->queueFactory->create();
				$queueEntry->setOrderId( $salesOrder->getId() );
				$queueEntry->setStatus ( Queue::SEND_STATUS_READY );
				$this->queueRepository->save($queueEntry);
				$this->logger->info('Added queue entry for order '.$salesOrder->getId());
		    }
	    } catch (\Exception $ex) {
    		$this->logger->error('Error creating queue entry: '.$ex->getMessage());
	    }

	    return $salesOrder;
    }

}