<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Setup;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\Helper\UpdateHelper;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ConfigService
     */
    public $configService;
    /**
     * @var InitializerInterface $initializer
     */
    private $initializer;
    /**
     * @var \CleverReach\CleverReachIntegration\Helper\UpdateHelper
     */
    private $updateHelper;

    public function __construct(
        ConfigService $configService,
        InitializerInterface $initializer,
        UpdateHelper $updateHelper
    ) {
        $this->configService = $configService;
        $this->initializer = $initializer;
        $this->updateHelper = $updateHelper;
    }

    /**
     * Does the Update process.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\RecipientsGetException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->startSetup();
        $databaseHandler = new DatabaseHandler($installer, $this->configService, $this->initializer, $this->updateHelper);
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $databaseHandler->dropTables();
        }

        $databaseHandler->createTables();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $databaseHandler->setInitialConfig();
        }

        $databaseHandler->alterTables();

        $tagsUpdated = false;
        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $databaseHandler->updateTags();
            $tagsUpdated = true;
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $databaseHandler->exchangeTokens();
            $databaseHandler->registerEventHandler();

            try {
                $databaseHandler->updateProductSearchEndpoint();
            } catch (\Exception $e) {
                Logger::logError(
                    'Could not update product search endpoint because: ' . $e->getMessage(),
                    'Integration'
                );
            }

            $databaseHandler->setImportStatisticsDisplayed();
            // if tags are updated, all recipients and segments are re-synced so no need to to it again
            if (!$tagsUpdated) {
                $databaseHandler->syncAllRecipients();
            }
        }

        $installer->endSetup();
    }
}
