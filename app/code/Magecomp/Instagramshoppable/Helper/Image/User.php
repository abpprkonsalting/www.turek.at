<?php
namespace Magecomp\Instagramshoppable\Helper\Image;

use Magento\Framework\App\Helper\Context;
use Magecomp\Instagramshoppable\Helper\Data as HelperData;
use Magecomp\Instagramshoppable\Helper\Image;
use Magecomp\Instagramshoppable\Model\InstagramauthFactory;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class User extends Image
{
    const INSTAGRAM_API_USER_MEDIA_URL = 'https://api.instagram.com/v1/users/%userId%/media/recent/';
    const INSTAGRAM_API_USER_ID_URL = 'https://api.instagram.com/v1/users/search?q=%userId%';

    protected $_helperData;
    protected $_modelInstagramauthFactory;
	protected $_storeManager;
    protected $_modelInstagramimageFactory;
	protected $_WriterInterface;

    public function __construct(Context $context, 
        HelperData $helperData, 
		WriterInterface $WriterInterface,
        InstagramauthFactory $modelInstagramauthFactory,
		StoreManagerInterface $storeManager, 
        InstagramshoppableimageFactory $modelInstagramimageFactory)
    {
        $this->_helperData = $helperData;
		$this->_WriterInterface = $WriterInterface;
        $this->_modelInstagramauthFactory = $modelInstagramauthFactory;
        $this->_modelInstagramimageFactory = $modelInstagramimageFactory;
		$this->_storeManager = $storeManager;

        parent::__construct($context, $WriterInterface, $storeManager);
    }
    public function update()
    {
        $responseStatus = true;
        foreach ($this->getUsers(false) as $userId) {
            $user_id = $this->getUserId($userId);
            if ($user_id)
            {
                $endpointUrl = $this->getEndpointUrl($user_id);
            }
            else
            {
                $endpointUrl = $this->getEndpointUrl($userId);
            }
            $response = $this->getImages($endpointUrl, '@' . $userId);
            if(isset($response['error'])){
                $responseStatus = false;
            }
        }
        return $responseStatus;
    }
    public function getUserId($userId)
    {
        $url = $this->getSearchEndpointUrl($userId);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($output);
        if (isset($data->data))
        {
            $data2 = $data->data;
            if (isset($data2[0])) {
                $data3 = $data2[0];
                if (isset($data3->id)) {
                    return $data3->id;
                }
            }
        }
        return null;
    }
    public function getUsers($withPrefix = true)
    {
        $rawUsers = $this->scopeConfig->getValue('instagramshoppable/module_options/users', ScopeInterface::SCOPE_STORE);
        $users = explode(',', $rawUsers);
        $out = [];
        foreach ($users as $user) {
            $user = ltrim(trim($user), '@');
            if (!empty($user)) {
                if($withPrefix){
                    $out[] = '@' . $user;
                } else {
                    $out[] = $user;
                }
            }
        }
        return $out;
    }
    public function getSearchEndpointUrl($userId)
    {
        $endpointUrl = str_replace('%userId%', $userId, self::INSTAGRAM_API_USER_ID_URL);
		$accessToken = $this->scopeConfig->getValue('instagramshoppable/module_options/access_token', ScopeInterface::SCOPE_STORE);
        $endpointUrl = $endpointUrl . '&access_token=' . $accessToken;
		
		return $endpointUrl;
    }

    public function getEndpointUrl($userId)
    {
        $endpointUrl = str_replace('%userId%', $userId, self::INSTAGRAM_API_USER_MEDIA_URL);
        $helper = $this->_helperData;
		$accessToken = $this->scopeConfig->getValue('instagramshoppable/module_options/access_token', ScopeInterface::SCOPE_STORE);
        $endpointUrl = $helper->buildUrl($endpointUrl, [
            'access_token'  => $accessToken,
        ]);
		
        return $endpointUrl;
    }

    protected function getImages($endpointUrl, $tag)
    {
		$tag = ltrim($tag,'@');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($output);

        unset($output);
        $out = [];
        if (!isset($data->meta, $data->meta->code) || $data->meta->code !== 200) {
            $out['error'] = __("Instagram connect error");
            return $out;
        }
        if (!isset($data->data)) {
            $out['error'] = __("Instagram data not founded");
            return $out;
        }
        if (isset($data->pagination->next_url)) {
            $out['nextUrl'] = $data->pagination->next_url;
        }
        if (isset($data->pagination->next_max_tag_id)) {
            $out['nextMaxId'] = $data->pagination->next_max_tag_id;
        }
        foreach ($data->data as $item)
        {
			$username    = $item->user->username;
			$standardResolutionUrl = $item->images->standard_resolution->url;
            $lowResolutionUrl      = $item->images->low_resolution->url;
            $systemId              = $item->id;
			$media_type            = $item->type;
			$likes                 = $item->likes->count;
			$comments              = $item->comments->count;
			$link 				   = $item->link;
			
			$captionText = '';
            if($item->caption){
                $captionText = $item->caption->text;
            }
			if($media_type=='video')
			{
				$standardResolutionUrl = $item->videos->standard_resolution->url;
            	$lowResolutionUrl      = $item->videos->low_bandwidth->url;
			}
			elseif($media_type=='carousel')
			{
				$media_type='image';
			}
            $image = $this->_modelInstagramimageFactory->create();
            $image->setThumbnailUrl($lowResolutionUrl)
                ->setStandardResolutionUrl($standardResolutionUrl)
                ->setImageId($systemId)
                ->setUsername($username)
                ->setCaptionText($captionText)
				->setImageDesc($captionText)
				->setMediaType($media_type)
				->setImageLikes($likes)
				->setImageComments($comments)
				->setLink($link)
                ->setTag($tag)
                ->save();
        }
        return $out;
    }
}