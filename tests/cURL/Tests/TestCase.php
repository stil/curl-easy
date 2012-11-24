<?php
namespace cURL\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $okTestUrl = 'http://localhost:55555/';
    protected $errorTestUrl = 'http://localhost:55555/timeout';
    
    public function validateSuccesfulResponse($response)
    {
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('OK', $data['status']);
        $info = $response->getInfo();
        $this->assertInternalType('array', $info);
        $this->assertEquals(200, $info['http_code']);
        $this->assertEquals(200, $response->getInfo(CURLINFO_HTTP_CODE));
        $this->assertFalse($response->hasError());
    }
    
    public function validateTimeoutedResponse($response)
    {
        $this->assertInternalType('null', $response->getContent());
        $this->assertTrue($response->hasError());
        $this->assertEquals(CURLE_OPERATION_TIMEOUTED, $response->getError()->getCode());
        $this->assertNotEmpty($response->getError()->getMessage());
    }
}