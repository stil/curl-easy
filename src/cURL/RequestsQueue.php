<?php
namespace cURL;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Countable;

class RequestsQueue extends EventDispatcher implements RequestsQueueInterface, Countable
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
     * @var int Amount of requests running
     */
    protected $running = 0;
    
    /**
     * @var array Array of requests attached
     */
    protected $requests = array();
    
    /**
     * Constructor
     * Utilise curl_multi_init()
     *
     * @return void
     */
    public function __construct()
    {
        $this->mh = curl_multi_init();
    }
    
    /**
     * Destructor
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
    protected function read()
    {
        while ($info = curl_multi_info_read($this->mh)) {
            $ch = $info['handle'];
            $request = $this->requests[(int)$ch];
            $this->detach($request);
            
            $event = new Event;
            $event->request = $request;
            $event->response = new Response($request, curl_multi_getcontent($request->getHandle()));
            if ($info['result'] !== CURLE_OK) {
                $event->response->setError(new Error(curl_error($ch), $info['result']));
            }
            $event->queue = $this;
            $this->dispatch('complete', $event);
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
        if ($this->count() == 0) {
            throw new Exception('Cannot perform if there are no requests in queue.');
        }
        
        foreach ($this->requests as $k => $request) {
            if (!$request->_running) {
                $this->getDefaultOptions()->applyTo($request);
                $request->getOptions()->applyTo($request);
                $request->_running = true;
            }
        }
        
        $before = $this->running;
        do {
            $mrc = curl_multi_exec($this->mh, $this->running);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        $after = $this->running;
        
        if ($after < $before) {
            $this->read();
        }
        
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
        if ($this->count() == 0) {
            throw new Exception('Cannot select if there are no requests in queue.');
        }
        return curl_multi_select($this->mh, $timeout) !== -1; 
    }
}
