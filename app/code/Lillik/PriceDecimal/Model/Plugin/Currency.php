<?php
/**
 *
 * @package package Lillik\PriceDecimal\Model\Plugin\Local
 *
 * @author  Lilian Codreanu <lilian.codreanu@gmail.com>
 */

namespace Lillik\PriceDecimal\Model\Plugin;

use Lillik\PriceDecimal\Model\ConfigInterface;

class Currency extends PriceFormatPluginAbstract
{

	protected $request;
	protected $state;

	public function __construct( ConfigInterface $moduleConfig,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\State $state) {
		$this->request = $request;
		$this->state = $state;
		parent::__construct( $moduleConfig );
	}

	/**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\CurrencyInterface $subject
     * @param array                                ...$args
     *
     * @return array
     */
    public function beforeToCurrency(
        \Lillik\PriceDecimal\Model\Currency $subject,
        ...$arguments
    ) {
        if ($this->getConfig()->isEnable()) {
	        $arguments[1]['precision'] = 2;
        	if ($this->state->getAreaCode()=="adminhtml" && $this->request->getControllerModule()=="Magento_Catalog")
	        {
		        $arguments[1]['precision'] = 4;
	        }

        }
        return $arguments;
    }
}
