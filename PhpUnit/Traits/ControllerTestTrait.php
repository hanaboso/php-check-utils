<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use Apitte\Core\Dispatcher\IDispatcher;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Closure;
use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ServerRequest;
use JsonException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Trait ControllerTestTrait
 *
 * @package Hanaboso\PhpCheckUtils\PhpUnit\Traits
 */
trait ControllerTestTrait
{

    /**
     * @var KernelBrowser
     */
    protected KernelBrowser $client;

    /**
     * @var IDispatcher
     */
    protected IDispatcher $dispatcher;

    /**
     * @var Session
     */
    protected Session $session;

    /**
     * @var UsageTrackingTokenStorage
     */
    protected UsageTrackingTokenStorage $tokenStorage;

    /**
     * @var string
     */
    protected static string $HTTP = 'http';

    /**
     * @var string
     */
    protected static string $BODY = 'body';

    /**
     * @var string
     */
    protected static string $HEADERS = 'headers';

    /**
     * @var string
     */
    protected static string $STATUS = 'status';

    /**
     * @var string
     */
    protected static string $REQUEST = '#(GET|POST|PUT|PATCH|DELETE) /#';

    /**
     * @param array $options
     * @param array $server
     */
    protected function startClient(array $options = [], array $server = []): void
    {
        $this->client = self::createClient(
            array_merge(['environment' => 'test', 'debug' => TRUE], $options),
            $server
        );
    }

