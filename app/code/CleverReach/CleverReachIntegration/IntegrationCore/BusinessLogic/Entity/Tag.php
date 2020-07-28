<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity;

/**
 * Class Tag
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity
 */
class Tag extends AbstractTag
{
    /**
     * Tag constructor.
     *
     * @param string $name Tag name.
     * @param string $type Tag type.
     */
    public function __construct($name, $type)
    {
        parent::__construct($name, $type);
    }
}
