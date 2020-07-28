<?php
namespace Magecomp\Instagramshoppable\Controller\Adminhtml\Shoppableauth;

abstract class AbstractShoppableauth extends \Magento\Backend\App\Action
{
    const INSTAGRAM_AUTH_URL = 'https://api.instagram.com/oauth/authorize/';
    const INSTAGRAM_ACCESSS_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    const INSTAGRAM_SESSION_DATA_KEY = 'instagramshoppable_session_data';
    const INSTAGRAM_CONFIG_DATA_KEY = 'magecomp/instagramshoppable/instagramshoppable_data';

    protected $_modelUrl;
    protected $_configScopeConfigInterface;
    protected $_helperData;

    public function __construct(\Magento\Backend\App\Action\Context $context, 
        \Magento\Backend\Model\Url $modelUrl, 
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface, 
        \Magecomp\Instagramshoppable\Helper\Data $helperData)
    {
        $this->_modelUrl = $modelUrl;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_helperData = $helperData;

        parent::__construct($context);
    }
	protected function _isAllowed()
    {
        return true;
    }
    public function preDispatch()
    {
        $this->_modelUrl->turnOffSecretKey();
        parent::preDispatch();
    }
    protected function _getAccessToken($code)
    {
        $postParams = $this->_getInstagamHelper()->buildUrl(
            null,
            [
                'client_id' => $this->_getClientId(),
                'client_secret' => $this->_getClientSecret(),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->_getAuthRedirectUri(),
                'code'          => $code
            ]
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::INSTAGRAM_ACCESSS_TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }
    protected function _getAuthUrl()
    {
         $url = $this->_getInstagamHelper()->buildUrl(
             self::INSTAGRAM_AUTH_URL,
             [
                 'client_id'    => $this->_getClientId(),
                 'redirect_uri' => $this->_getAuthRedirectUri(),
                 'response_type'=> 'code'
             ]
         );
        return $url;
    }
    protected function _getAuthRedirectUri()
    {
		return strstr($this->getUrl('instagramshoppable/auth/'), '/auth/', true).'/auth/index/';
    }
    protected function _getClientId()
    {
        return trim($this->_configScopeConfigInterface->getValue('instagramshoppable/module_options/client_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
    protected function _getClientSecret()
    {
        return trim($this->_configScopeConfigInterface->getValue('instagramshoppable/module_options/client_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
    protected function _getInstagamHelper()
    {
        return $this->_helperData;
    }
}