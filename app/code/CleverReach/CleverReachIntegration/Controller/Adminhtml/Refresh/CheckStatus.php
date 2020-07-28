<?php

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\Refresh;

use Magento\Backend\App\Action;

class CheckStatus extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * CheckStatus Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultJsonFactory->create()->setData(['status' => 'finished']);
    }
}
