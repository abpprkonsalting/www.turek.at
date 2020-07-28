<?php
namespace Magecomp\Instagramshoppable\Helper\Image;

use Magento\Framework\App\Helper\Context;
use Magecomp\Instagramshoppable\Helper\Image;
use Magecomp\Instagramshoppable\Model\InstagramshoppableimageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class Hashtag extends Image
{
    protected $_modelInstagramshoppableimageFactory;
	protected $_WriterInterface;
	protected $_storeManager;

    public function __construct(Context $context, 
		WriterInterface $WriterInterface,
        InstagramshoppableimageFactory $modelInstagramshoppableimageFactory,
		StoreManagerInterface $storeManager)
    {
		$this->_WriterInterface = $WriterInterface;
        $this->_modelInstagramshoppableimageFactory = $modelInstagramshoppableimageFactory;
		$this->_storeManager = $storeManager;
        parent::__construct($context, $WriterInterface, $storeManager);
    }
    public function update($storeid)
    {
        $responseStatus = true;
		$ImageFatch = $this->getFetchImageCount($storeid);
		if($ImageFatch == '' || $ImageFatch <= 0)
		{
			$ImageFatch = 100;
		}
		$Hashtaglist = $this->getTags($storeid);
		foreach($Hashtaglist as $hashid)
		{
			$hashid = trim($hashid);
			if($hashid != '') :
				$baseUrl = $this->getTagsURL($hashid);
				$url = $baseUrl;
				$total_image = 0;
				while(1)
				{
					$insta_source = file_get_contents($url); // instagrame tag url
					$insta_array = json_decode($insta_source, TRUE);
					$i = 0;
					while($i < count($insta_array['graphql']['hashtag']['edge_hashtag_to_media']['edges']))
					{
						$data_array = $insta_array['graphql']['hashtag']['edge_hashtag_to_media']['edges'][$i]['node'];
						if(!array_key_exists('caption',$data_array))
						{
							$data_array['caption'] = '';
						}
						if($data_array['is_video'])
						{
							$code=$data_array['shortcode'];
							$vedio_url='https://www.instagram.com/p/'.$code.'/?__a=1';

							$a1 = file_get_contents($vedio_url);
							$b1 = json_decode($a1, TRUE);
						
							$media_type='video';
							$media_url=$b1['graphql']['shortcode_media']['video_url'];
						}
						else
						{
							$media_type='image';
							$media_url=$data_array['thumbnail_src'];
						}
						
						$image = $this->_modelInstagramshoppableimageFactory->create();
						$image->setThumbnailUrl($media_url)
							->setStandardResolutionUrl($media_url)
							->setImageId($data_array['shortcode'])
							->setUsername($hashid)
							->setCaptionText($data_array['edge_media_to_caption']['edges'][0]['node']['text'])
							->setMediaType($media_type)
							->setTag($hashid)
							->setImageLikes($data_array['edge_liked_by']['count'])
							->setImageComments($data_array['edge_media_to_comment']['count'])
							->save();
						$i++;
						$total_image++;
						if($total_image >= $ImageFatch)
						{
							break;
						}
					}
					if(!$insta_array['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['has_next_page']) break;
					$url = $baseUrl.'&max_id='.$insta_array['graphql']['hashtag']['edge_hashtag_to_media']['page_info']['end_cursor'];
					if($total_image >= $ImageFatch)
					{
						break;
					}
				}
			endif;
		}
        return $responseStatus;
    }
}