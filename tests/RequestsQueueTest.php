<?php

    namespace cURL\Tests;

    use cURL;
    use cURL\Exception;
    use cURL\RequestsQueue;
    use SplObjectStorage;

    class RequestsQueueTest extends TestCase
    {
        /**
         * Test run queue without any requests
         */
        public function testSendNoRequests()
        {
            $bool = false;
            try {
                $q = new RequestsQueue();
                $q->send();
            } catch (cURL\Exception $ex) {
                $bool = true;
            }
            $this->assertTrue($bool);
        }

        /**
         * Test setDefaultOptions() and getDefaultOptions()
         */
        public function testOptions()
        {
            $q = new RequestsQueue();
            $opts = $q->getDefaultOptions();
            $this->assertInstanceOf('cURL\Options', $opts);
            $this->assertEmpty($opts->toArray());

            $opts = new cURL\Options();
            $opts->set(CURLOPT_URL, 'http://example-1/');
            $opts->set(CURLOPT_USERAGENT, 'browser');
            $q->setDefaultOptions($opts);
            $this->assertEquals($opts, $q->getDefaultOptions());
        }

        /**
         * Returns RequestsQueue for tests
         *
         * @return RequestsQueue    Queue for tests
         */
        protected function prepareTestQueue(): RequestsQueue
        {
            $test = $this;
            $queue = new RequestsQueue();
            $queue->getDefaultOptions()
                ->set(CURLOPT_RETURNTRANSFER, true)
                ->set(CURLOPT_ENCODING, '');

            $s = new SplObjectStorage();

            $queue->addListener(
                'complete',
                function (cURL\Event $event) use ($test, $s) {
                    $test->validateSuccesfulResponse($event->response, $s[$event->request]);
                }
            );

            for ($i = 0; $i < 5; $i++) {
                $request = new cURL\Request();
                $s[$request] = $i;
                $request->getOptions()->set(CURLOPT_URL, $this->createRequestUrl($i));
                $queue->attach($request);
            }

            $this->assertEquals(5, $queue->count());
            $this->assertCount(5, $queue);

            return $queue;
        }

        /**
         * Test request synchronous
         * @throws Exception
         */
        public function testQueueSynchronous()
        {
            $queue = $this->prepareTestQueue();
            $queue->send();
        }

        /**
         * Test request asynchronous
         * @throws Exception
         */
        public function testQueueAsynchronous()
        {
            $queue = $this->prepareTestQueue();

            while ($queue->socketPerform()) {
                $queue->socketSelect();
            }

            $e = null;
            try {
                $queue->socketPerform();
            } catch (cURL\Exception $e) {
            }

            $this->assertInstanceOf('cURL\Exception', $e);
        }

        /**
         * Test requests attaching on run time
         * @throws Exception
         */
        public function testRepeatOnRuntime()
        {
            $n = 0;
            $queue = $this->prepareTestQueue();
            $queue->addListener(
                'complete',
                function (cURL\Event $event) use (&$n) {
                    $n++;
                    $request = $event->request;
                    $queue = $event->queue;
                    if (!isset($request->repeat)) {
                        $request->repeat = true;
                        $queue->attach($request);
                    }
                }
            );
            $queue->send();
            $this->assertEquals(10, $n);
        }

        /**
         * Test requests attaching on run time
         * @throws Exception
         */
        public function testAttachNewOnRuntime()
        {
            $total = 10;
            $test = $this;
            $queue = new RequestsQueue();
            $queue->getDefaultOptions()
                ->set(CURLOPT_RETURNTRANSFER, true)
                ->set(CURLOPT_ENCODING, '');

            $s = new SplObjectStorage();

            $n = 0;
            $attachNew = function () use ($queue, &$n, $total, $s) {
                if ($n < $total) {
                    $n++;
                    $request = new cURL\Request();
                    $s[$request] = $n;
                    $request->getOptions()->set(CURLOPT_URL, $this->createRequestUrl($n));
                    $queue->attach($request);
                }
            };

            $attachNew();
            $queue->addListener(
                'complete',
                function (cURL\Event $event) use (&$requests, $test, $attachNew, $s) {
                    $test->validateSuccesfulResponse($event->response, $s[$event->request]);
                    $attachNew();
                }
            );
            $queue->send();
            $this->assertEquals($total, $n);
        }

        /**
         * Tests whether 'complete' event on individual Request has been fired
         * when using RequestsQueue
         * @throws Exception
         */
        public function testRequestCompleteEvent()
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

            $queue = new RequestsQueue();
            $queue->attach($req);
            $queue->send();

            $this->assertEquals(1, $eventFired);
        }

        /**
         * Requests which fail very quickly might cause infinite loop and return no response.
         * http://curl.haxx.se/libcurl/c/curl_multi_perform.html
         * "If an added handle fails very quickly, it may never be counted as a running_handle."
         * This test ensures that it won't loop and will return properly error code.
         * @throws Exception
         */
        public function testErrorCode()
        {
            $request = new cURL\Request('');
            $request->addListener('complete', function (cURL\Event $e) {
                $this->assertEquals(3, $e->response->getError()->getCode());
            });

            $queue = new RequestsQueue();
            $queue->attach($request);
            $queue->send();
        }
    }
