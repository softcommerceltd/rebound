<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Http;

use GuzzleHttp;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\StreamInterface;
use SoftCommerce\Rebound\Logger\Logger;
use SoftCommerce\Rebound\Model\ConfigInterface;

/**
 * Class Client
 * @package SoftCommerce\Rebound\Http
 */
class Client implements ClientInterface, ReboundServerInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var Response|null
     */
    private ?Response $response = null;

    /**
     * @var string|int|null
     */
    private $responseStatusCode;

    /**
     * @var StreamInterface|null
     */
    private ?StreamInterface $responseBody = null;

    /**
     * @var string|null
     */
    private ?string $responseContents = null;

    /**
     * @var array
     */
    private array $request = [];

    /**
     * @var array
     */
    private array $params = [];

    /**
     * @var string|null
     */
    private ?string $requestUri = null;

    /**
     * @var int
     */
    private int $requestNo = 0;

    /**
     * @var string|null
     */
    private ?string $userName;

    /**
     * @var string|null
     */
    private ?string $accessToken;

    /**
     * Client constructor.
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ConfigInterface $config
     * @param Logger $logger
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(
        GuzzleHttp\ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ConfigInterface $config,
        Logger $logger,
        Json $serializer,
        array $data = []
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->userName = $data[self::REQUEST_LOGIN] ?? null;
        $this->accessToken = $data[self::REQUEST_API_KEY] ?? null;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response ?: $this->responseFactory->create();
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatusCode;
    }

    /**
     * @param string|int $statusCode
     * @return $this
     */
    public function setResponseStatusCode($statusCode)
    {
        $this->responseStatusCode = $statusCode;
        return $this;
    }

    /**
     * @return StreamInterface
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @param StreamInterface $body
     * @return $this
     */
    public function setResponseBody(StreamInterface $body)
    {
        $this->responseBody = $body;
        return $this;
    }

    /**
     * @param bool $decoded
     * @return array|bool|float|int|mixed|string|null
     */
    public function getResponseContents($decoded = true)
    {
        return false === $decoded
            ? $this->responseContents
            : $this->serializer->unserialize($this->responseContents ?: '[]');
    }

    /**
     * @param string $contents
     * @return $this
     */
    public function setResponseContents(string $contents)
    {
        $this->responseContents = $contents;
        return $this;
    }

    /**
     * @return array|string|mixed
     */
    public function getRequest()
    {
        return $this->request ?: [];
    }

    /**
     * @param string|array|mixed $data
     * @return $this
     */
    public function setRequest($data)
    {
        $this->request = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setRequestUri(string $uri)
    {
        $this->requestUri = $uri;
        return $this;
    }

    /**
     * @return $this
     */
    public function executeBefore()
    {
        $this->response =
        $this->responseBody =
        $this->responseContents =
        $this->responseStatusCode =
        $this->requestUri =
            null;

        $this->params =
        $this->request =
            [];
        $this->requestNo += 1;

        return $this;
    }

    /**
     * @param string $uri
     * @param array $request
     * @param string $method
     * @return $this|Client
     * @throws LocalizedException
     */
    public function execute(
        string $uri,
        array $request = [],
        string $method = Request::HTTP_METHOD_POST
    ) {
        $this->executeBefore()
            ->setRequest($request)
            ->buildParams()
            ->setParams(
                [
                    GuzzleHttp\RequestOptions::DECODE_CONTENT => true,
                    GuzzleHttp\RequestOptions::FORM_PARAMS => $this->getParams()
                ]
            );

        try {
            $client = $this->clientFactory->create();
            $response = $client->request($method, $uri, $this->getParams());
        } catch (GuzzleException $e) {
            $this->log($e->getMessage());
            $response = $this->responseFactory->create([
                'status' => $e->getCode(),
                'reason' => $e->getMessage()
            ]);
        }

        if (!$body = $response->getBody()) {
            throw new LocalizedException(__('Could not retrieve response body.'));
        }

        if (!$contents = $body->getContents()) {
            throw new LocalizedException(__('Could not retrieve response contents.'));
        }

        $this->setResponse($response)
            ->setResponseStatusCode($response->getStatusCode())
            ->setResponseBody($body)
            ->setResponseContents($contents);

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function buildParams()
    {
        $this->setParams(
            array_merge(
                [
                    self::REQUEST_LOGIN => $this->getUserName(),
                    self::REQUEST_API_KEY => $this->getAccessToken()
                ],
                $this->getRequest()
                    ? ['request' => $this->serializer->serialize($this->getRequest())]
                    : []
            )
        );

        return $this;
    }

    /**
     * @return array|string|mixed
     */
    private function getParams()
    {
        return $this->params ?: [];
    }

    /**
     * @param string|array|mixed $data
     * @return $this
     */
    private function setParams($data)
    {
        $this->params = $data;
        return $this;
    }

    /**
     * @param $message
     * @param array $context
     * @param bool $force
     * @return $this
     */
    private function log($message, array $context = [], $force = false)
    {
        if (false === $force || !$this->config->getIsActiveDebug()) {
            return $this;
        }

        if ($this->config->getIsDebugPrintToArray()) {
            $this->logger->debug(print_r([$message => $context], true), []);
        } else {
            $this->logger->debug($message, $context);
        }

        return $this;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getUserName(): string
    {
        if (null === $this->userName) {
            throw new LocalizedException(__('User name is not set.'));
        }

        return $this->userName;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getAccessToken(): string
    {
        if (null === $this->accessToken) {
            throw new LocalizedException(__('Access token is not set.'));
        }

        return $this->accessToken;
    }
}
