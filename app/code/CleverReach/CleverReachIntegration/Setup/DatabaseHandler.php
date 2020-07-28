<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Setup;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\Helper\UpdateHelper;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Recipients;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\ExchangeAccessTokenTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\FilterSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\ProductSearchSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RecipientSyncTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\RegisterEventHandlerTask;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync\UpdateTagsToNewSystemTask;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Queue;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Framework\Setup\SchemaSetupInterface;

class DatabaseHandler
{
    /**
     * @var \Magento\Framework\Setup\SchemaSetupInterface
     */
    private $installer;
    /**
     * @var InitializerInterface $initializer
     */
    private $initializer;
    /**
     * @var UpdateHelper $updateHelper
     */
    private $updateHelper;

    public $configService;

    public function __construct(
        SchemaSetupInterface $installer,
        ConfigService $configService,
        InitializerInterface $initializer,
        UpdateHelper $updateHelper
    ) {
        $this->configService = $configService;
        $this->installer = $installer;
        $this->initializer = $initializer;
        $this->updateHelper = $updateHelper;
    }

    /**
     * Creates plugin database tables
     *
     * @throws \Zend_Db_Exception
     */
    public function createTables()
    {
        $this->createProcessTable();
        $this->createConfigurationTable();
        $this->createQueueTable();
    }

    /**
     * Drops plugin database tables
     */
    public function dropTables()
    {
        $this->dropTable('cleverreach_config');
        $this->dropTable('cleverreach_process');
        $this->dropTable('cleverreach_queue');
    }

    /**
     * Alter plugin database tables
     */
    public function alterTables()
    {
        $queueTableName = $this->installer->getTable('cleverreach_queue');

        if (!$this->installer->getConnection()->tableColumnExists($queueTableName, 'lastExecutionProgress')) {
            $this->installer->getConnection()->addColumn(
                $queueTableName,
                'lastExecutionProgress',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'LastExecutionProgress',
                ]
            );
        }
    }

    /**
     * Queues UpdateTagsToNewSystemTags
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function updateTags()
    {
        $this->initializer->registerServices();
        $accessToken = $this->configService->getAccessToken();
        if ($accessToken === null || $accessToken === '') {
            return;
        }

        $this->enqueueTask(new UpdateTagsToNewSystemTask($this->updateHelper->getTagsInOldFormat()));
    }

    /**
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\RecipientsGetException
     */
    public function syncAllRecipients()
    {
        /** @var Recipients $recipientService */
        $recipientService = ServiceRegister::getService(Recipients::CLASS_NAME);

        $this->enqueueTask(new FilterSyncTask());
        $this->enqueueTask(new RecipientSyncTask($recipientService->getAllRecipientsIds()));
    }

    /**
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function registerEventHandler()
    {
        $this->enqueueTask(new RegisterEventHandlerTask());
    }

    public function exchangeTokens()
    {
        $this->initializer->registerServices();
        $accessToken = $this->configService->getAccessToken();
        if ($accessToken === null || $accessToken === '') {
            return;
        }

        $task = new ExchangeAccessTokenTask();
        $task->execute();
    }

    /**
     * Updates product search endpoint to support magento 2.3.*
     *
     * @throws QueueStorageUnavailableException
     */
    public function updateProductSearchEndpoint()
    {
        $this->initializer->registerServices();
        $accessToken = $this->configService->getAccessToken();
        if ($accessToken === null || $accessToken === '') {
            return;
        }

        $this->enqueueTask(new ProductSearchSyncTask());
    }

    /**
     * @param \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Task $task
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    private function enqueueTask(Task $task)
    {
        $this->initializer->registerServices();
        $accessToken = $this->configService->getAccessToken();
        if ($accessToken === null || $accessToken === '') {
            return;
        }

        /** @var Queue $queueService */
        $queueService = ServiceRegister::getService(Queue::CLASS_NAME);
        $queueService->enqueue('Magento2-update', $task);
    }

    /**
     * Sets import statistic to have already been displayed.
     */
    public function setImportStatisticsDisplayed()
    {
        $this->configService->setImportStatisticsDisplayed(true);
    }

    /**
     * Set initial plugin configuration
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    public function setInitialConfig()
    {
        $this->initializer->registerServices();
        $this->configService->setTaskRunnerStatus('', null);
        $this->configService->setProductSearchEndpointPassword(md5(time()));
    }

    /**
     * Creates Process table.
     *
     * @throws \Zend_Db_Exception
     */
    private function createProcessTable()
    {
        $processTable = $this->installer->getTable('cleverreach_process');

        if (!$this->installer->getConnection()->isTableExists($processTable)) {
            $table = $this->installer->getConnection()
                ->newTable($this->installer->getTable('cleverreach_process'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'runner',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    500,
                    ['nullable' => false],
                    'Runner'
                );

            $this->installer->getConnection()->createTable($table);
        }
    }

    /**
     * Creates configuration table.
     *
     * @throws \Zend_Db_Exception
     */
    private function createConfigurationTable()
    {
        $configTable = $this->installer->getTable('cleverreach_config');

        if (!$this->installer->getConnection()->isTableExists($configTable)) {
            $table = $this->installer->getConnection()
                ->newTable($this->installer->getTable('cleverreach_config'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
                    'Id'
                )
                ->addColumn(
                    'configKey',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'ConfigKey'
                )
                ->addColumn(
                    'configValue',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['default' => null, 'nullable' => true],
                    'ConfigValue'
                );

            $this->installer->getConnection()->createTable($table);
        }
    }

    /**
     * Creates queue table.
     *
     * @throws \Zend_Db_Exception
     */
    private function createQueueTable()
    {
        $queueTable = $this->installer->getTable('cleverreach_queue');

        if (!$this->installer->getConnection()->isTableExists($queueTable)) {
            $table = $this->installer->getConnection()
                ->newTable($this->installer->getTable('cleverreach_queue'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
                    'Id'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'queueName',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'QueueName'
                )
                ->addColumn(
                    'progress',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => 0, 'nullable' => false],
                    'Progress'
                )
                ->addColumn(
                    'retries',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => 0, 'nullable' => false],
                    'Retries'
                )
                ->addColumn(
                    'failureDescription',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'FailureDescription'
                )
                ->addColumn(
                    'serializedTask',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    16777215,
                    ['nullable' => false],
                    'SerializedTask'
                )
                ->addColumn(
                    'createTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'CreateTimestamp'
                )
                ->addColumn(
                    'queueTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'QueueTimestamp'
                )
                ->addColumn(
                    'lastUpdateTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'LastUpdateTimestamp'
                )->addColumn(
                    'startTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'StartTimestamp'
                )->addColumn(
                    'finishTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'FinishTimestamp'
                )->addColumn(
                    'failTimestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'FailTimestamp'
                )->addColumn(
                    'lastExecutionProgress',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => 0, 'nullable' => false],
                    'LastExecutionProgress'
                );

            $this->installer->getConnection()->createTable($table);
        }
    }

    private function dropTable($tableName)
    {
        $tableInstance = $this->installer->getTable($tableName);
        if ($this->installer->getConnection()->isTableExists($tableInstance)) {
            $this->installer->getConnection()->dropTable($tableName);
        }
    }
}
