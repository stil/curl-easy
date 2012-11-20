<?php
namespace cURL;
use Countable;

class RequestsQueue implements RequestsQueueInterface, Countable
{
    protected $defaultOptions = null;
    protected $eventManager;
    protected $mh;
    protected $running = 0;
    protected $requests = array();
    
    /**
     * Constructor of MultiHandler
     * Utilise curl_multi_init()
     *
     * @return void
     */
    public function __construct()
    {
        $this->eventManager = new EventManager;
        $this->mh = curl_multi_init();
    }
    
    /**
     * Destructor of MultiHandler
     * Utilise curl_multi_close()
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
    public function setDefaultOptions(Options $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     * When every request will be complete callback is executed
     *
     * @param callback $callback Callback function to execute
     *
     * @return void
     */
    public function onRequestComplete($callback)
    {
        $this->eventManager->attach('complete', $callback);
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
     * Utilise curl_multi_add_handle().
     *
     * @param Request $request Request to add
     *
     * @return int Returns 0 on success, or one of the CURLM_XXX errors code.
     */
    public function attach(Request $request)
    {
        /* If timeStart exists it means request is added on runtime */
        if (isset($request->timeStart)) {
            $request->timeStart = microtime(true);
        }
        
        $this->requests[$request->getUID()] = $request;
        return curl_multi_add_handle($this->mh, $request->getHandle());
    }
    
    /**
     * Detach request from pool.
     * Utilise curl_multi_remove_handle().
     *
     * @param Request $request Request to remove
     *
     * @return int Returns 0 on success, or one of the CURLM_XXX errors code.
     */
    public function detach(Request $request)
    {
        unset($this->requests[$request->getUID()]);
        return curl_multi_remove_handle($this->mh, $request->getHandle());
    }
    
    /**
     * Processes handles which are ready and removes them from pool.
     *
     * @return void
     */
    protected function readAll()
    {
        while ($info = curl_multi_info_read($this->mh)) {
            $ch = $info['handle'];
            $uid = (int)$ch;
            $request = $this->requests[$uid];
            $request->setErrorCode($info['result']);
            $this->detach($request);
            $this->eventManager->notify('complete', array($this, $request));
        }
    }
    
    /**
     * Returns count of handles in queue
     * 
     * @return int    Handles count
     */
    public function count()
    {
        return count($this->requests);
    }
    
    /**
     * Setup options before execution
     * 
     * @return void
     */
    protected function initProcessing()
    {
        foreach ($this->requests as $k => $request) {
            if (isset($request->timeStart)) {
                continue;
            }
            $this->getDefaultOptions()->applyTo($request);
            $request->getOptions()->applyTo($request);
            $request->timeStart = microtime(true);
        }
    }
    
    /**
     * Sends requests in parallel
     *
     * @return void
     */
    public function send()
    {
        while ($this->socketPerform()) {
            if (!$this->socketSelect()) {
                return;
            }
        }
    }
    
    /**
     * Download available data on socket.
     * 
     * @return bool    TRUE when there are any requests on queue, FALSE when finished
     */
    public function socketPerform()
    {
        $this->initProcessing();
        
        do {
            $mrc = curl_multi_exec($this->mh, $this->running);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        
        $this->readAll();
        
        do {
            $mrc = curl_multi_exec($this->mh, $this->running);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        
        return $this->count() > 0;
    }
    
    /**
     * Waits until activity on socket
     * On success, returns TRUE. On failure, this function will
     * return FALSE on a select failure or timeout (from the underlying
     * select system call)
     * 
     * @param float $timeout Maximum time to wait
     * 
     * @return bool
     */
    public function socketSelect($timeout = 1)
    {
        return curl_multi_select($this->mh, $timeout) !== -1; 
    }
}
