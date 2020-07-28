<?php
namespace Magecomp\Instagramshoppable\Cron;

use Magento\Backend\App\Action\Context;
use Magecomp\Instagramshoppable\Model\Productfeed;
use Magecomp\Instagramshoppable\Model\Observer;
class Fbfeedregenerate
{
    protected $_productFeed;
    protected $_instaObserver;

    public function __construct(Context $context, Productfeed $productfeed, Observer $instaObserver)
    {
        $this->_productFeed = $productfeed;
        $this->_instaObserver = $instaObserver;
    }
    public function execute()
    {
        try
        {
            $this->_instaObserver->internalGenerateProductFeed();
        }
        catch(\Exception $e)
        {
            $this->_productFeed->log($e->getMessage());
        }
    }
}