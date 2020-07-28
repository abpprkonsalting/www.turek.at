<?php
namespace Magecomp\Instagramshoppable\Model;
use Magento\Framework\Model\AbstractModel;
class Instagramshoppableimage extends AbstractModel implements InstagramshoppableimageInterface, \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'instagramshoppableimage_image_id';
	
    protected function _construct()
    {
       $this->_init("Magecomp\Instagramshoppable\Model\ResourceModel\Instagramshoppableimage");
    }
	
	public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
