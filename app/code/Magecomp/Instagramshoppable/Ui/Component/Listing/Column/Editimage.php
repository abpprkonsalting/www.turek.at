<?php
namespace Magecomp\Instagramshoppable\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\Session;
class Editimage extends Column
{
	const ROW_EDIT_URL = 'instagramshoppable/shoppableindex/edit';
	protected $_urlBuilder;
    private $_editUrl;
	protected $_backendSession;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
		Session $backendSession,
        array $components = [],
        array $data = [],
        $editUrl = self::ROW_EDIT_URL
    ) 
    {
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
		$this->_backendSession = $backendSession;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
	
	public function getCurrentStoreID()
	{
		$cstsession = $this->_backendSession;
		if(strpos($this->_urlBuilder->getCurrentUrl(), 'store') !== false)
		{
			$urlarray = explode('/',$this->_urlBuilder->getCurrentUrl());
			if(in_array('store',$urlarray) && sizeof($_GET) == 0)
			{
				$key = array_search('store', $urlarray);
				$Storeid = $urlarray[$key+1];
				$cstsession->setMyStore($Storeid);		
			}
		}
		else
		{
			$cstsession->setMyStore(0);	
		}
		return $cstsession->getMyStore();
	}
	
	public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) 
		{
            foreach ($dataSource['data']['items'] as &$item) 
			{
                $name = $this->getData('name');
                if (isset($item['image_id'])) 
				{
					$Storeid = $this->getCurrentStoreID();
					if($Storeid != '' && $Storeid > 0)
					{
						$this->_editUrl = $this->_editUrl."/store/".$Storeid; 
					}
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->_editUrl, 
                            ['id' => $item['image_id']]
                        ),
                        'label' => __('Edit'),
                    ];
                }
            }
        }
        return $dataSource;
    }
}