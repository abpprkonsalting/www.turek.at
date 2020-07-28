<?php
namespace Magecomp\Instagramshoppable\Block\Catalog\Product\View;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Helper\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\Registry;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
	protected $jsonEncoder;
	protected $_helperData;
	protected $_helperProduct;
	protected $registry;
	protected $_collection = [];
	
	public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
		HelperData $helperData,
		Product $helperProduct,
		Registry $registry,
        array $data = []
    )
	{
        $this->jsonEncoder = $jsonEncoder;
		$this->_helperData = $helperData;
		$this->_helperProduct = $helperProduct;
		$this->registry = $registry;
        parent::__construct($context, $arrayUtils, $jsonEncoder);
    }
	public function Isshowinmoreviewsection()
	{
		$helper = $this->_helperData;
		return ($helper->isEnabled() && $helper->showImagesOnProductPage() && $helper->getImageOnProductMoreViewsection() && count($this->getInstagramshoppableGalleryImages()) > 0);
	}
	
	public function getInstagramshoppableGalleryImages()
    {
		$product = $this->registry->registry('current_product');
        if (!$this->_collection)
		{
        	$this->_collection = $this->_helperProduct->getInstagramshoppableGalleryImages($product);
        }
        return $this->_collection;
    }
}