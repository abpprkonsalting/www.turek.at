<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\Helper\Data as BackendData;
use Magecomp\Instagramshoppable\Helper\Data;
use Magento\Framework\Locale\Resolver;
use Magecomp\Instagramshoppable\Model\Productfeed;
use Magento\Framework\UrlInterface;

class Synctofb extends Container
{
    protected $productMetadataInterface;
    protected $productCollection;

    protected $storeManagerInterface;

    protected $backendHelper;

    protected $instaHelper;

    protected $productFeedModel;

    protected $localeResolver;

    protected $logger;

    public function __construct(Context $context,

                                ProductMetadataInterface $productMetadataInterface,

                                CollectionFactory $productCollectionFactory,

                                StoreManagerInterface $storeManagerInterface,

                                Data $instaHelper,

                                BackendData $backendHelper,

                                Productfeed $productFeedModel,

                                Resolver $localeResolver,

                                LoggerInterface $logger,

                                array $data = [])

    {

        $this->productMetadataInterface = $productMetadataInterface;

        $this->productCollection = $productCollectionFactory;

        $this->storeManagerInterface = $storeManagerInterface;

        $this->instaHelper = $instaHelper;

        $this->backendHelper = $backendHelper;

        $this->productFeedModel = $productFeedModel;

        $this->localeResolver = $localeResolver;

        $this->logger = $logger;

        parent::__construct($context, $data);

    }

    public function getMagentoVersion()

    {

        return $this->productMetadataInterface->getVersion();

    }

    public function getBaseUrl()

