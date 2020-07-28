<?php

namespace CleverReach\CleverReachIntegration\Helper;

use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url as MagentoUrl;

class Url
{
    /** @var StoreManagerInterface  */
    private $storeManager;
    
    /** @var MagentoUrl  */
    private $urlHelper;

    /**
     * Url constructor.
     * @param StoreManagerInterface $storeManager
     * @param MagentoUrl $urlHelper
     */
    public function __construct(StoreManagerInterface $storeManager, MagentoUrl $urlHelper)
    {
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Extracts FE controllers url from Magento, based on route path and route params.
     *
     * @param $routePath
     * @param array $routeParams
     *
     * @return string
     */
    public function getFrontUrl($routePath, $routeParams = null)
    {
        $storeView = $this->storeManager->getStore();

        $url = $this->urlHelper->setScope($storeView)->getUrl($routePath, $routeParams);

        return $this->removeUnnecessaryQueryParams($url, $routeParams);
    }

    private function removeUnnecessaryQueryParams($url, $routeParams)
    {
        if ($routeParams !== null) {
            return $url;
        }

        return explode('?', $url)[0];
    }
}
