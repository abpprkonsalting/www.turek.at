<?php
namespace Magecomp\Instagramshoppable\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Instagramshoppableimage extends AbstractDb
{
	protected $_isPkAutoIncrement = false;
    
    protected function _construct()
    {
    	$this->_init("instagramshoppable_image", "image_id");
    }
}
