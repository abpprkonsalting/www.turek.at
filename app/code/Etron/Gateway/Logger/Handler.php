<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 23.07.18
 * Time: 09:01
 */

namespace Etron\Gateway\Logger;


class Handler extends \Magento\Framework\Logger\Handler\Base {
	/**
	 * Logging level
	 * @var int
	 */
	protected $loggerType = Logger::DEBUG;

	/**
	 * File name
	 * @var string
	 */
	protected $fileName = '/var/log/etron_gateway.log';
}