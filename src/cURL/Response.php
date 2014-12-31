<?php
namespace cURL;

class Response
{
    protected $ch;
    protected $error;
    protected $content = null;
    
    /**
     * Constructs response
     * 
     * @param Request $request Request
     * @param string  $content Content of reponse
     */
    public function __construct(Request $request, $content = null)
    {
        $this->ch = $request->getHandle();
        
        if (is_string($content)) {
            $this->content = $content;
        }
    }
    
    /**
     * Get information regarding a current transfer
     * If opt is given, returns its value as a string
     * Otherwise, returns an associative array with
     * the following elements (which correspond to opt), or FALSE on failure.
     *
     * @param int $key One of the CURLINFO_* constants
     * @return mixed
     */
    public function getInfo($key = null)
    {
        return $key === null ? curl_getinfo($this->ch) : curl_getinfo($this->ch, $key);
    }
    
    /**
     * Returns content of request
     * 
     * @return string    Content
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Sets error instance
     * 
     * @param Error $error Error to set
     * @return void
     */
    public function setError(Error $error)
    {
        $this->error = $error;
    }
    
    /**
     * Returns a error instance
     * 
     * @return Error|null
     */
    public function getError()
    {
        return isset($this->error) ? $this->error : null;
    }
    
    /**
     * Returns the error number for the last cURL operation.    
     * 
     * @return int  Returns the error number or 0 (zero) if no error occurred. 
     */
    public function hasError()
    {
        return isset($this->error);
    }
}
