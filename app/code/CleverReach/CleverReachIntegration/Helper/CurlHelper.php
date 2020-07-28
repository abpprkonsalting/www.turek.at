<?php

namespace CleverReach\CleverReachIntegration\Helper;

use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\HttpResponse;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use Magento\Framework\HTTP\Client\Curl;

class CurlHelper extends Curl
{
    const RESPONSE_STATUS_CONTINUE = 100;

    /**
     * @var Configuration
     */
    private $configService;

    /**
     * Creates and sends request.
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $body
     *
     * @return HttpResponse
     * @throws HttpCommunicationException
     */
    public function sendHttpRequest($method, $url, $headers = [], $body = '')
    {
        $this->removeCurlOptions();
        $this->setHeaders($headers);

        $curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        $curlAdditionalOptions = $this->getConfigService()->getCurlAdditionalOptions();
        if (!empty($curlAdditionalOptions)) {
            $curlOptions += $curlAdditionalOptions;
        }

        if ($method === 'POST') {
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        try {
            $this->setOptions($curlOptions);
            $this->makeRequest($method, $url);
        } catch (\Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new HttpCommunicationException('Request ' . $url . ' failed.', 0, $e);
        }

        return new HttpResponse($this->getStatus(), $this->getHeaders(), $this->getBody());
    }

    /**
     * Creates and sends request asynchronously.
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $body
     *
     * @return bool
     */
    public function sendHttpRequestAsync($method, $url, $headers = [], $body = '')
    {
        $this->_ch = curl_init();

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_TIMEOUT_MS => $this->getConfigService()->getAsyncProcessRequestTimeout(),
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($method === 'DELETE' || $method === 'PUT') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        if ($method === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        $curlAdditionalOptions = $this->getConfigService()->getCurlAdditionalOptions();
        if (!empty($curlAdditionalOptions)) {
            $curlOptions += $curlAdditionalOptions;
        }

        $this->curlOptions($curlOptions);

        return curl_exec($this->_ch);
    }

    /**
     * Parse headers - CURL callback function
     *
     * @param resource $ch curl handle, not needed
     * @param string $data
     *
     * @return int
     * @throws \Exception
     */
    protected function parseHeaders($ch, $data)
    {
        $this->resetHeaderCountIfResponseStatusIsContinue();

        return parent::parseHeaders($ch, $data);
    }

    private function resetHeaderCountIfResponseStatusIsContinue()
    {
        if ($this->_responseStatus === self::RESPONSE_STATUS_CONTINUE && $this->_headerCount === 2) {
            $this->_headerCount = 0;
        }
    }

    private function getConfigService()
    {
        if (null === $this->configService) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * Removes all user defined curl options.
     */
    private function removeCurlOptions()
    {
        $this->_curlUserOptions = [];
    }
}
