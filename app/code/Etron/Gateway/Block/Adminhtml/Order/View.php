<?php
namespace Etron\Gateway\Block\Adminhtml\Order;

class View extends \Magento\Sales\Block\Adminhtml\Order\View {

    protected function _construct()
    {
        parent::_construct();

        //if ($this->_isAllowedAction('Magento_Sales::unhold') && $order->canUnhold()) {
            $this->buttonList->add(
                'order_resend',
                [
                    'label' => __('Nachsenden (ETRON)'),
                    'class' => 'resend',
                    'onclick' => 'setLocation(\'' . $this->getResendUrl() . '\')',
                ]
            );
        //}
    }

    /**
     * @return string
     */
    public function getResendUrl()
    {
        return $this->getUrl('etrongateway/order/resend');
    } 
}