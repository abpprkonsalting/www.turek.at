<?php
namespace Magecomp\Instagramshoppable\Controller\Gallery;

use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\Action\Context;
use Magecomp\Instagramshoppable\Helper\Image;

class Popuphtml extends \Magento\Framework\App\Action\Action
{
    protected $_modelInstagramshoppableimageFactory;
    protected $_helperData;
	protected $_helperImage;
	protected $_productloader;
	protected $productimghelper;
	
    public function __construct(Context $context, 
        Product $helperProduct, 
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
        HelperData $helperData,
		Image $helperImage,
		\Magento\Catalog\Model\Product $productloader,
		\Magento\Catalog\Helper\Image $producthelper)
    {
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
        $this->_helperData = $helperData;
		$this->_helperImage = $helperImage;
		$this->_productloader = $productloader;
		$this->productimghelper = $producthelper;
        parent::__construct($context);
    }
	public function execute()
	{
		try
		{
			if($_POST['Id'] != '')
			{
				$html = '';	
				$image = $this->_modelInstagramshoppableimageFactory->create()->load($_POST['Id']);
				if($image->getLink()!='')
				{
					$insta_url=$image->getLink().'?__a=1';
				}
				else
				{
					$insta_url='https://www.instagram.com/p/'.$_POST['Id'].'/?__a=1';	
				}

				$a1 = file_get_contents($insta_url);
				$b1 = json_decode($a1, TRUE);
				
				$instaArr=$b1['graphql']['shortcode_media'];
				
				$html .= "<div id='loadingdiv' style='display:none'></div>";
				$html .= "<div id='whitebgdiv'>";
				
				if($this->_helperData->shownpInPopup()) :
				$html .= "<div id='prevbtndiv' onclick='prevnextpopup(1);'></div>";
				$html .= "<div id='nextbtndiv' onclick='prevnextpopup(2);'></div>";
				endif;
				//$html .= "<div id='closebtndiv' onclick='closepopup();'></div>";
				
				$html .= "<div id='leftpart'>"; // LEFT-PART START
				if($image->getMediaType() == "image" ){
					if (array_key_exists("edge_sidecar_to_children",$instaArr))
					{
						foreach($instaArr['edge_sidecar_to_children']['edges'] as $node)
						{
							$imgUrl   = $node['node']['display_resources'][0]['src'];
							$html .= "<img class='mySlides'  alt='".$image->getImageTitle()."' src='".$imgUrl."' />";
						}
						
						$html .= '<i  onclick="plusDivs(-1)" class="fa fa-arrow-circle-left" aria-hidden="true"></i>';
						$html .= '<i  onclick="plusDivs(+1)" class="fa fa-arrow-circle-right" aria-hidden="true"></i>';
					}
					else
					{
					  $html .= "<img alt='".$image->getImageTitle()."' src='".$image->getStandardResolutionUrl()."' />";
					}
				}
				else{
					$html .= " <video width='97%'  controls>
                	<source src='". $image->getThumbnailUrl() ."' type='video/mp4'>
                	Your browser does not support the video tag.
                </video>";
				}
				$html .= "<span id='titletext'></span>";
            	$html .= "</div>"; // LEFT-PART END
				
				$html .= "<div id='rightpart'>"; // RIGHT-PART START

				// USER DATA ADDED TO THIS (Start)
				$instaArr = $b1['graphql']['shortcode_media'];
				$html.=" <div class='main-user-div'><div class='user'><div class='userimage'><img src='".$instaArr['owner']['profile_pic_url']."' alt='".$instaArr['owner']['username']."'></div> <div class='userlink'><a target='_blank' href='https://www.instagram.com/".$instaArr['owner']['username']."'>".$instaArr['owner']['username']."</a></div></div></div>";
				// USER DATA ADDED TO THIS (End)

				if($this->_helperImage->getPopupConfiguration()) : // SHOW PRODUCT IN POPUP
					$html .= "<ul class='alltitleul prditem'>";
					if($image['product_id_1'] != '' && $image['product_id_1'] != 0) :
						$product = $this->_productloader->load($image['product_id_1']);
						$html .= "<li class='".$image['title1x']."-".$image['title1y']."'>";
						$html .= "<div id='prditemdiv1'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname1'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					if($image['product_id_2'] != '' && $image['product_id_2'] != 0) :
						$product = $this->_productloader->load($image['product_id_2']);
						$html .= "<li class='".$image['title2x']."-".$image['title2y']."'>";
						$html .= "<div id='prditemdiv2'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname2'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					if($image['product_id_3'] != '' && $image['product_id_3'] != 0) :
						$product = $this->_productloader->load($image['product_id_3']);
						$html .= "<li class='".$image['title3x']."-".$image['title3y']."'>";
						$html .= "<div id='prditemdiv3'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname3'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					if($image['product_id_4'] != '' && $image['product_id_4'] != 0) :
						$product = $this->_productloader->load($image['product_id_4']);
						$html .= "<li class='".$image['title4x']."-".$image['title4y']."'>";
						$html .= "<div id='prditemdiv4'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname4'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					if($image['product_id_5'] != '' && $image['product_id_5'] != 0) :
						$product = $this->_productloader->load($image['product_id_5']);
						$html .= "<li class='".$image['title5x']."-".$image['title5y']."'>";
						$html .= "<div id='prditemdiv5'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname5'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					if($image['product_id_6'] != '' && $image['product_id_6'] != 0) :
						$product = $this->_productloader->load($image['product_id_6']);
						$html .= "<li class='".$image['title6x']."-".$image['title6y']."'>";
						$html .= "<div id='prditemdiv6'>";
						$html .= "<a>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</div>";
						$html .= "<a href='".$product->getProductUrl()."' target='_blank' title='".$product->getName()."' class='prdblocka'>";
						$html .= "<img src='".$this->productimghelper->init($product, 'category_page_list')->constrainOnly(FALSE)->keepAspectRatio(FALSE)->keepFrame(FALSE)->resize(120,90)->getUrl()."' />";
						$html .= "<span id='prdname6'>".$product->getName()."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					
					$html .= "</ul>";	
				else : // SHOW TITLE WITH LINKS IN POPUP
				$html .= "<ul class='alltitleul'>";
					if($image['title1'] != '') :
						$html .= "<li class='".$image['title1x']."-".$image['title1y']."'>";
						$html .= "<a href='".$image['titlelink1']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title1']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					if($image['title2'] != '') :
						$html .= "<li class='".$image['title2x']."-".$image['title2y']."'>";
						$html .= "<a href='".$image['titlelink2']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title2']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					if($image['title3'] != '') :
						$html .= "<li class='".$image['title3x']."-".$image['title3y']."'>";
						$html .= "<a href='".$image['titlelink3']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title3']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					if($image['title4'] != '') :
						$html .= "<li class='".$image['title4x']."-".$image['title4y']."'>";
						$html .= "<a href='".$image['titlelink4']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title4']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					if($image['title5'] != '') :
						$html .= "<li class='".$image['title5x']."-".$image['title5y']."'>";
						$html .= "<a href='".$image['titlelink5']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title5']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					if($image['title6'] != '') :
						$html .= "<li class='".$image['title6x']."-".$image['title6y']."'>";
						$html .= "<a href='".$image['titlelink6']."' target='_blank'>";
						$html .= "<span class='number'>&nbsp;</span>";
						$html .= "<span class='numbertitle'> ".$image['title6']."</span>";
						$html .= "</a>";
						$html .= "</li>";
					endif;
					$html .= "</ul>";	
				endif;
				
				$html .= "<div id='instatitle'>".$image->getImageDesc()."</div>";
				
				$html .= "</div>"; // RIGHT-PART END
				
				$html .= "</div>";
				echo $html;
			}
		}
		catch(\Exception $e)
		{
			echo $e->getMessage();	
		}
	}
}