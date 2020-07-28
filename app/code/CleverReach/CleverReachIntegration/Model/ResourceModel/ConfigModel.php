<?php

namespace CleverReach\CleverReachIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConfigModel extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('cleverreach_config', 'id');
    }

    /**
     * Saves config value by key
     *
     * @param $key
     * @param $value
     * @return int
     */
    public function updateValue($key, $value)
    {
        $row = $this->fetchRowByKey($key);
        $newData = ['configKey' => $key, 'configValue' => $value];

        if ($row) {
            $whereCondition = [$this->getIdFieldName() . '=?' => $row[$this->getIdFieldName()]];
            $this->getConnection()->update($this->getMainTable(), $newData, $whereCondition);
            $ret = $row['id'];
        } else {
            $this->getConnection()->insert($this->getMainTable(), $newData);

            $selectInserted = $this->getConnection()->select()->from($this->getMainTable())->where(
                'configKey = ?',
                $key
            );
            $inserted = $this->getConnection()->fetchRow($selectInserted);
            $ret = $inserted['id'];
        }

        return $ret;
    }

    /**
     * Return value by key
     *
     * @param $key
     * @return null
     */
    public function getValue($key)
    {
        $row = $this->fetchRowByKey($key);

        if (empty($row)) {
            return null;
        }

        return $row['configValue'];
    }

    private function fetchRowByKey($key)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())->where(
            'configKey = ?',
            $key
        );

        return $this->getConnection()->fetchRow($select);
    }
}
