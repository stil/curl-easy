<?php
namespace cURL;
class RequestsQueue implements RequestsQueueInterface {
    protected $defaultOptions = null;
    protected $eventManager;
    protected $mh;
    protected $active = false;
    protected $requests = array();
    
    /**
     * Constructor of MultiHandler
     * Utilise curl_multi_init()
     *
     * @return void
     */
    public function __construct() {
        $this->eventManager = new EventManager;
        $this->mh = curl_multi_init();
    }
    
    /**
     * Destructor of MultiHandler
     * Utilise curl_multi_close()
     *
     * @return void
     */
    public function __destruct() {
        if (isset($this->mh)) curl_multi_close($this->mh);
    }
    
    /**
     * Returns cURL\Options object with default request's options
     *
     * @return Options
     */
    public function getDefaultOptions() {
        if (!isset($this->defaultOptions)) {
            $this->defaultOptions = new Options;
        }
        return $this->defaultOptions;
    }
    
    /**
     * Overrides default options with given Options object
     *
     * @param Options $defaultOptions New options
     *
     * @return void
     */
    public function setDefaultOptions(Options $defaultOptions) {
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     * When every request will be complete callback is executed
     *
     * @param callback $callback
     *
     * @return void
     */
    public function onRequestComplete($callback) {
        $this->eventManager->attach('complete', $callback);
    }
    
    /**
     * Get cURL multi handle
     *
     * @return resource
     */
    public function getHandle() {
        return $this->mh;
    }
    
    /**
     * Attach request to queue.
     * Utilise curl_multi_add_handle().
     *
     * @param Request $request
     *
     * @return int Returns 0 on success, or one of the CURLM_XXX errors code.
     */
    public function attach(Request $request) {
        $this->requests[$request->getUID() ] = $request;
        return curl_multi_add_handle($this->mh, $request->getHandle());
    }
    
    /**
     * Detach request from pool.
     * Utilise curl_multi_remove_handle().
     *
     * @param Request $request
     *
     * @return int Returns 0 on success, or one of the CURLM_XXX errors code.
     */
    public function detach(Request $request) {
        unset($this->requests[$request->getUID() ]);
        return curl_multi_remove_handle($this->mh, $request->getHandle());
    }
    
    /**
     * Processes handles which are ready and removes them from pool.
     *
     * @return void
     */
    protected function readAll() {
        while ($info = curl_multi_info_read($this->mh)) {
            $ch = $info['handle'];
            $uid = (int)$ch;
            $request = $this->requests[$uid];
            $this->eventManager->notify('complete', array($this, $request));
            $this->detach($request);
        }
    }
    
    /**
     * Removes timeout handles from queue.
     *
     * @return void
     */
    protected function cleanupTimeoutedRequests() {
        foreach ($this->requests as $handle) {
            if ($handle->timeout > 0 && (microtime(true) - $handle->timeStart) >= $handle->timeout) {
                $this->eventManager->notify('complete', array($this, $handle));
                $this->detach($handle);
            }
        }
    }
    
    /**
     * Returns count of handles in queue.
     *
     * @return int
     */
    public function activeHandlesCount() {
        return count($this->handles);
    }
    
    /**
     * Setup options before execution
     * 
     * @return void
     */
    protected function initProcessing() {
        foreach ($this->requests as $k => $request) {
            $this->getDefaultOptions()->applyTo($request);
            $request->getOptions()->applyTo($request);
            if (!isset($request->timeStart)) $request->timeStart = microtime(true);
        }
        $this->active = true;
    }
    
    /**
     * Sends requests in parallel with blocking
     *
     * @param bool $blocking Block script execution until requests is complete?
     *
     * @return bool is execution still running?
     */
    public function send() {
        while ($this->socketPerform()) {
            if (!$this->socketSelect()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Download available data on socket.
     * 
     * @return bool    TRUE when there are any requests on queue, FALSE when finished
     */
    public function socketPerform() {
        if (!$this->active) {
            $this->initProcessing();
        }
        $remaining = count($this->requests);
        if ($remaining > 0) {
            curl_multi_exec($this->mh, $running);
            $this->readAll();
            /* Remove timeout requests */
            $this->cleanupTimeoutedRequests();
            var_dump(memory_get_usage() / 1024);
            return $running > 0;
        }
        return false;
    }
    
    /**
     * Waits until activity on socket
     * 
     * @param float $timeout Maximum time to wait
     * 
     * @return bool    TRUE on success, FALSE on timeout
     */
    public function socketSelect($timeout = 0.03) {
        return curl_multi_select($this->mh, $timeout) !== - 1;
    }
}
