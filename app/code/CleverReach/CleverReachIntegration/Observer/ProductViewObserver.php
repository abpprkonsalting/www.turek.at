<?php

namespace CleverReach\CleverReachIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductViewObserver implements ObserverInterface
{
    /** @var \Magento\Framework\App\RequestInterface  */
    private $request;

    /** @var \Magento\Catalog\Model\Session  */
    private $catalogSession;

    /**
     * @var \CleverReach\CleverReachIntegration\Helper\Initializer
     */
    private $initializer;

    /**
     * ProductViewObserver constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\Session $catalogSession,
        \CleverReach\CleverReachIntegration\Helper\InitializerInterface $initializer
    ) {
        $this->request = $request;
        $this->catalogSession = $catalogSession;
        $this->initializer = $initializer;
    }

    public function execute(Observer $observer)
    {
        $this->initializer->registerServices();

        $params = $this->request->getParams();
        if (!empty($params['crmailing'])) {
            $this->catalogSession->setData('mailingId', $params['crmailing']);
        }
    }
}
