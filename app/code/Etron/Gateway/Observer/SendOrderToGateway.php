<?php
namespace Etron\Gateway\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class SendOrderToGateway implements ObserverInterface
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $config;
    
    /** @var \Etron\Gateway\Helper\Data */
    protected $helper;
    
    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $collection;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Etron\Gateway\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Collection $collection
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->helper = $helper;
        $this->collection = $collection;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('Observer SendOrderToGateway called');
        
        if (!$this->config->getValue('gateway/settings/is_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->logger->info('Gateway is disabled');
            return;
        }
        
        try {

            //$orderIds = $observer->getEvent()->getData('order_ids');
            $order = $observer->getEvent()->getData('order');
            if (!$order) {
                $this->logger->warn('No "order" parameter passed with event');
                return $this;
            }
            $orderIds = [ $order->getId() ];

            $this->collection->addFieldToFilter('entity_id', ['in' => $orderIds]);
            foreach ($this->collection as $order) {
                $orderData = $order->getData();
                $orderData['payment'] = $order->getPayment()->getData();
                if ($orderData['payment']['method'] == 'banktransfer') {
                    $this->logger->info('Order with payment method "banktransfer" is retained');
                    return $this;
                }
                $this->logger->info('Order Dump',$order->getData());
                $response = $this->helper->sendOrderJSON($order);
                $this->logger->debug('Status:'.$response['code'].', Meldung:'.$response['response']);
            }
        } catch (\Exception $ex) {
            $this->logger->warn('Error: ' . $ex->getMessage());
        }
        
    }
    
    
}