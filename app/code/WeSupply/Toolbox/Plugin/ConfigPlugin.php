<?php

namespace WeSupply\Toolbox\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Phrase;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

class ConfigPlugin
{
    protected $configWriter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var WeSupplyApiInterface
     */
    protected $_weSupplyApi;

    /**
     * @var Helper
     */
    private $_helper;

    /**
     * API Credentials
     */
    private $subdomain;
    private $apiClientId;
    private $apiClientSecret;

    /**
     * ConfigPlugin constructor.
     * @param Context $context
     * @param WriterInterface $configWriter
     * @param WeSupplyApiInterface $weSupplyApi
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        WeSupplyApiInterface $weSupplyApi,
        Helper $helper
    ) {
        $this->configWriter = $configWriter;
        $this->messageManager = $context->getMessageManager();
        $this->_weSupplyApi = $weSupplyApi;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @return \Magento\Config\Model\Config
     */
    public function beforeSave(\Magento\Config\Model\Config $subject)
    {
        if ($subject->getSection() != 'wesupply_api') {
            // exit if section is not 'wesupply_api'
            return $subject;
        }

        // check required fields
        $groups = $subject->getGroups();
        if (!isset($groups['step_1']) || !isset($groups['step_2'])) {
            // reset status flag and continue
            $this->resetConnectionStatus($subject);

            return $subject;
        }
        $apiCredentials = array_merge($groups['step_1']['fields'], $groups['step_2']['fields']);
        $params = $this->prepareApiParams($apiCredentials);

        if ($params['has_error']) {
            $this->messageManager->addErrorMessage(__($params['validation_message']));
            // reset connection status flag and reset sms notification
            $this->resetConnectionStatus($subject);
            $this->resetConfigValues($groups, ['step_5/enable_delivery_estimations']);

            $subject->setData('groups', $groups);

            return $subject;
        }

        // check WeSupply api credentials
        $apiPath = $this->subdomain . '.' . $this->_helper->getWeSupplyDomain() . '/api/';
        $this->_weSupplyApi->setProtocol($this->_helper->getProtocol());
        $this->_weSupplyApi->setApiPath($apiPath);
        $this->_weSupplyApi->setApiClientId($this->apiClientId);
        $this->_weSupplyApi->setApiClientSecret($this->apiClientSecret);

        $apiResponse = $this->_weSupplyApi->checkApiCredentials();
        if (!$apiResponse) { // couldn't get a valid token
            $this->messageManager->addErrorMessage(__('Invalid token. Check WeSupply Account Client Id and Client Secret.'));
            // reset connection status flag and reset sms notification
            $this->resetConnectionStatus($subject);
            $this->resetConfigValues($groups, ['step_5/enable_delivery_estimations']);

            $subject->setData('groups', $groups);

            return $subject;
        }

        // set connection status flag and continue
        $this->setConnectionStatus($subject);

        /**
         * Check services availabilities
         */

        // check estimation service availability
        $forceExit = false;
        $enableDeliveryEstimation = $this->_helper->recursivelyGetArrayData(['step_5','fields','enable_delivery_estimations','value'], $groups, false);

        if (!$enableDeliveryEstimation) {
            // no check needed
            return $subject;
        }

        if ($enableDeliveryEstimation) {
            // delivery estimate enabling required
            $serviceIsAvailable = $this->_weSupplyApi->checkServiceAvailability('estimation');

            if ($serviceIsAvailable === false) { // API credentials check has thrown an exception
                $this->messageManager->addErrorMessage(__('Something went wrong. Check error log files for more details'));
                $this->resetConfigValues($groups, ['step_5/enable_delivery_estimations']);
                $subject->setData('groups', $groups);

                $forceExit = true;
            }

            if ($forceExit) {
                return $subject;
            }

            if (isset($serviceIsAvailable['allowed']) && $serviceIsAvailable['allowed'] === false) {
                $this->messageManager->addErrorMessage(__('In order to enable Delivery Estimation functionality, make sure this addon is activated under your WeSupply account. Please upgrade you plan.'));
                $this->resetConfigValues($groups, ['step_5/enable_delivery_estimations']);
                $subject->setData('groups', $groups);
            }
        }

        return $subject;
    }

    /**
     * @param $apiCredentials
     * @return array
     */
    private function prepareApiParams($apiCredentials)
    {
        $response = [
            'has_error' => false,
            'validation_message' => ''
        ];

        $params = [];
        if (isset($apiCredentials['wesupply_subdomain']['value'])) {
            $params['subdomain'] = $apiCredentials['wesupply_subdomain']['value'];
        } else {
            if (isset($apiCredentials['wesupply_subdomain']['inherit'])) {
                // try to get inherited value
                $params['subdomain'] = $this->_helper->getClientName();
            }
        }

        if (isset($apiCredentials['wesupply_client_id']['value'])) {
            $params['apiClientId'] = $apiCredentials['wesupply_client_id']['value'];
        } else {
            if (isset($apiCredentials['wesupply_client_id']['inherit'])) {
                // try to get inherited value
                $params['apiClientId'] = $this->_helper->getWeSupplyApiClientId();
            }
        }

        if (isset($apiCredentials['wesupply_client_secret']['value'])) {
            $params['apiClientSecret'] = $apiCredentials['wesupply_client_secret']['value'];
        } else {
            if (isset($apiCredentials['wesupply_client_secret']['inherit'])) {
                // try to get inherited value
                $params['apiClientSecret'] = $this->_helper->getWeSupplyApiClientSecret();
            }
        }

        $validationMessage = $this->_validateParams($params);
        if ($validationMessage) {
            $response['has_error'] = true;
            $response['validation_message'] = $validationMessage;
        }

        return $response;
    }

    /**
     * @param $params
     * @return bool|Phrase
     */
    private function _validateParams($params)
    {
        if (!$params['subdomain'] || empty($params['subdomain'])) {
            return __('WeSupply Subdomain is required.');
        }
        $this->subdomain = trim($params['subdomain']);
        $this->apiClientId = trim($params['apiClientId']) ?? '';
        $this->apiClientSecret = trim($params['apiClientSecret']) ?? '';

        return false;
    }

    /**
     * @param $subject
     */
    protected function resetConnectionStatus($subject)
    {
        $scope = $this->getCurrentScope($subject);
        $scopeId = $this->getCurrentScopeId($subject);
        $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 0, $scope, $scopeId);
    }

    /**
     * @param $subject
     */
    protected function setConnectionStatus($subject)
    {
        $scope = $this->getCurrentScope($subject);
        $scopeId = $this->getCurrentScopeId($subject);
        $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 1, $scope, $scopeId);
    }

    /**
     * @param $groups
     * @param array $fields
     * @return mixed
     */
    protected function resetConfigValues(&$groups, $fields = [])
    {
        foreach ($fields as $field) {
            $fieldArr = explode('/', $field);
            $groups[$fieldArr[0]]['fields'][$fieldArr[1]]['value'] = 0;
        }

        return $groups;
    }

    /**
     * @param $subject
     * @return string
     */
    protected function getCurrentScope($subject)
    {
        if ($subject->getStore()) {
            return 'stores';
        }

        if ($subject->getWebsite()) {
            return 'websites';
        }

        return 'default';
    }

    /**
     * @param $subject
     * @return string
     */
    protected function getCurrentScopeId($subject)
    {
        if ($subject->getStore()) {
            return  $subject->getStore();
        }

        if ($subject->getWebsite()) {
            return $subject->getWebsite();
        }

        return '0';
    }
}
