<?php
namespace cURL\Tests;

use cURL\Response;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $okTestUrl = 'https://httpbin.org/get';
    protected $timeoutTestUrl = 'https://httpbin.org/delay/10';

    public function createRequestUrl($param = 'ok')
    {
        return $this->okTestUrl.'?'.http_build_query(array('curl-easy' => $param));
    }

    public function validateSuccesfulResponse(Response $response, $param = 'ok')
    {
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertInternalType('array', $data);
        $info = $response->getInfo();
        $this->assertInternalType('array', $info);
        $this->assertEquals(200, $info['http_code']);
        $this->assertEquals($param, $data['args']['curl-easy']);
        $this->assertEquals(200, $response->getInfo(CURLINFO_HTTP_CODE));
        $this->assertFalse($response->hasError());
    }
    
    public function validateTimeoutedResponse(Response $response)
    {
        $this->assertEmpty($response->getContent());
        $this->assertTrue($response->hasError());
        $this->assertEquals(CURLE_OPERATION_TIMEOUTED, $response->getError()->getCode());
        $this->assertNotEmpty($response->getError()->getMessage());
    }
}
