<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Controller\Auth;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RefreshUserInfoTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\BadAuthInfoException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration as ConfigInterface;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use Magento\Framework\App\ResponseInterface;
use CleverReach\CleverReachIntegration\Helper\Url;

class Callback extends \Magento\Framework\App\Action\Action
{

    private $resultJsonFactory;

    private $resultPageFactory;

    /**
     * @var Url
     */
    private $urlHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Url $urlHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlHelper = $urlHelper;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws HttpCommunicationException
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');

        if (empty($code)) {
            return $this->resultJsonFactory->create()->setData([
                'status' => false,
                'message' => __('Wrong parameters. Code not set.'),
            ]);
        }
        
        $redirectUrl = $this->urlHelper->getFrontUrl('cleverreach/auth/callback/');
        /** @var \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
        try {
            $result = $proxy->getAuthInfo($code, $redirectUrl);
        } catch (BadAuthInfoException $e) {
            return $this->resultJsonFactory->create()->setData([
                'status' => false,
                'message' => $e->getMessage() ? $e->getMessage() : __('Unsuccessful connection.'),
            ]);
        }

        $queue = ServiceRegister::getService(Queue::CLASS_NAME);
        $configService = ServiceRegister::getService(ConfigInterface::CLASS_NAME);
        $queue->enqueue($configService->getQueueName(), new RefreshUserInfoTask($result));

        return $this->resultPageFactory->create();
    }
}
