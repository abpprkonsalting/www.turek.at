<?php
namespace Magecomp\Instagramshoppable\Controller\Gallery;

class Instalist extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
		parent::__construct($context);
    }
	public function execute()
	{
		$this->_view->loadLayout();
        $this->_view->renderLayout();
	}
}
