<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Model\ResourceModel;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\TaskExecution\QueueItem as TaskQueueItem;

class QueueItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Model Initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cleverreach_queue', 'id');
    }

    /**
     * @param TaskQueueItem $queueItem
     * @param array $additionalWhere
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveTaskQueueItem(TaskQueueItem $queueItem, array $additionalWhere)
    {
        $queueItemId = $queueItem->getId();

        if (is_null($queueItemId) || $queueItemId <= 0) {
            $queueItemId = $this->insertQueueItem($queueItem);
        } else {
            $queueItemId = $this->updateQueueItem($queueItem, $additionalWhere);
        }

        return $queueItemId;
    }

    public function findOldestQueuedItems($limit, $runningQueues)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->where(
                'status = ?',
                TaskQueueItem::QUEUED
            )
            ->group('queueName')
            ->order(['queueTimestamp'])
            ->limit($limit);
        
        if (!empty($runningQueues)) {
            $select->where(
                'queueName NOT IN (?)',
                $runningQueues
            );
        }

        return $connection->fetchAll($select);
    }

    public function findAllQueuedItems($filterBy, $sortBy, $offset, $limit)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->limit($limit, $offset);

        if (!empty($filterBy)) {
            foreach ($filterBy as $filterKey => $filterValue) {
                $select->where(
                    $filterKey . ' = ?',
                    $filterValue
                );
            }
        }

        if (!empty($sortBy)) {
            foreach ($sortBy as $sortKey => &$sortValue) {
                $sortValue = $sortKey . ' ' . $sortValue;
            }

            $select->order(implode(',', $sortBy));
        }

        return $connection->fetchAll($select);
    }

    /**
     * @param string $where
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteQueueItemByWhereCondition($where)
    {
        $this->getConnection()->delete($this->getMainTable(), $where);
    }

    private function insertQueueItem(TaskQueueItem $queueItem)
    {
        $connection = $this->getConnection();
        $newData = $this->getNewQueueItemData($queueItem);
        $connection->insert($this->getMainTable(), $newData);

        return $connection->lastInsertId();
    }

    private function updateQueueItem(TaskQueueItem $queueItem, array $additionalWhere)
    {
        $connection = $this->getConnection();
        $row = $this->getQueueItemRow($connection, $queueItem, $additionalWhere);
        $this->checkIfRecordWithWhereConditionsExists($row, $additionalWhere);

        $newData = $this->getNewQueueItemData($queueItem);

        $whereCondition = [$this->getIdFieldName() . '=?' => $row[$this->getIdFieldName()]];
        $connection->update($this->getMainTable(), $newData, $whereCondition);

        return $queueItem->getId();
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param TaskQueueItem $queueItem
     * @param array $additionalWhere
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getQueueItemRow($connection, $queueItem, $additionalWhere)
    {
        $select = $connection->select()->from($this->getMainTable())->where(
            'id = ?',
            $queueItem->getId()
        )->forUpdate(true);

        if (!empty($additionalWhere)) {
            foreach ($additionalWhere as $whereKey => $whereValue) {
                $condition = ' = ?';
                if (is_null($whereValue)) {
                    $condition = ' IS NULL';
                }

                $select->where($whereKey . $condition, $whereValue);
            }
        }

        return $connection->fetchRow($select);
    }

    private function checkIfRecordWithWhereConditionsExists($row, array $additionalWhere)
    {
        if (empty($row)) {
            Logger::logDebug(\json_encode([
                'Message' => 'Failed to save queue item, update condition not met.',
                'WhereCondition' => $additionalWhere,
            ]));

            throw new QueueItemSaveException('Failed to save queue item, update condition not met.');
        }
    }

    private function getNewQueueItemData(TaskQueueItem $queueItem)
    {
        return [
            'status' => $queueItem->getStatus(),
            'type' => $queueItem->getTaskType(),
            'queueName' => $queueItem->getQueueName(),
            'lastExecutionProgress' => (int)$queueItem->getLastExecutionProgressBasePoints(),
            'progress' => (int)$queueItem->getProgressBasePoints(),
            'retries' => $queueItem->getRetries(),
            'failureDescription' => $queueItem->getFailureDescription(),
            'serializedTask' => $queueItem->getSerializedTask(),
            'createTimestamp' => $queueItem->getCreateTimestamp(),
            'queueTimestamp' => $queueItem->getQueueTimestamp(),
            'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            'startTimestamp' => $queueItem->getStartTimestamp(),
            'finishTimestamp' => $queueItem->getFinishTimestamp(),
            'failTimestamp' => $queueItem->getFailTimestamp(),
        ];
    }
}
