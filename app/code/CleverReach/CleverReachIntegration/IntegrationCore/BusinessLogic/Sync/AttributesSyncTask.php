<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\ShopAttribute;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Attributes;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class AttributesSyncTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 */
class AttributesSyncTask extends BaseSyncTask
{
    const INITIAL_PROGRESS_PERCENT = 5;
    /**
     * Array of global attribute IDs on CleverReach.
     *
     * @var array
     */
    private $globalAttributesIdsFromCR;

    /**
     * String representation of object
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this->globalAttributesIdsFromCR);
    }

    /**
     * Constructs the object.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->globalAttributesIdsFromCR = unserialize($serialized);
    }

    /**
     * Runs task execution.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    public function execute()
    {
        $globalAttributes = $this->getGlobalAttributesIdsFromCleverReach();
        // After retrieving all global attributes set initial progress
        $progressPercent = self::INITIAL_PROGRESS_PERCENT;
        $this->reportProgress($progressPercent);

        $attributesToSend = $this->getAllAttributes();

        // Calculate progress step after setting initially progress
        $totalAttributes = count($attributesToSend);
        $progressStep = (100 - self::INITIAL_PROGRESS_PERCENT) / $totalAttributes;
        $i = 0;
        foreach ($attributesToSend as $attribute) {
            $i++;
            if (isset($globalAttributes[$attribute['name']])) {
                $attributeIdOnCR = $globalAttributes[$attribute['name']];
                $this->getProxy()->updateGlobalAttribute($attributeIdOnCR, $attribute);
            } else {
                $this->getProxy()->createGlobalAttribute($attribute);
            }

            $progressPercent += $progressStep;
            if ($i === $totalAttributes) {
                $this->reportProgress(100);
            } else {
                $this->reportProgress($progressPercent);
            }
        }
    }

    /**
     * Get global attributes IDs from CleverReach.
     *
     * @return array
     *   Array of global attribute IDs on CleverReach.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    private function getGlobalAttributesIdsFromCleverReach()
    {
        if (empty($this->globalAttributesIdsFromCR)) {
            $this->globalAttributesIdsFromCR = $this->getProxy()->getAllGlobalAttributes();
        }

        return $this->globalAttributesIdsFromCR;
    }

    /**
     * Return all global attributes formatted for sending to CleverReach.
     *
     * Example:
     * [
     *   [
     *     'name' => 'email',
     *     'type' => 'text',
     *   ],
     *   [
     *     'name' => 'birthday',
     *     'type' => 'date',
     *   ]
     * ]
     *
     * @return array
     *   Array of global attributes supported by integration.
     */
    private function getAllAttributes()
    {
        /** @var Attributes $attributesService */
        $attributesService = ServiceRegister::getService(Attributes::CLASS_NAME);
        $attributes = [
            [
                'name' => 'email',
                'type' => 'text',
            ],
            [
                'name' => 'salutation',
                'type' => 'text',
            ],
            [
                'name' => 'title',
                'type' => 'text',
            ],
            [
                'name' => 'firstname',
                'type' => 'text',
            ],
            [
                'name' => 'lastname',
                'type' => 'text',
            ],
            [
                'name' => 'street',
                'type' => 'text',
            ],
            [
                'name' => 'zip',
                'type' => 'text',
            ],
            [
                'name' => 'city',
                'type' => 'text',
            ],
            [
                'name' => 'company',
                'type' => 'text',
            ],
            [
                'name' => 'state',
                'type' => 'text',
            ],
            [
                'name' => 'country',
                'type' => 'text',
            ],
            [
                'name' => 'birthday',
                'type' => 'date',
            ],
            [
                'name' => 'phone',
                'type' => 'text',
            ],
            [
                'name' => 'shop',
                'type' => 'text',
            ],
            [
                'name' => 'customernumber',
                'type' => 'text',
            ],
            [
                'name' => 'language',
                'type' => 'text',
            ],
            [
                'name' => 'newsletter',
                'type' => 'text',
            ],
        ];

        /** @var ShopAttribute $attribute */
        foreach ($attributes as &$attribute) {
            $shopAttribute = $attributesService->getAttributeByName($attribute['name']);
            $attribute['description'] = $shopAttribute->getDescription();
            $attribute['preview_value'] = $shopAttribute->getPreviewValue();
            $attribute['default_value'] = $shopAttribute->getDefaultValue();
        }

        return $attributes;
    }
}
