<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Synctofb;
use Magento\Framework\Controller\ResultFactory;
class Regenajax extends \Magento\Backend\App\Action
{
    protected $adminSession;
    protected $observer;
    protected $resultJsonFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magecomp\Instagramshoppable\Model\Observer $observer,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->adminSession = $adminSession;
        $this->observer = $observer;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        if ($this->getRequest()->getParams() && $this->adminSession->isLoggedIn())
        {
            return $this->doRegenerateitnow($this->getRequest());
        }
    }

    public function doRegenerateitnow($request)
    {
        $use_cache = $request->getParam('useCache', false);
        $this->observer->internalGenerateProductFeed(false, $use_cache);
        $res['success'] = true;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($res);
        return $resultJson;
    }
}