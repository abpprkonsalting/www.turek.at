<?php
namespace Magecomp\Instagramshoppable\Block\Adminhtml\Shoppableindex\Edit\Renderer;

use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;

class Thumbnail extends \Magento\Framework\Data\Form\Element\AbstractElement
{
	protected $blockdata;
	protected $request;
	protected $InstagramshoppableimageFactory;
	
	public function __construct(Factory $factoryElement,
	CollectionFactory $factoryCollection,
	Escaper $escaper,
	\Magecomp\Instagramshoppable\Block\Homepage $blockdata,
	\Magento\Framework\App\Request\Http $request,
	InstagramshoppableimageFactory $InstagramshoppableimageFactory)
    {
		$this->blockdata = $blockdata;
		$this->request = $request;
		$this->InstagramshoppableimageFactory = $InstagramshoppableimageFactory;
        parent::__construct($factoryElement,$factoryCollection,$escaper);
    }
	
    public function getAfterElementHtml()
    {
		$html = '';
		if ($this->getValue()) 
		{
			$html = '<p>Simply drag and drop each pin to automatically set top and left positions for each hotspot.</p>';
			$html .= '<div onMouseOver="move_init();" class="image-div" id="image-div" style="height:554px; width:554px;"><img id="'.$this->getHtmlId().'_image" style="border: 1px solid #d6d6d6;" title="'.$this->getValue().'" src="'.$this->getValue().'" alt="'.$this->getValue().'" width="552" height="552" />';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot6" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/6.png').'" alt="hotspot icon">';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot5" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/5.png').'" alt="hotspot icon">';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot4" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/4.png').'" alt="hotspot icon">';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot3" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/3.png').'" alt="hotspot icon">';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot2" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/2.png').'" alt="hotspot icon">';
			$html .= '<img onmousedown="_move_item(this);" class="hotspot-img" id="hotspot1" src="'.$this->blockdata->getViewFileUrl('Magecomp_Instagramshoppable::images/1.png').'" alt="hotspot icon">';
			$html .= '</div>';
			$html .= '<script type="text/javascript">
						  _item = null;
						  mouse_x = 0;
						  mouse_y = 0;
						  ele_x = 0;
						  ele_y = 0;

						  function move_init()
						  {
							document.onmousemove = _move;
							document.onmouseup = _stop;
						  }
						  function _stop(e)
						  {
							  if(_item != null)
							  { 
							     var xValue = _item.offsetTop + 15;
								 var yValue = _item.offsetLeft+ 25;
								 switch(_item.id)
								 {
									 case "hotspot1":
									 		document.getElementById("title1x").value=xValue;
											document.getElementById("title1y").value=yValue;
									 		break;
									 case "hotspot2":
									 		document.getElementById("title2x").value=xValue;
											document.getElementById("title2y").value=yValue;
									 		break;
									 case "hotspot3":
									 		document.getElementById("title3x").value=xValue;
											document.getElementById("title3y").value=yValue;
									 		break;					 
								     case "hotspot4":
									 		document.getElementById("title4x").value=xValue;
											document.getElementById("title4y").value=yValue;
									 		break;
									 case "hotspot5":
									 		document.getElementById("title5x").value=xValue;
											document.getElementById("title5y").value=yValue;
									 		break;
									 case "hotspot6":
									 		document.getElementById("title6x").value=xValue;
											document.getElementById("title6y").value=yValue;
									 		break;
								 }
							   }
							  _item = null;
						  }
						  function offset(el) 
						  {
							  var rect = el.getBoundingClientRect(),
							  scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
							  scrollTop = window.pageYOffset || document.documentElement.scrollTop;
							  return { top: rect.top + scrollTop, left: rect.left + scrollLeft }
                          }
						  function _move(e)
						  {
							var imgdiv = document.getElementById("image-div");
							var imagediv = offset(imgdiv);
						
							mouse_x = document.all ? window.event.clientX : e.pageX;
							mouse_y = document.all ? window.event.clientY : e.pageY;

							if(_item != null)
							{ 
							  _item.style.left = (mouse_x-(imagediv.left + 23)) + "px";
							  _item.style.top = (mouse_y-(imagediv.top + 43)) + "px";
							}
						  }
						  function _move_item(ele)
						  {
							_item = ele;
							ele_x = mouse_x - _item.offsetLeft;
							ele_y = mouse_y - _item.offsetTop;
						   }
						</script>';
						
			$html .= '<style> 
					 .image-div {position: relative;}
					 .hotspot-img {position: absolute;width:50px;height:50px}';
			
			$image = $this->InstagramshoppableimageFactory->create()->load($this->request->getParam('id'));
			  if($image['title1x'] > 0 || $image['title1y'] > 0) :
				  $html .= '#hotspot1 {left:'.($image['title1y']-25).'px;top:'.($image['title1x']-15).'px;}';	
			  else :
				  $html .= '#hotspot1 {right: -53px;top: 0;}';
			  endif;
			  
			  if($image['title2x'] > 0 || $image['title2y'] > 0) :
				  $html .= '#hotspot2 {left:'.($image['title2y']-25).'px;top:'.($image['title2x']-15).'px;}';	
			  else :
				  $html .= '#hotspot2 {right: -53px;top:55px;}';
			  endif;
			  
			  if($image['title3x'] > 0 || $image['title3y'] > 0) :
				  $html .= '#hotspot3 {left:'.($image['title3y']-25).'px;top:'.($image['title3x']-15).'px;}';	
			  else :
				  $html .= '#hotspot3 {right: -53px;top:110px;}';
			  endif;
			  
			  if($image['title4x'] > 0 || $image['title4y'] > 0) :
				  $html .= '#hotspot4 {left:'.($image['title4y']-25).'px;top:'.($image['title4x']-15).'px;}';	
			  else :
				  $html .= '#hotspot4 {right: -53px;top: 165px;}';
			  endif;
			  
			  if($image['title5x'] > 0 || $image['title5y'] > 0) :
				  $html .= '#hotspot5 {left:'.($image['title5y']-25).'px;top:'.($image['title5x']-15).'px;}';	
			  else :
				  $html .= '#hotspot5 {right: -53px;top: 220px;}';
			  endif;
			  
			  if($image['title6x'] > 0 || $image['title6y'] > 0) :
				  $html .= '#hotspot6 {left:'.($image['title6y']-25).'px;top:'.($image['title6x']-15).'px;}';	
			  else :
				  $html .= '#hotspot6 {right: -53px;top: 275px;}';
			  endif; 
			  
			  $html .= '.postion-div > input {padding: 3px;text-align: center;width: 50px;} 
					 .postion-div {margin-bottom: 10px;margin-top: 10px;}  
					 .postion-div {font-size: 13px;font-weight: bold;margin-bottom: 10px;margin-top: 10px;}
					 #thumbnail_url{display: none;}
		     </style>'; 
		}
        return $html;
    }
}