<?php
namespace cURL;
class Request implements RequestInterface {
    /**
     * @var resource cURL Handler
     */
    protected $ch;
    
    /**
     * @var Options Object containing options for current request
     */
    protected $options = null;
    
    /**
     * Unix timestamp with microseconds, used only for async connections
     * CURLOPT_TIMEOUT does not work properly with the regular multi and multi_socket interfaces.
     * The work-around for apps is to simply remove the easy handle once the time is up.
     * See also: http://curl.haxx.se/bug/view.cgi?id=250145
     * FOR INTERNAL USE ONLY
     * @var float
     */
    public $timeStart = null;
    
    /**
     * @var float Timeout in seconds. Zero means no timeout.
     * FOR INTERNAL USE ONLY
     */
    public $timeout = 0;
    
    /**
     * Create new cURL handle
     *
     * @param string $url The URL to fetch.
     *
     * @return void
     */
    public function __construct($url = null) {
        $this->ch = curl_init($url);
    }
    
    /**
     * Closes cURL resource and frees the memory.
     * It is neccessary when you make a lot of requests
     * and you want to avoid fill up the memory.
     *
     * @return void
     */
    public function __destruct() {
        if (isset($this->ch)) curl_close($this->ch);
    }
    
    /**
     * Get the cURL\Options object
     *
     * @return Options
     */
    public function getOptions() {
        if (!isset($this->options)) {
            $this->options = new Options;
        }
        return $this->options;
    }
    
    /**
     * Set the cURL\Options object
     *
     * @return Options
     */
    public function setOptions(Options $options) {
        $this->options = $options;
    }
    
    /**
     * Get raw cURL handle
     *
     * @return resource
     */
    public function getHandle() {
        return $this->ch;
    }
    
    /**
     * Get unique id of cURL handle
     * Useful for debugging or logging.
     *
     * @return int
     */
    public function getUID() {
        return (int)$this->ch;
    }
    
    /**
     * Get information regarding a current transfer
     * If opt is given, returns its value as a string
     * Otherwise, returns an associative array with the following elements (which correspond to opt), or FALSE on failure.
     *
     * @param int $opt One of the CURLINFO_* constants
     *
     * @return mixed
     */
    public function getInfo($opt = 0) {
        if ($opt == 0) return curl_getinfo($this->ch);
        else return curl_getinfo($this->ch, $opt);
    }
    
    /**
     * Perform a cURL session.
     * Equivalent to curl_exec().
     * This function should be called after initializing a cURL
     * session and all the options for the session are set.
     *
     * @return mixed    TRUE on success or FALSE on failure. However, if the CURLOPT_RETURNTRANSFER option is set, it will return the result on success, FALSE on failure.
     */
    public function send() {
        $this->options->applyTo($this);
        return curl_exec($this->ch);
    }
    
    /**
     * Returns the content of a cURL handle if CURLOPT_RETURNTRANSFER is set.
     * Equivalent to curl_multi_getcontent().
     * Use it only when making parallel connections.
     *
     * @return string    Content of a cURL handle if CURLOPT_RETURNTRANSFER is set.
     */
    public function getContent() {
        return curl_multi_getcontent($this->ch);
    }
}
