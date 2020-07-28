<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableindex;

class NewAction extends AbstractShoppableindex
{
	 public function execute()
	 {
		$this->_forward('edit');
	 }
}
