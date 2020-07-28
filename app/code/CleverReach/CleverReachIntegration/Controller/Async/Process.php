<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Controller\Async;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;

class Process extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \CleverReach\CleverReachIntegration\Model\ResourceModel\Process
     */
    private $resourceProcess;

    /**
     * @var \CleverReach\CleverReachIntegration\Model\Process
     */
    private $processModel;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \CleverReach\CleverReachIntegration\Model\ResourceModel\Process $resourceProcess,
        \CleverReach\CleverReachIntegration\Model\Process $processModel
    ) {
        $this->resourceProcess = $resourceProcess;
        $this->processModel = $processModel;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     */
    public function execute()
    {
        $guid = $this->getRequest()->getParam('guid');
        try {
            $process = $this->processModel->getCollection()->getItemById($guid);
            if (empty($process)) {
                return;
            }

            $runner = unserialize($process->getDataByKey('runner'));
            $runner->run();
            $this->resourceProcess->deleteProcessForGuid($guid);
        } catch (\Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
        }
    }
}
