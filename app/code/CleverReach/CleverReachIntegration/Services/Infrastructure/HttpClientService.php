<?php

namespace CleverReach\CleverReachIntegration\Services\Infrastructure;

use CleverReach\CleverReachIntegration\IntegrationCore\BusinessLogic\DTO\OptionsDTO;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\Configuration;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Interfaces\Required\HttpClient;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\HttpResponse;
use CleverReach\CleverReachIntegration\Helper\CurlHelper;

class HttpClientService extends HttpClient
{
    private $configService;
    private $curlHelper;

    public function __construct(CurlHelper $curlHelper)
    {
        $this->curlHelper = $curlHelper;
    }

    /**
     * Creates and sends request
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $body In JSON format
     * @return HttpResponse
     * @throws HttpCommunicationException Only in situation when there is no connection, no response, throw this exception
     */
    public function sendHttpRequest($method, $url, $headers = [], $body = '')
    {
        return $this->curlHelper->sendHttpRequest($method, $url, $this->getFixedHeaders($headers), $body);
    }

    /**
     * Creates and sends request asynchronously
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $body In JSON format
     */
    public function sendHttpRequestAsync($method, $url, $headers = [], $body = '')
    {
        $this->curlHelper->sendHttpRequestAsync($method, $url, $this->getFixedHeaders($headers), $body);
    }

    /**
     * Get additional options for request
     *
     * @return array OptionsDTO
     */
    protected function getAdditionalOptions()
    {
        $combinations = [];
        $combinations[] = [new OptionsDTO(CURLOPT_FOLLOWLOCATION, 1)];
        $combinations[] = [new OptionsDTO(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4)];
        $combinations[] = [new OptionsDTO(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6)];

        return $combinations;
    }

    /**
     * Save additional options for request
     *
     * @param $optionsDTO
     */
    protected function setAdditionalOptions($optionsDTO)
    {
        $options = [];
        /** @var OptionsDTO $optionDTO */
        foreach ($optionsDTO as $optionDTO) {
            $options[$optionDTO->getName()] = $optionDTO->getValue();
        }
        
        $this->getConfigService()->setCurlAdditionalOptions($options);
    }

    /**
     * Reset additional options for request to default value
     */
    protected function resetAdditionalOptions()
    {
        $this->getConfigService()->resetCurlAdditionalOptions();
    }

    private function getFixedHeaders($headers)
    {
        $newHeaders = [];

        foreach ($headers as $header) {
            // First element of this array is key and second is value for header
            $headerArray = explode(':', $header);
            $newHeaders[$headerArray[0]] = $headerArray[1];
        }

        return $newHeaders;
    }

    /**
     * @return ConfigService
     */
    private function getConfigService()
    {
        if (empty($this->configService)) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
