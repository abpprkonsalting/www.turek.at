<?php
namespace Magecomp\Instagramshoppable\Model\ResourceModel\Instagramshoppableimage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	public function _construct()
	{
		$this->_init("Magecomp\Instagramshoppable\Model\Instagramshoppableimage", "Magecomp\Instagramshoppable\Model\ResourceModel\Instagramshoppableimage");
	}
}