<?php

namespace Charcoal\Tests\App;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client as HttpClient;

/**
 * Start a PHP builtin server instance, ready to serve a copy of the project.
 */
trait ServerTestTrait
{
    /**
     * @var mixed The process identifier of the built-in PHP server.
     */
    static private $serverProcess;

    /**
     * @var string The hostname for the built-in PHP server.
     */
    static protected $serverHost = 'localhost';

    /**
     * @var string The port number on which the built-in PHP server will be opened.
     */
    static protected $serverPort = '8484';

    /**
     * @var null|string The server root directory, where it should be ran from.
     */
    static protected $serverRoot;

    /**
     * @var string The APPLICATION_ENV environment variable.
     */
    static protected $serverApplicationEnv = 'phpunit';

    /**
     * Asserts that an array has a specified subset.
     *
     * @param  array|ArrayAccess|mixed[] $subset                 The expected subset.
     * @param  array|ArrayAccess|mixed[] $array                  The actual haystack.
     * @param  boolean                   $checkForObjectIdentity Unused.
     * @param  string                    $message                The error to report.
     * @throws InvalidArgumentException
     */
    abstract public function assertArraySubset($subset, $array, $checkForObjectIdentity = false, $message = ''): void;

    /**
     * Retrieve the built-in PHP server URL.
     */
    protected static function serverURL(): string
    {
        return static::$serverHost.':'.static::$serverPort;
    }

    /**
     * Retrieve the root directory, where to start the built-in PHP server.
     * @return string
     */
    protected static function serverRoot()
    {
        if (static::$serverRoot !== null) {
            return static::$serverRoot;
        }
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'www';
    }

    /**
     * Retrieve wether the tests are run on windows or not.
     */
    protected static function isWindows(): bool
    {
        return (stristr(php_uname('s'), 'win') !== false);
    }

    /**
     * Start a built-in PHP server process.
     */
    #[\PHPUnit\Framework\Attributes\BeforeClass]
    public static function bootUpBuiltInServer(): void
    {
        $command = sprintf(
            'php -S %s -t %s',
            static::serverURL(),
            static::serverRoot()
        );

        if (static::isWindows()) {
            $command = sprintf(
                'set APPLICATION_ENV=%s; start /b %s',
                static::$serverApplicationEnv,
                $command
            );
        } else {
            $command = sprintf(
                'APPLICATION_ENV=%s %s',
                static::$serverApplicationEnv,
                $command
            );
        }
        static::$serverProcess = popen($command, 'r');

        sleep(2);
    }

    /**
     * Terminates the built-in PHP server process.
     */
    #[\PHPUnit\Framework\Attributes\AfterClass]
    public static function turnDownBuiltInServer(): void
    {
        pclose(static::$serverProcess);
    }

    /**
     * @param array $request The request data (method, route, options).
     */
    protected function callRequest(array $request): \Psr\Http\Message\ResponseInterface
    {
        $route = str_replace('.', '', $request['route']);
        $client = new HttpClient();
        return $client->request(
            $request['method'],
            'http://'.static::serverURL().$route,
            $request['options']
        );
    }

    protected function assertResponseMatchesExpected(array $expected, ResponseInterface $response)
    {
        if (isset($expected['statusCode']) && $expected['statusCode']) {
            $this->assertResponseHasStatusCode($expected['statusCode'], $response);
        }
        if (isset($expected['json']) && $expected['json']) {
            $this->assertResponseBodyMatchesJson($expected['json'], $response);
        }
        if (isset($expected['body']) && $expected['body']) {
            if (is_string($expected['body'])) {
                $this->assertResponseBodyRegExp($expected['body'], $response);
            } else {
                foreach ($expected['body'] as $regexp) {
                    $this->assertResponseBodyRegExp($regexp, $response);
                }
            }
        }
    }

    /**
     * @param integer           $expectedStatusCode
     */
    protected function assertResponseHasStatusCode($expectedStatusCode, ResponseInterface $response)
    {
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    /**
     * @param array|string      $json
     */
    protected function assertResponseBodyMatchesJson($json, ResponseInterface $response)
    {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        $results = json_decode((string)$response->getBody(), true);
        foreach ($json as $k => $v) {
            $this->assertArrayHasKey($k, $results);
            if (is_array($v)) {
                $this->assertArraySubset($v, $results[$k]);
            } else {
                $this->assertEquals($v, $results[$k]);
            }
        }
    }

    /**
     * @param string            $pattern
     */
    protected function assertResponseBodyRegExp($pattern, ResponseInterface $response)
    {
        $this->assertRegExp($pattern, (string)$response->getBody());
    }
}
