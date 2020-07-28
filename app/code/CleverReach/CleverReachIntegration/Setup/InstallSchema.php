<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Setup;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\Helper\UpdateHelper;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var ConfigService
     */
    public $configService;
    /**
     * @var InitializerInterface
     */
    public $initializer;
    /**
     * @var UpdateHelper
     */
    public $updateHelper;

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
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->startSetup();

        $databaseHandler = new DatabaseHandler($installer, $this->configService, $this->initializer, $this->updateHelper);
        $databaseHandler->dropTables();
        $databaseHandler->createTables();
        $databaseHandler->setInitialConfig();
        $databaseHandler->alterTables();

        $installer->endSetup();
    }
}
