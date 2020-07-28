<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml;

class Shoppableindex extends \Magento\Backend\Block\Widget\Grid\Container
{
	protected function _construct()
    {
        $this->_controller = 'adminhtml_shoppableindex';
        $this->_blockGroup = 'Magecomp_Instagramshoppable';
        $this->_headerText = __('Manage Images');

        parent::_construct();
    }
	protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
