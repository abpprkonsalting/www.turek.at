<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\Helper\Url;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;

class ConfigService extends Configuration
{
    const MODULE_NAME = 'cleverreach';
    const INTEGRATION_NAME = 'Magento';
    /** Async request default timeout in milliseconds */
    const ASYNC_PROCESS_REQUEST_TIMEOUT = 1000;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    private $storeManager;

    /** @var Url  */
    private $urlHelper;

    /**
     * ConfigService constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Url $urlHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Url $urlHelper
    ) {
    
        $this->storeManager = $storeManager;
        self::$context = '';
        $this->urlHelper = $urlHelper;
    }

    /**
     * Return whether product search is enabled or not
     *
     * @return bool
     */
    public function isProductSearchEnabled()
    {
        return true;
    }

    /**
     * Retrieves parameters needed for product search registrations
     *
     * @return array, with array keys name, url, password
     */
    public function getProductSearchParameters()
    {
        $store = $this->storeManager->getStore();

        $params = [
            'ajax' => 1,
        ];

        $url = $this->urlHelper->getFrontUrl('cleverreach/search/productsearch', $params);

        return [
            'name' => self::INTEGRATION_NAME . ' (' . $store->getName() . ') '
                . 'ProductSearch - '. $store->getBaseUrl(),
            'url' => $url,
            'password' => $this->getProductSearchEndpointPassword()
        ];
    }

    /**
     * Retrieves integration name
     *
     * @return string
     */
    public function getIntegrationName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getQueueName()
    {
        return 'MagentoDefault';
    }

    public function getClientId()
    {
        return 'CFkMVkzRPM';
    }

    public function getClientSecret()
    {
        return 'SNfWYY6lkdgxevBzCuq752MqOHKozzar';
    }

    /**
     * Get auto configure state
     *
     * @return string
     */
    public function getAutoConfigureState()
    {
        return $this->getConfigRepository()->get('CLEVERREACH_AUTO_CONFIGURE_STATUS');
    }

    /**
     * Set if auto test server run successfully
     *
     * @param $value
     */
    public function setAutoConfigureState($value)
    {
        $this->getConfigRepository()->set('CLEVERREACH_AUTO_CONFIGURE_STATUS', $value);
    }

    /**
     * Get auto configuration test start time
     *
     * @return int
     */
    public function getAutoConfigureStartTime()
    {
        return $this->getConfigRepository()->get('CLEVERREACH_AUTO_CONFIGURE_START');
    }

    /**
     * Set auto configuration test start time
     *
     * @param $value
     */
    public function setAutoConfigureStartTime($value)
    {
        $this->getConfigRepository()->set('CLEVERREACH_AUTO_CONFIGURE_START', $value);
    }

    /**
     * Get curl additional options
     *
     * @return array
     */
    public function getCurlAdditionalOptions()
    {
        $optionsJson = $this->getConfigRepository()->get('CLEVERREACH_CURL_OPTIONS');

        return !empty($optionsJson) ? json_decode($optionsJson, true) : [];
    }

    /**
     * Set curl additional options
     *
     * @param $value
     */
    public function setCurlAdditionalOptions($value)
    {
        $this->getConfigRepository()->set('CLEVERREACH_CURL_OPTIONS', json_encode($value));
    }

    /**
     * Reset all curl additional options
     */
    public function resetCurlAdditionalOptions()
    {
        $this->getConfigRepository()->set('CLEVERREACH_CURL_OPTIONS', json_encode([]));
    }

    /**
     * @return string
     */
    public function getCrEventHandlerURL()
    {
        $params = [
            'ajax' => 1,
        ];

        return $this->urlHelper->getFrontUrl('cleverreach/webhook/cleverreachwebhooks', $params);
    }

    /**
     * Returns current user language code.
     *
     * @return string
     */
    public function getAuthenticatedUserLangCode()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Backend\Model\Auth\Session $session */
        $session = $om->get('Magento\Backend\Model\Auth\Session');
        $locale = $session->getUser()->getInterfaceLocale();

        if ($locale === null || $locale === '') {
            $locale = 'en_US';
        }

        return substr($locale, 0, 2);
    }
}
