<?php
namespace Magecomp\Instagramshoppable\Block\Catalog\Product\View;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Helper\Product;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class Detail extends Template
{
    protected $_helperData;
    protected $_helperProduct;
	protected $_collection = [];
	protected $registry;

    public function __construct(Context $context,
		HelperData $helperData, 
        Product $helperProduct,
		Registry $registry,
		array $data = [])
    {
		$this->registry = $registry;
        $this->_helperData = $helperData;
        $this->_helperProduct = $helperProduct;
		parent::__construct($context, $data);
    }
	public function showInstagramshoppableImages()
	{
		$helper = $this->_helperData;
		return ($helper->isEnabled() && $helper->showImagesOnProductPage() && count($this->getInstagramshoppableGalleryImages()) > 0);
	}
	public function Isshowindetailsection()
	{
		return $this->_helperData->getImageOnProductsection();
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