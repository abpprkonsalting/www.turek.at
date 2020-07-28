<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Redirecturl extends Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magecomp_Instagramshoppable::instagramshoppable/config/form/field/redirecturl.phtml');
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
 }