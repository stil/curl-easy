<?php
namespace cURL\Tests;

use cURL;

class RequestTest extends TestCase
{
    /**
     * Test setOptions() and getOptions() methods
     */
    public function testSetGetOptions()
    {
        $req = new cURL\Request();
        $opts = $req->getOptions();
        $this->assertInstanceOf('cURL\Options', $opts);
        $this->assertEmpty($opts->toArray());
        
        $opts = new cURL\Options();
        $opts->set(CURLOPT_RETURNTRANSFER, true);
        $req->setOptions($opts);
        $this->assertEquals($opts, $req->getOptions());
        
    }
    
    /**
     * Test synchronous request through send()
     */
    public function testRequestSynchronous()
    {
        /**
         * Successful request
         */
        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->createRequestUrl())
            ->set(CURLOPT_RETURNTRANSFER, true);
        $this->assertInternalType('resource', $req->getHandle());
        $this->validateSuccesfulResponse($req->send());
        
        /**
         * Timeouted request
         */
        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->timeoutTestUrl)
            ->set(CURLOPT_TIMEOUT, 3)
            ->set(CURLOPT_RETURNTRANSFER, true);
        $this->validateTimeoutedResponse($req->send());
    }
    
    /**
     * Test asynchronous request through socketPerform() and socketSelect()
     */
    public function testRequestAsynchronous()
    {
        $test = $this;
        
        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->createRequestUrl())
            ->set(CURLOPT_RETURNTRANSFER, true);
        $req->addListener(
            'complete',
            function ($event) use ($test) {
                $test->validateSuccesfulResponse($event->response);
            }
        );
        
        $n = 0;
        while ($req->socketPerform()) {
            $n++;
            $req->socketSelect();
        }

        $e = null;
        try {
            $req->socketPerform();
        } catch (cURL\Exception $e) {
        }
        
        $this->assertInstanceOf('cURL\Exception', $e);
        $this->assertGreaterThan(0, $n);
        
        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->timeoutTestUrl)
            ->set(CURLOPT_TIMEOUT, 3)
            ->set(CURLOPT_RETURNTRANSFER, true);
        $req->addListener(
            'complete',
            function ($event) use ($test) {
                $test->validateTimeoutedResponse($event->response);
            }
        );
        
        while ($req->socketPerform()) {
            $req->socketSelect();
        }
    }

    /**
     * Tests whether 'complete' event on individual Request has been fired
     * once when using RequestsQueue
     */
    public function testRequestCompleteEventAsynchronous()
    {
        $eventFired = 0;

        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->createRequestUrl())
            ->set(CURLOPT_RETURNTRANSFER, true);
        $req->addListener(
            'complete',
            function ($event) use (&$eventFired) {
                $this->validateSuccesfulResponse($event->response);
                $eventFired++;
            }
        );

        while ($req->socketPerform()) {
            $req->socketSelect();
        }
        $this->assertEquals(1, $eventFired);
    }

    /**
     * Tests whether 'complete' event on individual Request has not been fired
     * when Request::send() was used.
     */
    public function testRequestCompleteEventSynchronous()
    {
        $eventFired = 0;

        $req = new cURL\Request();
        $req->getOptions()
            ->set(CURLOPT_URL, $this->createRequestUrl())
            ->set(CURLOPT_RETURNTRANSFER, true);
        $req->addListener(
            'complete',
            function (cURL\Event $event) use (&$eventFired) {
                $eventFired++;
            }
        );

        $req->send();
        $this->assertEquals(0, $eventFired);
    }
}
