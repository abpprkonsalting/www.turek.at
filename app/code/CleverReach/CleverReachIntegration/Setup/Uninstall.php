<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Setup;

use CleverReach\CleverReachIntegration\Helper\InitializerInterface;
use CleverReach\CleverReachIntegration\Helper\UpdateHelper;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements UninstallInterface
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
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->startSetup();
        $this->initializer->registerServices();

        /** @var \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        try {
            $proxy->deleteReceiverEvent();
        } catch (\Exception $e) {
            Logger::logError('Could not delete receiver event handler because: ' . $e->getMessage(), 'Integration');
        }

        try {
            $contentId = $this->configService->getProductSearchContentId();
            if (!empty($contentId)) {
                $proxy->deleteProductSearchEndpoint($contentId);
            }
        } catch (\Exception $e) {
            Logger::logError('Could not delete product search endpoint because: ' . $e->getMessage(), 'Integration');
        }

        $databaseHandler = new DatabaseHandler(
            $installer,
            $this->configService,
            $this->initializer,
            $this->updateHelper
        );
        $databaseHandler->dropTables();

        $installer->endSetup();
    }
}
