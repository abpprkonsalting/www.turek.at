<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 30.05.17
 * Time: 16:20
 */

namespace Etron\Gateway\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Resend extends \Magento\Sales\Controller\Adminhtml\Order
{
     /* @var \Etron\Gateway\Helper\Data $helper */
    protected $helper;

    /* @var  \Etron\Gateway\Logger\Logger $logger */
    protected $logger;

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->helper = $this->_objectManager->get('\Etron\Gateway\Helper\Data');
                $this->logger = $this->_objectManager->get('\Etron\Gateway\Logger\Logger');
                $this->logger->debug('Nachsenden(ETRON)', $order->getData());
                $response = $this->helper->sendOrderJSON($order);
                if ($response['code']!=200) {
                    $this->messageManager->addErrorMessage(__('Bestellung #'.$order->getIncrementId().' konnte nicht nachgesendet werden. Status:'.$response['code'].', Meldung:'.$response['response']));
                    $this->logger->warn('Nachsenden(ETRON) fehlgeschlagen', $response);
                } else {
                    $this->messageManager->addSuccessMessage(__('Bestellung #'.$order->getIncrementId().' wurde erfolgreich nachgesendet. Status:'.$response['code'].', Meldung:'.$response['response']));
                    $this->logger->warn('Nachsenden(ETRON) erfolgreich', $response);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->error('Nachsenden(ETRON) fehlgeschlagen'.$e->getMessage());
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->error('Nachsenden(ETRON) fehlgeschlagen'.$e->getMessage());
                $this->messageManager->addErrorMessage(__('Bestellung konnte nicht nachgesendet werden.'));
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;

    }

}