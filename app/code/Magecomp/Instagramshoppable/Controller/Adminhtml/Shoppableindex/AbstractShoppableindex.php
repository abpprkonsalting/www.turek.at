<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

abstract class AbstractShoppableindex extends \Magento\Backend\App\Action
{
 	protected function _isAllowed()
    {
        return true;
    }
}