<?php
/**
 * @package     CleverReach_CleverReachIntegration
 * @author      CleverReach
 * @copyright   2019 CleverReach
 */

namespace CleverReach\CleverReachIntegration\Model\ResourceModel;

class Process extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('cleverreach_process', 'id');
    }

    /**
     * @param $processGuid
     * @param $runner
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveGuidAndRunner($processGuid, $runner)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where(
            'id = ?',
            $processGuid
        );

        $row = $connection->fetchRow($select);

        $newData = ['id' => $processGuid, 'runner' => serialize($runner)];

        if ($row) {
            $whereCondition = [$this->getIdFieldName() . '=?' => $row[$this->getIdFieldName()]];
            $connection->update($this->getMainTable(), $newData, $whereCondition);
        } else {
            $connection->insert($this->getMainTable(), $newData);
        }

        return $this;
    }

    /**
     * @param $guid
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteProcessForGuid($guid)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), [
            $connection->quoteInto('id = ?', $guid),
        ]);

        return $this;
    }
}
