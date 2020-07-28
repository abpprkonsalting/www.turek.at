<?php


namespace CleverReach\CleverReachIntegration\Controller\Refresh;

use CleverReach\CleverReachIntegration\Helper\Url;
use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Interfaces\Proxy;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Exceptions\BadAuthInfoException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use CleverReach\CleverReachIntegration\Services\Infrastructure\ConfigService;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use Magento\Framework\App\Action\Action;

class Callback extends Action
{
    private $resultJsonFactory;

    private $resultPageFactory;

    /** @var \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy */
    private $proxy;

    /** @var ConfigService */
    private $configService;

    /**
     * @var Url
     */
    private $urlHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Url $urlHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlHelper = $urlHelper;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws HttpCommunicationException
     * @throws \CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');

        if (empty($code)) {
            return $this->resultJsonFactory->create()->setData([
                'status' => false,
                'message' => __('Wrong parameters. Code not set.'),
            ]);
        }

        $redirectUrl = $this->urlHelper->getFrontUrl('cleverreach/refresh/callback/');
        try {
            $authInfo = $this->getProxy()->getAuthInfo($code, $redirectUrl);
        } catch (BadAuthInfoException $e) {
            return $this->resultJsonFactory->create()->setData([
                'status' => false,
                'message' => $e->getMessage() ? $e->getMessage() : __('Unsuccessful connection.'),
            ]);
        }

        if ($this->isUserInfoValid($authInfo->getAccessToken())) {
            $configService = $this->getConfigService();
            $configService->setAccessToken($authInfo->getAccessToken());
            $configService->setAccessTokenExpirationTime($authInfo->getAccessTokenDuration());
            $configService->setRefreshToken($authInfo->getRefreshToken());
        }

        return $this->resultPageFactory->create();
    }

    /**
     * Retrieves proxy.
     *
     * @return \CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\Proxy
     */
    private function getProxy()
    {
        if ($this->proxy === null) {
            $this->proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
        }

        return $this->proxy;
    }

    /**
     * Checks whether UserInfo
     *
     * @param string $accessToken
     * @return bool
     */
    private function isUserInfoValid($accessToken)
    {
        try {
            $apiUserInfo = $this->getProxy()->getUserInfo($accessToken);
        } catch (\Exception $e) {
            return false;
        }

        $dbUserInfo = $this->getConfigService()->getUserInfo();

        return !empty($apiUserInfo['id']) && ($apiUserInfo['id'] === $dbUserInfo['id']);
    }

    /**
     * Retrieves ConfigService.
     *
     * @return ConfigService
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
