<?php
namespace cURL;

use Symfony\Component\EventDispatcher\EventDispatcher;

class RequestsQueue extends EventDispatcher implements RequestsQueueInterface, \Countable
{
    /**
     * @var Options Default options for new Requests attached to RequestsQueue
     */
    protected $defaultOptions = null;
    
    /**
     * @var resource cURL multi handler
     */
    protected $mh;
    
    /**
     * @var Request[] Array of requests attached
     */
    protected $queue = array();
    
    /**
     * @var array Array of requests added to curl multi handle
     */
    protected $running = array();
    
    /**
     * Initializes curl_multi handler
     */
    public function __construct()
    {
        $this->mh = curl_multi_init();
    }
    
    /**
     * Destructor, closes curl_multi handler
     *
     * @return void
     */
    public function __destruct()
    {
        if (isset($this->mh)) {
            curl_multi_close($this->mh);
        }
    }
    
    /**
     * Returns cURL\Options object with default request's options
     *
     * @return Options
     */
    public function getDefaultOptions()
    {
        if (!isset($this->defaultOptions)) {
            $this->defaultOptions = new Options();
        }
        return $this->defaultOptions;
    }
    
    /**
     * Overrides default options with given Options object
     *
     * @param Options $defaultOptions New options
     * @return void
     */
    public function setDefaultOptions(Options $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     * Get cURL multi handle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->mh;
    }
    
    /**
     * Attach request to queue.
     *
     * @param Request $request Request to add
     * @return self
     */
    public function attach(Request $request)
    {
        $this->queue[$request->getUID()] = $request;
        return $this;
    }
    
    /**
     * Detach request from queue.
     *
     * @param Request $request Request to remove
     * @return self
     */
    public function detach(Request $request)
    {
        unset($this->queue[$request->getUID()]);
        return $this;
    }
    
    /**
     * Processes handles which are ready and removes them from pool.
     *
     * @return int Amount of requests completed
     */
    protected function read()
    {
        $n = 0;
        while ($info = curl_multi_info_read($this->mh)) {
            $n++;
            $request = $this->queue[(int)$info['handle']];
            $result = $info['result'];
            
            curl_multi_remove_handle($this->mh, $request->getHandle());
            unset($this->running[$request->getUID()]);
            $this->detach($request);
            
            $event = new Event();
            $event->request = $request;
            $event->response = new Response($request, curl_multi_getcontent($request->getHandle()));
            if ($result !== CURLE_OK) {
                $event->response->setError(new Error(curl_error($request->getHandle()), $result));
            }
            $event->queue = $this;
            $this->dispatch('complete', $event);
            $request->dispatch('complete', $event);
        }
        
        return $n;
    }
    
    /**
     * Returns count of handles in queue
     * 
     * @return int    Handles count
     */
    public function count()
    {
        return count($this->queue);
    }
    
    /**
     * Executes requests in parallel
     *
     * @return void
     */
    public function send()
    {
        while ($this->socketPerform()) {
            $this->socketSelect();
        }
    }
    
    /**
     * Returns requests present in $queue but not in $running
     * 
     * @return Request[]    Array of requests
     */
    protected function getRequestsNotRunning()
    {
        $map = $this->queue;
        foreach($this->running as $k => $v) unset($map[$k]);
        return $map;
    }

    /**
     * Download available data on socket.
     *
     * @throws Exception
     * @return bool    TRUE when there are any requests on queue, FALSE when finished
     */
    public function socketPerform()
    {
        if ($this->count() == 0) {
            throw new Exception('Cannot perform if there are no requests in queue.');
        }

        $notRunning = $this->getRequestsNotRunning();
        do {
            /**
             * Apply cURL options to new requests
             */
            foreach ($notRunning as $request) {
                $this->getDefaultOptions()->applyTo($request);
                $request->getOptions()->applyTo($request);
                curl_multi_add_handle($this->mh, $request->getHandle());
                $this->running[$request->getUID()] = $request;
            }

            $runningHandles = null;
            do {
                // http://curl.haxx.se/libcurl/c/curl_multi_perform.html
                // If an added handle fails very quickly, it may never be counted as a running_handle.
                $mrc = curl_multi_exec($this->mh, $runningHandles);
            } while ($mrc === CURLM_CALL_MULTI_PERFORM);

            if ($runningHandles < count($this->running)) {
                $this->read();
            }
            
            $notRunning = $this->getRequestsNotRunning();
        } while (count($notRunning) > 0);
        // Why the loop? New requests might be added at runtime on 'complete' event.
        // So we need to attach them to curl_multi handle immediately.
        
        return $this->count() > 0;
    }
    
    /**
     * Waits until activity on socket
     * On success, returns TRUE. On failure, this function will
     * return FALSE on a select failure or timeout (from the underlying
     * select system call)
     * 
     * @param float|int $timeout Maximum time to wait
     * @throws Exception
     * @return bool
     */
    public function socketSelect($timeout = 1)
    {
        if ($this->count() == 0) {
            throw new Exception('Cannot select if there are no requests in queue.');
        }
        return curl_multi_select($this->mh, $timeout) !== -1;
    }
}
