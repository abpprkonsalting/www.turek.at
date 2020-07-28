<?php

namespace Etron\Gateway\Console;

use Magento\Framework\Exception\AlreadyExistsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
/**
 * Class ResendOrder.php
 */
class ResendOrder extends Command
{

    const NAME_ARGUMENT = 'order';

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $_orderCollection;
    

    /**
     * RegenerateUrls constructor.
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param string $name
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Etron\Gateway\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
		\Magento\Framework\App\State $state,
        $name = 'resend_order'
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->helper = $helper;
        $this->_orderCollection = $orderCollection;
	$state->setAreaCode('frontend');
        parent::__construct($name);
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('etron:resend_order')
        ->setDefinition([
              new InputArgument(
                  self::NAME_ARGUMENT,
                  InputArgument::OPTIONAL,
                  'Order Number'
              ),
          ]);
        $this->setDescription('Resend order to the gateway');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $order_nr = $input->getArgument(self::NAME_ARGUMENT);
      if (is_null($order_nr)) {
        throw new \InvalidArgumentException('Argument ' . self::NAME_ARGUMENT . ' is missing.');
      }
      $this->_orderCollection->addFieldToFilter('entity_id', ['eq' => $order_nr]);
      foreach ($this->_orderCollection as $order) {
          echo "Sending order ".$order->getIncrementId()." to gateway....";
          $this->logger->info('Order Dump',$order->getData());
          $this->helper->sendOrderJSON($order);
          echo "Done.\n";
      }
    }
}
