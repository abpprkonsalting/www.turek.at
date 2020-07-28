<?php
namespace Etron\Gateway\Api\Data;
interface QueueInterface 
{
	const ENTITY_ID = 'entity_id';
	const ORDER_ID = 'order_id';
	const STATUS = 'status';
	const MESSAGE = 'message';

	const SEND_STATUS_READY = 0;
	const SEND_STATUS_SENDING = 1;
	const SEND_STATUS_SUCCESSFUL = 2;
	const SEND_STATUS_ERROR = -1;

	function getId();
	function setId($id);

	function getOrderId();
	function setOrderId($orderId);

	function getStatus();
	function setStatus($status);

	function getMessage();
	function setMessage($message);


}