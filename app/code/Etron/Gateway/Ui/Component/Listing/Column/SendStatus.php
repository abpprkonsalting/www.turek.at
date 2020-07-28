<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 23.04.18
 * Time: 13:05
 */

namespace Etron\Gateway\Ui\Component\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;


class SendStatus  extends Column {

	protected $_orderRepository;
	protected $_searchCriteria;

	public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
	{
		$this->_orderRepository = $orderRepository;
		$this->_searchCriteria  = $criteria;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}

	public function prepareDataSource(array $dataSource)
	{

		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {


				if (isset($item['entity_id'])) {
					$status = null;
					$log = null;

					try {
						$order = $this->_orderRepository->get($item["entity_id"]);
						if ($order->getEntityId()) {
							$status = $order->getData("gw_send_status");
							$log = $order->getData("gw_send_log");
							if (!empty($log)) {
								$log_array = json_decode($log, TRUE);
								if ($log_array && is_array($log_array)) {
									$log = array_pop($log_array);
								}
							}
						}
					} catch (NoSuchEntityException $ex) {
						//
					}


					$export_status = "";

					if ($status === null) {
						$export_status = ""; // ignored, order created before modification
					} else if ($status == 0) {
						$export_status = ""; // Pending
					} else if ($status == 1) {
						$export_status = "Sendet..";
					} else if ($status == 2) {
						$export_status = "Erfolgreich";
					} else if ($status < 0) {
						$export_status = "Fehler";
					}

					if (is_array($log)) {
						$export_status.= ' ('.$log['timestamp'].')';
					}

					// $this->getData('name') returns the name of the column so in this case it would return export_status
					$item['gw_send_status'] = $export_status;
				}

			}
		}

		return $dataSource;
	}
}