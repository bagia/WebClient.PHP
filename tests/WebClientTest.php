<?php

require_once( __DIR__ . '/../src/WebClient.php');

/**
 * @brief Tests for the WebClient class
 * @author bagia
 * Before running this file with phpunit, you should start
 * a web server with the Resources folder as its document root
 * on localhost:8000.
 * eg. php -S localhost:8000 /php/to/WebClient/test/Resources/
 */
class WebClientTest extends PHPUnit_Framework_TestCase {

    protected function checkHelloWorld($url) {
        $webClient = new WebClient();
        $response = $webClient->Navigate($url);
        $this->assertNotEquals($response, FALSE, "The request failed against {$url}");
        $this->assertEquals($response, 'Hello World', "Unexpected content.");
    }

    /**
     * Test accessing a regular HTTP resource
     */
    public function testHttp200() {
        $this->checkHelloWorld($this->http200Url);
    }

    /**
     * Test accessing an HTTPS endpoint with a valid certificate
     */
    public function testHttps200() {
        $this->checkHelloWorld($this->https200Url);
    }

    /**
     * Test a 404 error
     */
    public function testHttp404() {
        $webClient = new WebClient();
        $response = $webClient->Navigate($this->http404Url);
        $this->assertEquals($response, FALSE, "Response is not False.");
    }

    protected $http200Url = 'http://localhost:8000/helloworld.txt';
    protected $https200Url = 'https://raw.githubusercontent.com/bagia/WebClient.PHP/master/tests/Resources/helloworld.txt';
    protected $http404Url = 'http://localhost:8000/nofile.txt';
}