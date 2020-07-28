<?php

namespace WeSupply\Toolbox\Controller\Orders;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

class Login extends Action
{
    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Login constructor.
     * @param Context $context
     * @param Helper $helper
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        Helper $helper,
        SessionManagerInterface $session
    )
    {
        $this->_helper = $helper;
        $this->_session = $session;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if (TRUE === $this->isRequiredAuthToken() && !$this->getAuthToken()) {
            // missing required token
            return $this->_redirect($this->_helper->getTrackingInfoUri());
        }

        if (
            $this->getAuthTokenFromParams() == $this->_session->getSessionAuthToken() &&
            $this->getAuthSearchByFromParams() == $this->_session->getSessionAuthSearchBy()
        ) {
            // same request (and already logged in)
            return $this->_redirect('wesupply/orders/view');
        }

        $this->resetAuthParams();

        return $this->_redirect('wesupply/orders/view');
    }

    /**
     * @return string|null
     */
    private function getAuthTokenFromParams()
    {
        return $this->isRequiredAuthToken() ?
            $this->getRequest()->getParam('token') ?? null :
            'not-required';
    }

    /**
     * @return array
     */
    private function getAuthSearchByFromParams()
    {
        if ($this->getRequest()->getParam('embedded-oid')) {
            return ['embedded-oid' => $this->getRequest()->getParam('embedded-oid')];
        }

        if ($this->getRequest()->getParam('embedded-em')) {
            return ['embedded-em' => $this->getRequest()->getParam('embedded-em')];
        }

        return [];
    }

    /**
     * @return string|null
     */
    private function getAliasDomainFromParams()
    {
        return $this->getRequest()->getParam('domain') ?? null;
    }

    /**
     * @return string|null
     */
    private function getAuthToken()
    {
        if ($this->getAuthTokenFromParams()) {
            return $this->getAuthTokenFromParams();
        }

        return $this->_session->getSessionAuthToken();
    }

    /**
     * @return bool
     */
    private function isRequiredAuthToken()
    {
        if (is_null($this->getRequest()->getParam('embedded-oid'))) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return void
     */
    private function resetAuthParams()
    {
        $this->_session->setFirstAttempt(true);
        $this->_session->setSessionAuthSearchBy($this->getAuthSearchByFromParams());
        $this->_session->setSessionAuthToken($this->getAuthTokenFromParams());
        $this->_session->setSessionAliasDomain($this->getAliasDomainFromParams());
    }
}