<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Controller\Adminhtml\Content;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\InitialSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Exposed\TaskRunnerWakeup;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use Magento\Backend\App\Action;

class Routing extends Action
{
    const AUTO_CONFIGURE_STATE_CODE = 'autoconfigure';
    const WELCOME_STATE_CODE = 'welcome';
    const INITIAL_SYNC_STATE_CODE = 'initialsync';
    const DASHBOARD_STATE_CODE = 'dashboard';
    const REFRESH_STATE_CODE = 'refresh';

    const ADMIN_RESOURCE = 'CleverReach_CleverReachIntegration::cleverreach';

    /** @var Configuration */
    private $configService;

    /** @var Queue */
    private $queue;

    /** @var TaskRunnerWakeup */
    private $taskRunnerWakeupService;

    /** @var \Magento\Framework\App\Request\Http */
    private $request;

    /** @var \Magento\Framework\View\Result\PageFactory */
    private $resultPageFactory;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Returns current state of the import process
     *
     * @return mixed
     */
    public function execute()
    {
        $this->getTaskRunnerWakeupService()->wakeup();

        $this->redirectHandler();

        $this->_view->loadLayout();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('CleverReach');
        $resultPage->setActiveMenu('logeecom::base');

        return $resultPage;
    }
    
    private function redirectHandler()
    {
        if (!$this->isAuthTokenValid()) {
            if (!$this->isAutoConfigurePassed()) {
                $this->redirectIfNecessary(self::AUTO_CONFIGURE_STATE_CODE, 'cleverreach/content/autoconfigure');
                return;
            }

            $this->redirectIfNecessary(self::WELCOME_STATE_CODE, 'cleverreach/content/welcome');
            return;
        }

        if ($this->isInitialSyncInProgress()) {
            $this->redirectIfNecessary(self::INITIAL_SYNC_STATE_CODE, 'cleverreach/content/initialsync');
            return;
        }

        if (!$this->isRefreshTokenValid()) {
            $this->redirectIfNecessary(self::REFRESH_STATE_CODE, 'cleverreach/content/refresh');
            return;
        }

        $this->redirectIfNecessary(self::DASHBOARD_STATE_CODE, 'cleverreach/content/dashboard');
    }

    private function redirectIfNecessary($currentAction, $redirectUrl)
    {
        $actionName = $this->request->getActionName();

        if ($actionName !== $currentAction) {
            return $this->_redirect($redirectUrl);
        }
    }

    private function isAutoConfigurePassed()
    {
        return $this->getConfigService()->getAutoConfigureState() === 'success';
    }

    private function isAuthTokenValid()
    {
        $accessToken = $this->getConfigService()->getAccessToken();

        return $accessToken !== null && $accessToken !== '';
    }

    private function isRefreshTokenValid()
    {
        $refreshToken = $this->getConfigService()->getRefreshToken();

        return !empty($refreshToken);
    }

    private function isInitialSyncInProgress()
    {
        /** @var QueueItem $initialSyncTaskItem */
        $initialSyncTaskItem = $this->getQueueService()->findLatestByType('InitialSyncTask');
        if (!$initialSyncTaskItem) {
            try {
                $this->getQueueService()->enqueue($this->getConfigService()->getQueueName(), new InitialSyncTask());
            } catch (QueueStorageUnavailableException $e) {
                // If task enqueue fails do nothing but report that initial sync is in progress
            }

            return true;
        }

        return $initialSyncTaskItem->getStatus() !== QueueItem::COMPLETED
            && $initialSyncTaskItem->getStatus() !== QueueItem::FAILED;
    }

    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    private function getQueueService()
    {
        if ($this->queue === null) {
            $this->queue = ServiceRegister::getService(Queue::CLASS_NAME);
        }

        return $this->queue;
    }

    private function getTaskRunnerWakeupService()
    {
        if ($this->taskRunnerWakeupService === null) {
            $this->taskRunnerWakeupService = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskRunnerWakeupService;
    }
}
