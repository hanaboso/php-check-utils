<?php declare(strict_types=1);

namespace Hanaboso\PhpCheckUtils\PhpUnit\Traits;

use Apitte\Core\Dispatcher\IDispatcher;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Contributte\Psr7\Psr7Response;
use Contributte\Psr7\Psr7ServerRequest;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
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
     * @param string $path
     * @param array  $responseReplacements
     * @param array  $requestHttpReplacements
     * @param array  $requestBodyReplacements
     * @param array  $requestHeadersReplacements
     */
    protected function assertResponse(
        string $path,
        array $responseReplacements = [],
        array $requestHttpReplacements = [],
        array $requestBodyReplacements = [],
        array $requestHeadersReplacements = []
    ): void
    {
        $expectedResponse = $this->getControllerResponse($path);
        $request          = $this->getControllerRequest($path, $requestHttpReplacements);
        $http             = $request[self::$HTTP];
        $body             = $request[self::$BODY];
        $headers          = $request[self::$HEADERS] ?? [];

        [$method, $url] = explode(' ', $http);
        $response = $this->sendRequest(
            $method,
            $url,
            $this->replaceDynamicData($body, $requestBodyReplacements),
            $this->replaceDynamicData($headers, $requestHeadersReplacements)
        );
        $http     = $response[self::$STATUS];
        $body     = $this->replaceDynamicData($response[self::$BODY], $responseReplacements);

        $message = sprintf(
            'Response: %s%s%s%s',
            PHP_EOL,
            PHP_EOL,
            json_encode([self::$HTTP => $http, self::$BODY => $body], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            PHP_EOL
        );

        self::assertEquals($expectedResponse[self::$HTTP], $http, $message);
        self::assertEquals($expectedResponse[self::$BODY], $body, $message);
    }

    /**
     * @param string $path
     * @param string $uri
     * @param array  $headers
     * @param array  $replaceRequest
     * @param array  $replaceResponse
     */
    protected function assertGraphQlRequest(
        string $path,
        string $uri,
        array $headers = [],
        array $replaceRequest = [],
        array $replaceResponse = []
    ): void
    {
        $request = NULL;
        if (file_exists($path)) {
            $request = file_get_contents($path);
        }

        if ($replaceRequest) {
            foreach ($replaceRequest as $rep => $val) {
                $request = str_replace($rep, is_null($val) ? 'null' : $val, $request ?: '');
            }
        }

        $response = $this->sendGraphQlRequest($request, $uri, $headers);
        $content  = $this->getContentFromFile($path);

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

        $message = sprintf(
            'Response: %s%s%s%s',
            PHP_EOL,
            PHP_EOL,
            json_encode($response, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            PHP_EOL
        );

        self::assertEquals($content, $response, $message);
    }

    /**
     * @param string $request
     * @param string $uri
     * @param array  $headers
     *
     * @return array
     */
    protected function sendGraphQlRequest(string $request, string $uri, array $headers = []): array
    {
        $this->client->request('POST', $uri, ['query' => $request], [], $headers);
        $response = $this->client->getResponse();

        return json_decode($response->getContent(), TRUE, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $body
     * @param array  $headers
     * @param array  $files
     *
     * @return array
     */
    protected function sendRequest(
        string $method,
        string $url,
        array $body = [],
        array $headers = [],
        array $files = []
    ): array
    {
        if (isset($this->dispatcher)) {
            $response = $this->dispatcher->dispatch(
                new ApiRequest(
                    new Psr7ServerRequest(
                        $method, $url, $headers,
                        json_encode($body, JSON_THROW_ON_ERROR)
                    )
                ),
                new ApiResponse(new Psr7Response())
            );

            return [self::$BODY => $response->getJsonBody(), self::$STATUS => $response->getStatusCode()];
        } else if (isset($this->client)) {
            $this->client->request($method, $url, $body, $files, $headers, (string) json_encode($body));
            $response = $this->client->getResponse();

            return [
                self::$BODY   => json_decode((string) $response->getContent(), TRUE, 512, JSON_THROW_ON_ERROR),
                self::$STATUS => $response->getStatusCode(),
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
     * @param string $path
     *
     * @return array
     */
    private function getContentFromFile(string $path): array
    {
        $path = str_replace('.graphql', '.json', $path);

        if (!is_file($path)) {
            file_put_contents(
                $path,
                json_encode(
                    ['data' => []],
                    JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
                ),
                FILE_APPEND
            );

            exec(sprintf('chown %s %s', getenv('DEV_UID'), $path));
            exec(sprintf('chgrp %s %s', getenv('DEV_GID'), $path));

        }

        return $this->getContent($path);
    }

}
