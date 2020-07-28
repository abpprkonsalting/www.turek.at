<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableauth;

class Index extends AbstractShoppableauth
{
	public function execute()
    {		
		$this->_redirect($this->_getAuthUrl());
    }
}