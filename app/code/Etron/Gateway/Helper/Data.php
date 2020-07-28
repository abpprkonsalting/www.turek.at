<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Etron\Gateway\Helper;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	protected $storeManager;
	protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Etron\Gateway\Logger\Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }


	/**
	 * @param \Magento\Sales\Api\Data\OrderInterface $order
	 * @return bool
	 */
    public function isEnabled($order) {
	    return $this->scopeConfig->isSetFlag('gateway/settings/is_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());
    }

	/**
	 * @param \Magento\Sales\Api\Data\OrderInterface $order
	 * @return bool
	 */
    public function canAutomaticResend($order) {
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();
	    $manual_methods = explode(',', $this->scopeConfig->getValue('gateway/settings/manual_resend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId()));
        if (in_array($payment->getMethod(), $manual_methods)) {
            return false;
	    }
        return true;
    }

	private function getOrderArray($order) {
		if (!is_object($order)) return false;
       
	    $result = $order->getData();
        $result['created_at'] = $order->getCreatedAt();
		if ($order->getCreatedAt() == null) {
			$result['created_at'] = date("Y-m-d H:i:s");
		}
		
		$result['updated_at'] = $order->getUpdatedAt();
		if ($order->getUpdatedAt() == null) {
			$result['updated_at'] = date("Y-m-d H:i:s");
		}
        if ($result['is_virtual']==1) {
			// if order is virtual - use billing address as shipping address
	        $result['shipping_address'] = $order->getBillingAddress()->getData();
	        $result['shipping_address']['address_id'] = $result['shipping_address']['entity_id'];
        } else {
	        $result['shipping_address'] = $order->getShippingAddress()->getData();
	        $result['shipping_address']['address_id'] = $result['shipping_address']['entity_id'];
        }
        $result['billing_address']  = $order->getBillingAddress()->getData();
		$result['billing_address']['address_id'] = $result['billing_address']['entity_id'];
        
		if ($result['customer_id'] == null) {
			$result['customer_prefix'] = $result['billing_address']['prefix'];
			$result['customer_firstname'] = $result['billing_address']['firstname'];
			$result['customer_lastname'] = $result['billing_address']['lastname'];
		}

        // Build items list and fix " in items
        $items = $order->getAllItems();
        $ItemArray = array();
        if(count($items) > 0) {
            foreach ($items as $item) {
                $itemData = $item->getData();
                $itemData['name'] = str_replace('"','',$itemData['name']);
                $ItemArray[] = $itemData;
            }
        }
				
        if ($result['base_discount_amount']<0) {

            $ItemArray[] = array(
                    "item_id" => 0,
                    "order_id" => $result['increment_id'],
                    "product_id" => 0,
                    "product_type" => "simple",
                    "sku" => "#discount_amount" . (!empty($result['coupon_code']) ? ':'.$result['coupon_code'] : ''),
                    "name" => (!empty($result['coupon_rule_name']) ? $result['coupon_rule_name'] : 'Rabatt'),
                    "base_price" => $result['base_discount_amount'],
                    "base_price_incl_tax" => $result['base_discount_amount'],
                    "base_row_total_incl_tax" => $result['base_discount_amount'],
                    "tax_percent" => "0.000",
                    "qty_ordered" => 1,
                );
        }
        
        $result['items'] = $ItemArray;
        $result['total_item_count'] = count($ItemArray);
        
        
        $result['payment'] = $order->getPayment()->getData();
				
        if (substr($result['payment']['method'],0,10)=="payunitycw"){
            $additionalInformation = array();
            if ($result['payment']['method'] == 'payunitycw_mastercard'){
                $additionalInformation['financialInstitution'] = 'MC';
                $additionalInformation['paymentType'] = 'CC';        
            } elseif($result['payment']['method'] == 'payunitycw_visa'){
                $additionalInformation['financialInstitution'] = 'VISA';
                $additionalInformation['paymentType'] = 'CC'; 
            } else {
                $fi = substr($result['payment']['method'],11); // if "payunitycw_paypal" then result is "paypal"
                $additionalInformation['financialInstitution'] = $fi;
                $additionalInformation['paymentType'] = 'CC';
            }
            
            $result['payment']['method'] = 'hl_mpay'; // do mapping before GW
            $result['payment']['additional_data'] = serialize($additionalInformation);
            $result['payment']['additional_information'] = $additionalInformation;
        }
        if ($result['payment']['method'] == 'cryozonic_stripe') {
            $card = ucfirst(strtolower($result['payment']['cc_type']));
            $result['payment']['additional_information'] = array('financialInstitution'=>$card, 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method'] == 'pupay_cc') {
            if (isset($result['payment']['additional_information']) && is_array($result['payment']['additional_information']) &&
                isset($result['payment']['additional_information']['paymentType']) && $result['payment']['additional_information']['paymentType']!='CC')
            {
                   $result['payment']['additional_information'] = array('financialInstitution'=>'sofortueberweisung', 'paymentType'=>'CC');
                   $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
                   $result['payment']['method'] = "hl_mpay";
            }
        }
        if ($result['payment']['method'] == 'banktransfer') {
            // map 'banktransfer' to hl_mpay/Vorauskasse to work with ETRON
            $result['payment']['additional_information'] = array('financialInstitution'=>'Vorauskasse', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method'] == 'paypal_express') {
            // map 'paypal_wps_express' to hl_mpay/Paypal to work with ETRON
            $result['payment']['additional_information'] = array('financialInstitution'=>'PayPal', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method'] == 'paymentnetwork_pnsofortueberweisung') {
            // map 'paymentnetwork_pnsofortueberweisung' to hl_mpay/Paypal to work with ETRON
            $result['payment']['additional_information'] = array('financialInstitution'=>'Sofortüberweisung', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method']=="ops_cc") {
            $ccbrand = "??";
            if (isset($result['payment']['additional_information'])) {
                $ccbrand = $result['payment']['additional_information']['CC_BRAND'];
                if (strtolower($ccbrand) == 'mc') $ccbrand = 'Mastercard';
            }
            $result['payment']['additional_information'] = array('financialInstitution'=>$ccbrand, 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method']=="ops_directEbanking") {
            $result['payment']['additional_information'] = array('financialInstitution'=>'Sofortüberweisung', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method']=="ops_paypal") {
            $result['payment']['additional_information'] = array('financialInstitution'=>'PayPal', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        if ($result['payment']['method']=="vaimo_klarna_invoice") {
            $result['payment']['additional_information'] = array('financialInstitution'=>'Klarna', 'paymentType'=>'CC');
            $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            $result['payment']['method'] = "hl_mpay";
        }
        // support for wirecard seamless checkout
        if (substr($result['payment']['method'],0,25)=="wirecard_checkoutseamless"){
            // $parts = explode('_', $result['payment']['method']);
            // $type = end($parts); // wirecard_checkoutseamless_ccard / _paypal etc.
            $result['payment']['method'] = 'hl_mpay'; // override for ETRON
            if (isset($result['payment']['additional_information'])) {
                if ($result['payment']['additional_information']['paymentType']=='CCARD') { // fix for Gateway/ETRON
                    $result['payment']['additional_information']['paymentType']='CC';
                }
                // override this as well
                $result['payment']['additional_data'] = serialize($result['payment']['additional_information']);
            }
        }
				
        $result['status_history'] = array();
        
        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $history->getData();
        }

		return array($result);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    public function sendOrderJSON($order) {
        $result = ['code'=>'', 'response'=>''];

        if (!$this->isEnabled($order)) {
        	return ['code'=>'info', 'Gateway is disabled for store'];
        }
        $connection_name = $this->scopeConfig->getValue('gateway/settings/connection_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());
        $endpoint_url = $this->scopeConfig->getValue('gateway/settings/endpoint_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStoreId());
        
		$this->logger->info('--- sending order json data to host='.$endpoint_url.', connection='.$connection_name);
		$restApiPath = '/api/rest/1.0/orders/'.$connection_name.'/shop';
        
        $url = $endpoint_url . $restApiPath;
        $this->logger->info('--- using url: '.$url);
        
		try {
			$this->logger->info('--- get order json data');
			$jsondata = json_encode($this->getOrderArray($order));
			$this->logger->info('--- order json data = '.$jsondata);
			
			$this->logger->info('--- initialize curl transmission');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
            curl_setopt($ch, CURLOPT_POST, true);
            $postdata = "text=".urlencode($jsondata);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);                                                                                                                                                                                    
            $this->logger->info('--- executing curl transmission');
            $response = curl_exec($ch);
            $this->logger->info('--- curl responded: '.$response);
            
            $this->logger->info('--- evaluating response code');
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['code'] = $code;
            $result['response'] = $response;
            $this->logger->info('--- got result = code: '.$code.', response='.$response);
            $this->logger->info('--- closing curl transmission');
            curl_close($ch);
        }   catch (\Exception $e) {
            $this->logger->error('--- ERROR SENDING DATA: '.$e->getMessage());
            $result['code'] = 'error';
            $result['response'] = $e->getMessage();
        }
		return $result;
	}
	
}