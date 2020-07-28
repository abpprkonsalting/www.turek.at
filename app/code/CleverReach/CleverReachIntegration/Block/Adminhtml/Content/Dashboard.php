<?php

namespace CleverReach\CleverReachIntegration\Block\Adminhtml\Content;

use CleverReach\BusinessLogic\Entity\Tag;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use CleverReach\CleverReachIntegration\Services\BusinessLogic\RecipientService;
use Magento\Backend\Block\Template;

class Dashboard extends Template
{
    const CLEVERREACH_BUILD_EMAIL_URL = '/admin/login.php?ref=%2Fadmin%2Fmailing_create_new.php';
    const CLEVERREACH_GDPR_URL = 'https://www.cleverreach.com/en/features/privacy-security/eu-general-data-protection-regulation-gdpr/';

    /**
     * @var ConfigService
     */
    private $configService;

    /** @var  Queue */
    private $queueService;

    /** @var \Magento\Backend\Model\UrlInterface */
    private $backendUrl;

    public function __construct(
        Template\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    /**
     * Sets up values for dashboard screen.
     *
     * @return array
     */
    public function getDashboardConfig()
    {
        $userInfo = $this->getConfigService()->getUserInfo();
        $failureParameters = $this->getInitialSyncFailureParameters();
        $importStatisticsDisplayed = $this->getConfigService()->isImportStatisticsDisplayed();
        $segments = [];
        if (!$importStatisticsDisplayed) {
            /** @var RecipientService $recipientService */
            $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);
            $tags = $recipientService->getAllTags()->toArray();
            $segments = array_map(
                function ($tag) {
                    /** @var Tag $tag */
                    return $tag->getTitle();
                },
                $tags
            );

            $this->getConfigService()->setImportStatisticsDisplayed(true);
        }

        return [
            'buildFirstEmailUrl' => $this->backendUrl->getUrl('cleverreach/dashboard/buildfirstemail'),
            'retrySyncUrl' => $this->backendUrl->getUrl('cleverreach/dashboard/retrysync'),
            'recipientId' => $userInfo['id'],
            'helpUrl' => 'https://support.cleverreach.de/hc/en-us/requests/new',
            'buildEmailUrl' => 'https://' . $userInfo['login_domain'] .
                self::CLEVERREACH_BUILD_EMAIL_URL,
            'isFirstEmailBuild' => $this->getConfigService()->isFirstEmailBuilt(),
            'integrationName' => $this->getConfigService()->getIntegrationName(),
            'isInitialSyncTaskFailed' => $failureParameters['isFailed'],
            'initialSyncTaskFailureMessage' => $failureParameters['description'],
            'importStatisticsDisplayed' => $importStatisticsDisplayed,
            'importClass' => !$importStatisticsDisplayed ? 'cr-has-import' : '',
            'segments' => $segments,
            'numberOfSyncedRecipients' => !$importStatisticsDisplayed ?
                $this->getFormattedNumberOfSyncedRecipients() : '0',
            'gdprUrl' => self::CLEVERREACH_GDPR_URL,
        ];
    }

    /**
     * @return ConfigService
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * @return Queue
     */
    private function getQueueService()
    {
        if ($this->queueService === null) {
            $this->queueService = ServiceRegister::getService(Queue::CLASS_NAME);
        }

        return $this->queueService;
    }

    /**
     * @return array
     */
    private function getInitialSyncFailureParameters()
    {
        $params = ['isFailed' => false, 'description' => ''];
        /** @var QueueItem $initialSyncTask */
        $initialSyncTask = $this->getQueueService()->findLatestByType('InitialSyncTask');
        if ($initialSyncTask && $initialSyncTask->getStatus() === QueueItem::FAILED) {
            $params = [
                'isFailed' => true,
                'description' => $initialSyncTask->getFailureDescription()
            ];
        }

        return $params;
    }

    /**
     * Retrieves locally formatted number of synced recipients.
     *
     * @return string
     */
    private function getFormattedNumberOfSyncedRecipients()
    {
        $numberOfRecipients = $this->getConfigService()->getNumberOfSyncedRecipients();
        if (empty($numberOfRecipients)) {
            return '0';
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Backend\Model\Auth\Session $session */
        $session = $om->get('Magento\Backend\Model\Auth\Session');
        $locale = $session->getUser()->getInterfaceLocale();
        
        if ($locale === null || $locale === '') {
            $locale = 'en_US';
        }
        
        if (strpos($locale, 'en') === 0) {
            $thousandSep = ',';
            $decimalSep = '.';
        } else {
            $thousandSep = '.';
            $decimalSep = ',';
        }

        return number_format((float) $numberOfRecipients, 0, $decimalSep, $thousandSep);
    }
}
