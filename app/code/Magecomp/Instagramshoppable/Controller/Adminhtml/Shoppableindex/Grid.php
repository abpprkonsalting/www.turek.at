<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

class Grid extends AbstractShoppableindex
{
	public function execute()
    {
		$this->_initAction();
        $this->renderLayout();
    }
}
