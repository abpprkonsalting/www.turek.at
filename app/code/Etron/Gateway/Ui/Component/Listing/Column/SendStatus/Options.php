<?php
/**
 * Created by PhpStorm.
 * User: stephan
 * Date: 25.04.18
 * Time: 14:01
 */

namespace Etron\Gateway\Ui\Component\Listing\Column\SendStatus;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
	/**
	 * @var Escaper
	 */
	private $escaper;

	/**
	 * Constructor
	 *
	 * @param Escaper $escaper
	 */
	public function __construct(Escaper $escaper)
	{
		$this->escaper = $escaper;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toOptionArray()
	{
		return [
			[
				'value' => -1,
				'label' => $this->escaper->escapeHtml(__('Fehler'))
			],
			[
				'value' => 0,
				'label' => ''
			],
			[
				'value' => 1,
				'label' => $this->escaper->escapeHtml(__('Wird gesendet'))
			],
			[
				'value' => 2,
				'label' => $this->escaper->escapeHtml(__('Erfolgreich'))
			]
		];
	}
}