    {

        return $this->storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB,['_secure' => true]);

    }

    public function getBaseUrlMedia()

    {

        return $this->storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA,['_secure' => true]);

    }

    public function logException($e)

    {

        $this->logger->info($e->getMessage().",".$e->getTraceAsString());

    }



    public function setErrorLogging() {

        register_shutdown_function ( function()

        {

            $error = error_get_last();

            if( $error !== NULL) {

                $errno   = $error["type"];

                $errfile = $error["file"];

                $errline = $error["line"];

                $errstr  = $error["message"];

                $log = $errno.":".$errstr." @ ".$errfile." L".$errline;

                $this->logger->info($log);

            }

        } );

    }



    public  function getStoreName()

    {

        $frontendName = $this->storeManagerInterface->getStore()->getName();

        if ($frontendName) {

            return $frontendName;

        }

        return parse_url($this->getBaseUrl(), PHP_URL_HOST);

    }

    public function getSelectedStore()

    {

        return $this->storeManagerInterface->getStore()->getId();

    }

    public function getStores()

    {

        $stores = $this->storeManagerInterface->getStores();

        $store_map = array();

        foreach ($stores as $store)

        {

            $val = $store->getWebsite()->getName() . ' > ' .

                $store->getGroup()->getName()  . ' > ' .

                $store->getName();

            $store_map[$val] = $store->getId();

        }

        return $store_map;

    }

    public  function getTotalVisibleProducts($store_id = 0)

    {

        if ($store_id === null || !is_numeric($store_id))

        {

            $store_id = $this->storeManagerInterface->getStore()->getId();

        }

        $productCollection = $this->productCollection->create();

        $productCollection->addStoreFilter($store_id)

            ->addAttributeToFilter('visibility',

                array(

                    'neq' =>

                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE

                )

            )

            ->addAttributeToFilter('status',

                array(

                    'eq' => 1

                ));

        return $productCollection->getSize();

    }

    public function getFBTimeZones($fb_timezone)

    {

        $timezone = array(

            "africa_accra" => 59,

            "africa_cairo" => 53,

            "africa_casablanca" => 86,

            "africa_johannesburg" => 141,

            "africa_lagos" => 96,

            "africa_nairobi" => 78,

            "africa_tunis" => 133,

            "america_anchorage" => 4,

            "america_argentina_buenos_aires" => 10,

            "america_argentina_salta" => 11,

            "america_argentina_san_luis" => 9,

            "america_asuncion" => 111,

            "america_atikokan" => 33,

            "america_belem" => 24,

            "america_blanc_sablon" => 36,

            "america_bogota" => 43,

            "america_campo_grande" => 23,

            "america_caracas" => 139,

            "america_chicago" => 6,

            "america_costa_rica" => 44,

            "america_dawson" => 27,

            "america_dawson_creek" => 29,

            "america_denver" => 2,

            "america_edmonton" => 30,

            "america_el_salvador" => 131,

            "america_guatemala" => 61,

            "america_guayaquil" => 51,

            "america_halifax" => 37,

            "america_hermosillo" => 92,

            "america_iqaluit" => 34,

            "america_jamaica" => 75,

            "america_la_paz" => 21,

            "america_lima" => 103,

            "america_los_angeles" => 1,

            "america_managua" => 97,

            "america_mazatlan" => 93,

            "america_mexico_city" => 94,

            "america_montevideo" => 138,

            "america_nassau" => 26,

            "america_new_york" => 7,

            "america_noronha" => 22,

            "america_panama" => 102,

            "america_phoenix" => 5,

            "america_port_of_spain" => 135,

            "america_puerto_rico" => 107,

            "america_rainy_river" => 31,

            "america_regina" => 32,

            "america_santiago" => 41,

            "america_santo_domingo" => 49,

            "america_sao_paulo" => 25,

            "america_st_johns" => 38,

            "america_tegucigalpa" => 63,

            "america_tijuana" => 91,

            "america_toronto" => 35,

            "america_vancouver" => 28,

            "asia_amman" => 76,

            "asia_baghdad" => 72,

            "asia_bahrain" => 20,

            "asia_bangkok" => 132,

            "asia_beirut" => 81,

            "asia_colombo" => 82,

            "asia_dhaka" => 17,

            "asia_dubai" => 8,

            "asia_gaza" => 108,

            "asia_ho_chi_minh" => 140,

            "asia_hong_kong" => 62,

            "asia_irkutsk" => 121,

            "asia_jakarta" => 66,

            "asia_jayapura" => 68,

            "asia_jerusalem" => 70,

            "asia_kamchatka" => 125,

            "asia_karachi" => 105,

            "asia_kolkata" => 71,

            "asia_krasnoyarsk" => 120,

            "asia_kuala_lumpur" => 95,

            "asia_kuwait" => 80,

            "asia_magadan" => 124,

            "asia_makassar" => 67,

            "asia_manila" => 104,

            "asia_muscat" => 102,

            "asia_nicosia" => 45,

            "asia_novosibirsk" => 120,

            "asia_omsk" => 119,

            "asia_qatar" => 112,

            "asia_riyadh" => 126,

            "asia_seoul" => 79,

            "asia_shanghai" => 42,

            "asia_singapore" => 128,

            "asia_taipei" => 136,

            "asia_tokyo" => 77,

            "asia_vladivostok" => 123,

            "asia_yakutsk" => 122,

            "asia_yekaterinburg" => 118,

            "atlantic_azores" => 109,

            "atlantic_canary" => 54,

            "atlantic_reykjavik" => 73,

            "australia_broken_hill" => 14,

            "australia_perth" => 13,

            "australia_sydney" => 15,

            "europe_amsterdam" => 98,

            "europe_athens" => 60,

            "europe_belgrade" => 114,

            "europe_berlin" => 47,

            "europe_bratislava" => 130,

            "europe_brussels" => 18,

            "europe_bucharest" => 113,

            "europe_budapest" => 65,

            "europe_copenhagen" => 48,

            "europe_dublin" => 69,

            "europe_helsinki" => 56,

            "europe_istanbul" => 134,

            "europe_kaliningrad" => 115,

            "europe_kiev" => 137,

            "europe_lisbon" => 110,

            "europe_ljubljana" => 129,

            "europe_london" => 58,

            "europe_luxembourg" => 84,

            "europe_madrid" => 55,

            "europe_malta" => 88,

            "europe_moscow" => 116,

            "europe_oslo" => 99,

            "europe_paris" => 57,

            "europe_prague" => 46,

            "europe_riga" => 85,

            "europe_rome" => 74,

            "europe_samara" => 117,

            "europe_sarajevo" => 16,

            "europe_skopje" => 87,

            "europe_sofia" => 19,

            "europe_stockholm" => 127,

            "europe_tallinn" => 52,

            "europe_vienna" => 12,

            "europe_vilnius" => 83,

            "europe_warsaw" => 106,

            "europe_zagreb" => 64,

            "europe_zurich" => 39,

            "indian_maldives" => 90,

            "indian_mauritius" => 89,

            "num_timezones" => 142,

            "pacific_auckland" => 100,

            "pacific_easter" => 40,

            "pacific_galapagos" => 50,

            "pacific_honolulu" => 3,

            "unknown" => 0);



        return $timezone[$fb_timezone];

    }

    public function determineFbTimeZone($magentoTimezone)

    {

        $fb_timezone = str_replace("/", "_", strtolower($magentoTimezone));

        if($this->getFBTimeZones($fb_timezone))

            return $this->getFBTimeZones($fb_timezone);

        else

            return $this->getFBTimeZones("unknown");

    }

    public function getDiaSettingIdAjaxRoute()

    {

        return $this->backendHelper->getUrl("instagramshoppable/synctofb/mainajax");

    }

    public function getPixelAjaxRoute()

    {

        return $this->backendHelper->getUrl("instagramshoppable/synctofb/pixelajax");

    }

    public function getStoreAjaxRoute()

    {

        return $this->backendHelper->getUrl("instagramshoppable/synctofb/storeajax");

    }

    public function getFeedGenerateNowAjaxRoute()

    {

        return $this->backendHelper->getUrl("instagramshoppable/synctofb/regenajax");

    }

    public function fetchPixelId()

    {

        return $this->instaHelper->getPixelId();

    }

    public function getPixelInstallTime()

    {

        $pixel_install_time =  $this->instaHelper->getPixelInstallTime();

        return $pixel_install_time ?: '';

    }

    public function fetchStoreBaseCurrency()

    {

        return $this->storeManagerInterface->getStore()->getBaseCurrencyCode();

    }

    public function fetchStoreTimezone()

    {

        return $this->determineFbTimeZone($this->instaHelper->getStoreTimezone());

    }

    public function fetchStoreName()

    {

        return htmlspecialchars($this->getStoreName(), ENT_QUOTES, 'UTF-8');

    }

    public function fetchFeedSetupEnabled()

    {

        $setup  = $this->instaHelper->getCurrentSetup();

        return $setup['enabled'];

    }

    public function fetchFeedSetupFormat()

    {

        $setup  = $this->instaHelper->getCurrentSetup();

        return $setup['format'];



    }

    public function getDiaSettingId()

    {

        return $this->instaHelper->getDiaSettingId();

    }

    public function getFeedUrl()

    {
        return sprintf('%sfacebook_adstoolbox_product_feed.%s', $this->getBaseUrlMedia(),
            strtolower($this->fetchFeedSetupFormat())

        );

    }

    public function fetchFeedSamples()

    {

        $this->setErrorLogging();

        try {

            $productSamples = $this->generateFacebookProductSamples();

            return $productSamples;

        } catch (Exception $e) {

            return $e->getMessage()." : ".$e->getTraceAsString();

        }

    }

    public function generateFacebookProductSamples()

    {

        $MAX = 12;

        $this->conversion_needed = false;

        $this->categoryNameMap = array();



        $locale_code = $this->localeResolver->getLocale();

        $symbols = \Zend_Locale_Data::getList($locale_code, 'symbols');

        $this->group_separator = $symbols['group'];

        $this->decimal_separator = $symbols['decimal'];

        $this->conversion_needed = true;

        $this->store_url = $this->getBaseUrl();

        $this->store_id = $this->storeManagerInterface->getStore()->getId();



        $results = array();

        $productCollection = $this->productCollection->create();

        $productCollection->addStoreFilter($this->store_id)

            ->addAttributeToFilter('visibility',

                array(

                    'neq' =>

                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE

                )

            )

            ->addAttributeToFilter('status',

                array(

                    'eq' => 1

                ))

            ->setPageSize($MAX)

            ->setCurPage(0)

            ->addUrlRewrite();

        $count = 0;

        if ($productCollection)

        {

            $this->logger->info("Parse ".count($productCollection)." products");

        }

        else

        {

            $this->logger->info("read returned products FAILED. DB Query returned nothing. You need at least 1 VISIBLE ENABLED product to use this extension.");

            return $results;

        }

        foreach ($productCollection as $product)

        {

            $count++;

            $results[]= $this->productFeedModel->buildProductEntry($product, $product->getName());

        }



        $this->logger->info('Finish fetching product samples');

        return $results;

    }

}