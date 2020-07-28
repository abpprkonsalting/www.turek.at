<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml\Shoppableindex;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_coreRegistry = null;
	
	public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        $this->_objectId = 'image_id';
        $this->_blockGroup = 'Magecomp_Instagramshoppable';
        $this->_controller = 'adminhtml_shoppableindex';
        parent::_construct();
        if ($this->_isAllowedAction('Magecomp_Instagramshoppable::instagramshoppable'))
		{
            $this->buttonList->update('save', 'label', __('Save Image Information'));
        } 
		else 
		{
            $this->buttonList->remove('save');
        }
    }
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('instagramshoppable_data')->getId()) {
            return __("Edit Image '%1'", $this->escapeHtml($this->_coreRegistry->registry('instagramshoppable_data')->getTitle()));
        } else {
            return __('New Image');
        }
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    protected function _getSaveAndContinueUrl()
    {
		$storeId = $this->getRequest()->getParam('store');			
        return $this->getUrl('*/*/save', ['_current' => true, 'store' => $storeId, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
	public function getSaveUrl()
    {
		$storeId = $this->getRequest()->getParam('store');
        return $this->getUrl('*/*/save', ['_current' => true, 'store' => $storeId, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
	public function getDeleteUrl(array $args = [])
	{
		return $this->getUrl('*/*/delete', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
	}
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}