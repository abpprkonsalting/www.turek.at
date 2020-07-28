<?php

namespace CleverReach\CleverReachIntegration\Services\BusinessLogic;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Attributes;

class AttributesService implements Attributes
{
    private $attributes = [
        'email' => [
            'description' => 'Email',
        ],
        'salutation' => [
            'description' => 'Name Prefix',
        ],
        'firstname' => [
            'description' => 'First Name',
        ],
        'lastname' => [
            'description' => 'Last Name',
        ],
        'birthday' => [
            'description' => 'Date of Birth',
        ],
        'shop' => [
            'description' => 'Shop',
        ],
        'customernumber' => [
            'description' => 'ID',
        ],
        'street' => [
            'description' => 'Street Address',
        ],
        'zip' => [
            'description' => 'Zip/Postal Code',
        ],
        'city' => [
            'description' => 'City',
        ],
        'company' => [
            'description' => 'Company',
        ],
        'state' => [
            'description' => 'State/Province',
        ],
        'country' => [
            'description' => 'Country',
        ],
        'phone' => [
            'description' => 'Phone Number',
        ],
        'newsletter' => [
            'description' => 'isSubscribed',
        ]
    ];

    public function getAttributeByName($attributeName)
    {
        $attribute = new \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\ShopAttribute();
        $mappedAttribute = $this->getMappedAttribute($attributeName);

        if (!empty($mappedAttribute)) {
            $attribute->setDescription($this->translate($mappedAttribute['description']));
        }

        return $attribute;
    }

    private function getMappedAttribute($attributeName)
    {
        if (!empty($this->attributes[$attributeName])) {
            return $this->attributes[$attributeName];
        }

        return [];
    }

    private function translate($string)
    {
        $translation = __($string);

        return $translation->render();
    }
}
