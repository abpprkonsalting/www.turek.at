<?php
namespace Magecomp\Instagramshoppable\Model\Source;

class Updatetype
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label'=>__('Users')],
            ['value' => 0, 'label'=>__('Hashtags')],
        ];
    }
}