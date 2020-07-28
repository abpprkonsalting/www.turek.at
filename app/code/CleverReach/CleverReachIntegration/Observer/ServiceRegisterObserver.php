<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for service register
 *
 * Class ServiceRegisterObserver
 * @package CleverReach\CleverReachIntegration\Observer
 */
class ServiceRegisterObserver implements ObserverInterface
{
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;

    /**
     * ProductViewObserver constructor.
     * @param \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
     */
    public function __construct(
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
    ) {
        $this->initializer = $initializer;
    }

    /**
     * Register all needed services on highest event
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();
    }
}