    /**
     * @param object $user
     * @param string $password
     * @param string $tokenClass
     * @param string $secureKey
     * @param string $secureArea
     */
    protected function setClientCookies(
        object $user,
        string $password,
        string $tokenClass = UsernamePasswordToken::class,
        string $secureKey = '_security_',
        string $secureArea = 'secured_area'
    ): void
    {
        $this->session      = self::$container->get('session');
        $this->tokenStorage = self::$container->get('security.token_storage');

        $this->session->invalidate();
        $this->session->start();

        $token = new $tokenClass($user, $password, $secureArea, ['admin']);
        $this->tokenStorage->setToken($token);

        $this->session->set(sprintf('%s%s', $secureKey, $secureArea), serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param string       $path
     * @param array        $responseReplacements
     * @param array        $requestHttpReplacements
     * @param array        $requestBodyReplacements
     * @param array        $requestHeadersReplacements
     * @param Closure|NULL $bodyCallback
     *
     * @throws JsonException
     */
    protected function assertResponse(
        string $path,
        array $responseReplacements = [],
        array $requestHttpReplacements = [],
        array $requestBodyReplacements = [],
        array $requestHeadersReplacements = [],
        ?Closure $bodyCallback = NULL
    ): void
    {
        $expectedResponse = $this->getControllerResponse($path);
        $request          = $this->getControllerRequest($path, $requestHttpReplacements);
        $http             = $request[self::$HTTP];
        $body             = $request[self::$BODY];
        $headers          = $request[self::$HEADERS] ?? [];

        [$method, $url] = explode(' ', $http);

        $response    = $this->sendRequest(
            $method,
            $url,
            $this->replaceDynamicData($body, $requestBodyReplacements),
            $this->replaceDynamicData($headers, $requestHeadersReplacements),
            [],
            $bodyCallback
        );
        $http        = $response[self::$STATUS];
        $body        = $this->replaceDynamicData($response[self::$BODY], $responseReplacements);
        $headers     = $this->replaceDynamicData($response[self::$HEADERS], $responseReplacements);
        $messageData = [self::$HTTP => $http, self::$BODY => $body];

        if (array_key_exists(self::$HEADERS, $expectedResponse)) {
            $messageData[self::$HEADERS] = $headers;
        }

        $message = sprintf(
            'Response: %s%s%s%s',
            PHP_EOL,
            PHP_EOL,
            json_encode($messageData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            PHP_EOL
        );

        self::assertEquals($expectedResponse[self::$HTTP], $http, $message);
        self::assertEquals($expectedResponse[self::$BODY], $body, $message);
        if (array_key_exists(self::$HEADERS, $expectedResponse)) {
            $this->assertHeaders($expectedResponse[self::$HEADERS], $headers, $message);
        }
    }

    /**
     * @param string  $path
     * @param string  $uri
     * @param mixed[] $headers
     * @param mixed[] $replaceRequest
     * @param mixed[] $replaceResponse
     * @param mixed[] $fragments
     *
     * @throws JsonException
     */
    protected function assertGraphQlRequest(
        string $path,
        string $uri,
        array $headers = [],
        array $replaceRequest = [],
        array $replaceResponse = [],
        array $fragments = []
    ): void
    {
        $request = NULL;

        if (file_exists($path)) {
            $request = file_get_contents($path);
        }

        foreach ($fragments as $fragment) {
            $request = sprintf('%s%s%s', file_get_contents($fragment), PHP_EOL, $request);
        }

        if ($replaceRequest) {
            foreach ($replaceRequest as $rep => $val) {
                $request = str_replace($rep, is_null($val) ? 'null' : $val, $request ?: '');
            }
        }

        $response = $this->sendGraphQlRequest((string) $request, $uri, $headers);

        if ($replaceResponse) {
            array_walk_recursive(
                $response,
                static function (&$value, $key) use ($replaceResponse): void {
                    if (isset($replaceResponse[$key])) {
                        $value = $replaceResponse[$key];
                    }
                }
            );
        }

        self::assertEquals(
            $this->getContentFromFile($path, $response),
            $response,
            sprintf(
                'Response: %s%s%s%s',
                PHP_EOL,
                PHP_EOL,
                json_encode($response, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
                PHP_EOL
            )
        );
    }

    /**
     * @param string  $request
     * @param string  $uri
     * @param mixed[] $headers
     *
     * @return mixed[]
     * @throws JsonException
     */
    protected function sendGraphQlRequest(string $request, string $uri, array $headers = []): array
    {
        $this->client->request('POST', $uri, ['query' => $request], [], $headers);
        $response = $this->client->getResponse();

        if (get_class($response) === JsonResponse::class) {
            return json_decode($response->getContent() ?: '{}', TRUE, 512, JSON_THROW_ON_ERROR);
        } else {
            self::fail($response->getContent() ?: 'No Content');
        }
    }

    /**
     * @param string       $method
     * @param string       $url
     * @param array        $body
     * @param array        $headers
     * @param array        $files
     * @param Closure|NULL $bodyCallback
     *
     * @return array
     * @throws JsonException
     */
    protected function sendRequest(
        string $method,
        string $url,
        array $body = [],
        array $headers = [],
        array $files = [],
        ?Closure $bodyCallback = NULL
    ): array
    {
        if (isset($this->dispatcher)) {
            if (!class_exists('Apitte\\Core\\Dispatcher\\IDispatcher') ||
                !class_exists('Apitte\\Core\\Http\\ApiRequest') ||
                !class_exists('Apitte\\Core\\Http\\ApiResponse') ||
                !class_exists('Contributte\\Psr7\\Psr7Response') ||
                !class_exists('Contributte\\Psr7\\Psr7ServerRequest')) {
                throw new LogicException('Package "apitte/core" does not exist. Please install it first.');
            }

            $response = $this->dispatcher->dispatch(
                new ApiRequest(
                    new Psr7ServerRequest(
                        $method,
                        $url,
                        $headers,
                        json_encode($body, JSON_THROW_ON_ERROR)
                    )
                ),
                new ApiResponse(new Psr7Response())
            );

            return [
                self::$BODY    => $bodyCallback ? $bodyCallback($response) : $response->getJsonBody(),
                self::$STATUS  => $response->getStatusCode(),
                self::$HEADERS => $response->getHeaders(),
            ];
        } else if (isset($this->client)) {
            $this->client->request($method, $url, $body, $files, $headers, (string) json_encode($body));
            $response = $this->client->getResponse();

            return [
                self::$BODY    => $bodyCallback ? $bodyCallback($response) : json_decode(
                    (string) $response->getContent(),
                    TRUE,
                    512,
                    JSON_THROW_ON_ERROR
                ),
                self::$STATUS  => $response->getStatusCode(),
                self::$HEADERS => $response->headers->all(),
            ];
        } else {
            throw new LogicException('Dispatcher or client is not set');
        }
    }

    /**
     * --------------------------------------- HELPERS ----------------------------------------------
     */

    /**
     * @param string $path
     * @param array  $replacements
     *
     * @return array
     * @throws JsonException
     */
    private function getControllerRequest(string $path, array $replacements = []): array
    {
        if (!is_file($path)) {
            throw new LogicException(sprintf("Request '%s' cannot be loaded!", $path));
        }

        $content = $this->getContent($path);

        foreach ([self::$HTTP, self::$BODY] as $part) {
            if (!isset($content[$part])) {
                throw new LogicException(sprintf("Request missing '%s' part!", $part));
            }
        }

        if ($replacements) {
            $content[self::$HTTP] = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $content[self::$HTTP]
            );
        }

        if (!preg_match(self::$REQUEST, $content[self::$HTTP])) {
            throw new LogicException(sprintf("Request part '%s' is invalid", $content[self::$HTTP]));
        }

        return $content;
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws JsonException
     */
    private function getControllerResponse(string $path): array
    {
        $path = str_replace('Request.json', 'Response.json', $path);

        if (!is_file($path)) {
            file_put_contents(
                $path,
                json_encode(
                    [self::$HTTP => ApiResponse::S200_OK, self::$BODY => []],
                    JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
                ),
                FILE_APPEND
            );

            exec(sprintf('chown %s %s', getenv('DEV_UID'), $path));
            exec(sprintf('chgrp %s %s', getenv('DEV_GID'), $path));
        }

        $content = $this->getContent($path);

        foreach ([self::$HTTP, self::$BODY] as $part) {
            if (!isset($content[$part])) {
                throw new LogicException(sprintf("Response missing '%s' part!", $part));
            }
        }

        return $content;
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws JsonException
     */
    private function getContent(string $path): array
    {
        return json_decode(file_get_contents($path) ?: '{}', TRUE, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $data
     * @param array $replacements
     *
     * @return array
     */
    private function replaceDynamicData(array $data, array $replacements): array
    {
        if ($replacements) {
            array_walk_recursive(
                $data,
                static function (&$value, $key) use ($replacements): void {
                    if (isset($replacements[$key])) {
                        $value = $replacements[$key];
                    }
                }
            );
        }

        return $data;
    }

    /**
     * @param string  $path
     * @param mixed[] $response
     *
     * @return mixed[]
     * @throws JsonException
     */
    private function getContentFromFile(string $path, array $response): array
    {
        $path = str_replace('.graphql', '.json', $path);

        if (!is_file($path)) {
            file_put_contents(
                $path,
                sprintf('%s%s', json_encode($response, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), PHP_EOL)
            );

            exec(sprintf('chown %s %s', getenv('DEV_UID'), $path));
            exec(sprintf('chgrp %s %s', getenv('DEV_GID'), $path));
        }

        return $this->getContent($path);
    }

    /**
     * @param mixed[] $expectedHeaders
     * @param mixed[] $headers
     * @param string  $message
     */
    private function assertHeaders(array $expectedHeaders, array $headers, string $message): void
    {
        foreach ($expectedHeaders as $key => $expectedHeaderValue) {
            if (!is_array($expectedHeaderValue) && is_array($headers[$key])) {
                $expectedHeaderValue = [$expectedHeaderValue];
            }
            self::assertEquals($expectedHeaderValue, $headers[$key], $message);
        }
    }

}
