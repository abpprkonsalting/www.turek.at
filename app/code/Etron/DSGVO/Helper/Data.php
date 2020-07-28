<?php
namespace Etron\DSGVO\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;

class Data extends AbstractHelper {

	protected $blockRepository;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		BlockRepositoryInterface $blockRepository
	)
	{
		parent::__construct($context);
		$this->blockRepository = $blockRepository;
	}


	public function getCmsBlockId() {
		return $this->scopeConfig->getValue('etron_dsgvo/general/info_block',
											\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getCmsBlockHtml() {
		$content = false;
		if ($blockId = $this->getCmsBlockId()) {
			try {
				$block = $this->blockRepository->getById($blockId);
				$content = $block->getContent();
			} catch (LocalizedException $e) {
				$content = false;
			}
		}
		return $content;
	}
}