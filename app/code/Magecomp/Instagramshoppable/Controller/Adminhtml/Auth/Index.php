<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Auth;

use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_helperData;
    public function __construct(Context $context, HelperData $helperData)
    {
        $this->_helperData = $helperData;

        parent::__construct($context);
    }
    public function execute()
    {
        $code = $this->getRequest()->getQuery('code');
        $adminUrl = $this->_helperData
            ->getUrl("instagramshoppable/shoppableauth/callback", [ 'code' => $code ]);
		$this->_redirect($adminUrl);
        return;
    }
}