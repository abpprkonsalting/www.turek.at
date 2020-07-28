<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Entity\AuthInfo;

/**
 * Class RefreshUserInfoTask
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Sync
 */
class RefreshUserInfoTask extends BaseSyncTask
{
    /**
     * Authentication info.
     *
     * @var AuthInfo
     */
    private $authInfo;

    /**
     * RefreshUserInfoTask constructor.
     *
     * @param AuthInfo $authInfo Authentication data.
     */
    public function __construct(AuthInfo $authInfo)
    {
        $this->authInfo = $authInfo;
    }

    /**
     * String representation of object
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this->authInfo);
    }

    /**
     * Constructs the object.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->authInfo = unserialize($serialized);
    }

    /**
     * Runs task execution.
     *
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\InvalidConfigurationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpRequestException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\RefreshTokenExpiredException
     */
    public function execute()
    {
        $this->reportProgress(5);

        $configService = $this->getConfigService();
        $userInfo = $this->getProxy()->getUserInfo($this->authInfo->getAccessToken());
        if (!empty($userInfo)) {
            $configService->setAuthInfo($this->authInfo);
            $configService->setUserInfo($userInfo);
        }

        $this->reportProgress(100);
    }
}
