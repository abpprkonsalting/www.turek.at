<?php
namespace Magecomp\Instagramshoppable\Cron;

use Magento\Backend\App\Action\Context;
use Magecomp\Instagramshoppable\Helper\Image\User;
use Magento\Store\Model\StoreManagerInterface;
use Magecomp\Instagramshoppable\Model\Productfeed;

class Fetchimages
{
    protected $_imageUser;
    protected $_storeManager;
    protected $_productFeed;

    public function __construct(Context $context, User $imageUser,
                                StoreManagerInterface $storeManager, Productfeed $productfeed)
    {
        $this->_imageUser = $imageUser;
        $this->_storeManager = $storeManager;
        $this->_productFeed = $productfeed;
    }
    public function execute()
    {
        try
        {
            foreach ($this->_storeManager->getStores(true) as $curstore)
            {
                $StoreId = $curstore->getStoreId();
                $this->_imageUser->update($StoreId);
            }
        }
        catch(\Exception $e)
        {
            $this->_productFeed->log($e->getMessage());
        }
    }
}