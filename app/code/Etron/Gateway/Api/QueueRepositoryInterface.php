<?php
namespace Etron\Gateway\Api;

use Etron\Gateway\Api\Data\QueueInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QueueRepositoryInterface 
{
    public function save(QueueInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(QueueInterface $page);

    public function deleteById($id);
}
