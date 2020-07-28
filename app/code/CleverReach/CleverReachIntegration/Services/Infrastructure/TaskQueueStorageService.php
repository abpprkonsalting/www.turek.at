<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\TaskQueueStorage;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

class TaskQueueStorageService implements TaskQueueStorage
{

    /**
     * @var \CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem
     */
    private $queueItem;

    /**
     * @var \CleverReach\CleverReachIntegration\Model\QueueItem
     */
    private $queueItemModel;

    public function __construct(
        \CleverReach\CleverReachIntegration\Model\ResourceModel\QueueItem $queueItem,
        \CleverReach\CleverReachIntegration\Model\QueueItem $queueItemModel
    ) {
        $this->queueItem = $queueItem;
        $this->queueItemModel = $queueItemModel;
    }

    /**
     * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise update will be
     * performed.
     *
     * @param \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem Item to save
     * @param array $additionalWhere List of key/value pairs that must be satisfied upon saving queue item. Key is queue item
     * property and value is condition value for that property.
     * Example for MySql storage:
     *      $storage->save($queueItem, array('status' => 'queued')) should produce query
     *      UPDATE queue_storage_table SET .... WHERE .... AND status => 'queued'
     *
     * @return int Id of saved queue item
     * @throws QueueItemSaveException if queue item could not be saved
     */
    public function save(QueueItem $queueItem, array $additionalWhere = [])
    {
        try {
            $savedItemId = $this->queueItem->saveTaskQueueItem($queueItem, $additionalWhere);
        } catch (\Exception $exception) {
            throw new QueueItemSaveException(
                'Failed to save queue item. SQL error: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        return $savedItemId;
    }

    /**
     * Finds queue item by id
     *
     * @param int $id Id of a queue item to find
     * @return QueueItem|null Found queue item or null when queue item does not exist
     */
    public function find($id)
    {
        $item = null;
        
        $queueItemsModel = $this->queueItemModel->getCollection()->getItemById($id);
        if (!empty($queueItemsModel)) {
            $item = $this->createQueueItemFromArray($queueItemsModel->getData());
        }

        return $item;
    }

    /**
     * Finds latest queue item by type
     *
     * @param string $type Type of a queue item to find
     * @param string $context
     *
     * @return QueueItem|null Found queue item or null when queue item does not exist
     */
    public function findLatestByType($type, $context = '')
    {
        $item = null;

        $queueItemsModel = $this->queueItemModel
            ->getCollection()
            ->addFilter('type', $type)
            ->addOrder('queueTimestamp', TaskQueueStorage::SORT_DESC)
            ->getFirstItem();

        $queueItemsModelId = $queueItemsModel->getDataByKey('id');
        
        if (!empty($queueItemsModelId)) {
            $item = $this->createQueueItemFromArray($queueItemsModel->getData());
        }

        return $item;
    }

    /**
     * Finds list of earliest queued queue items per queue. Following list of criteria for searching must be satisfied:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be returned
     *
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned
     *
     * @return \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem[] Found queue item list
     */
    public function findOldestQueuedItems($limit = 10)
    {
        $items = [];
        $runningQueues = $this->findRunningQueues();
        $limit = (int)$limit;

        $queryItemArray = $this->queueItem->findOldestQueuedItems($limit, $runningQueues);
        
        if (!empty($queryItemArray)) {
            foreach ($queryItemArray as $itemModel) {
                $items[] = $this->createQueueItemFromArray($itemModel);
            }
        }

        return $items;
    }

    /**
     * Finds all queue items from all queues
     *
     * @param array $filterBy List of simple search filters, where key is queue item property and value is condition
     *      value for that property. Leave empty for unfiltered result.
     * @param array $sortBy List of sorting options where key is queue item property and value sort direction ("ASC" or "DESC").
     *      Leave empty for default sorting.
     * @param int $start From which record index result set should start
     * @param int $limit Max number of records that should be returned (default is 10)
     *
     * @return \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem[] Found queue item list
     */
    public function findAll(array $filterBy = [], array $sortBy = [], $start = 0, $limit = 10)
    {
        $items = [];
        $offset = (int)$start;
        $limit = (int)$limit;

        $queueItemsArray = $this->queueItem->findAllQueuedItems($filterBy, $sortBy, $offset, $limit);

        foreach ($queueItemsArray as $queueItem) {
            $items[] = $this->createQueueItemFromArray($queueItem);
        }

        return $items;
    }

    private function findRunningQueues()
    {
        $runningQueueItems = $this->findAll(['status' => QueueItem::IN_PROGRESS], [], 0, 10000);
        return array_map(
            function (QueueItem $runningQueueItem) {
                return $runningQueueItem->getQueueName();
            },
            $runningQueueItems
        );
    }

    /**
     * @param array $queueItemArray
     * @return QueueItem QueueItem
     */
    private function createQueueItemFromArray($queueItemArray)
    {
        $item = new QueueItem();
        $item->setId($queueItemArray['id']);
        $item->setStatus($queueItemArray['status']);
        $item->setQueueName($queueItemArray['queueName']);
        $item->setLastExecutionProgressBasePoints((int)$queueItemArray['lastExecutionProgress']);
        $item->setProgressBasePoints((int)$queueItemArray['progress']);
        $item->setRetries((int)$queueItemArray['retries']);
        $item->setFailureDescription($queueItemArray['failureDescription']);
        $item->setSerializedTask($queueItemArray['serializedTask']);
        $item->setCreateTimestamp($queueItemArray['createTimestamp']);
        $item->setQueueTimestamp($queueItemArray['queueTimestamp']);
        $item->setLastUpdateTimestamp($queueItemArray['lastUpdateTimestamp']);
        $item->setStartTimestamp($queueItemArray['startTimestamp']);
        $item->setFinishTimestamp($queueItemArray['finishTimestamp']);
        $item->setFailTimestamp($queueItemArray['failTimestamp']);

        return $item;
    }
}
