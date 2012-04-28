<?php
namespace cURL;

class Handler
{
	/**
	 * @var resource cURL Handler
	 */
	protected $ch;
	
	static protected $curlConstantsTable = array();
	
	/**
	 * Unix timestamp with microseconds.
	 * CURLOPT_TIMEOUT does not work properly with the regular multi and multi_socket interfaces.
	 * The work-around for apps is to simply remove the easy handle once the time is up.
	 * See also: http://curl.haxx.se/bug/view.cgi?id=250145
	 * @var float
	 */
	public $timeStart = null;
	
	/**
	 * @var float Timeout in seconds. Zero means no timeout.
	 */
	public $timeout = 0;
	
	/**
	 * Create new cURL handle
	 *
	 * @param string $url The URL to fetch.
	 *
	 * @return void
	 */
	public function __construct($url = null)
	{
		if ($url) $this->ch = curl_init($url);
		else $this->ch = curl_init();
	}
	
	/**
	 * Returns cURL raw resource.
	 *
	 * @return resource    cURL resource
	 */
	public function getResource()
	{
		return $this->ch;
	}
	
	/**
	 * Returns unique id of cURL resource. Useful for debugging or logging.
	 *
	 * @return int    Unique id of resource.
	 */
	public function getResourceID()
	{
		return (int)$this->ch;
	}
	
	public static function loadCurlConstantsTable() {
		$constants=get_defined_constants(true);
		$table=array();
		foreach($constants['curl'] as $key=>$value) {
			if(strpos($key,'CURLOPT_')===0) {
				$key=str_ireplace(array('CURLOPT','_'),'',$key);
				$table[$key]=$value;
			}
		}
		self::$curlConstantsTable=$table;
	}
	
	/**
	 * Sets an option for a cURL transfer
	 *
	 * @param mixed $opt   Array of options or single option name to set
	 * @param mixed $value Value of option (only when setting single option)
	 *
	 * @return Handler    Returns reference to itself for fluent interface.
	 */
	public function set($opt, $value = null)
	{
		if (is_array($opt)) {
			if (isset($opt[CURLOPT_TIMEOUT])) $this->timeout = $opt[CURLOPT_TIMEOUT];
			if (isset($opt[CURLOPT_TIMEOUT_MS])) $this->timeout = $opt[CURLOPT_TIMEOUT] / 1000;
			curl_setopt_array($this->ch, $opt);
			return $this;
		} else {
			if ($opt == CURLOPT_TIMEOUT) $this->timeout = $value;
			elseif ($opt == CURLOPT_TIMEOUT_MS) $this->timeout = $value / 1000;
			curl_setopt($this->ch, $opt, $value);
			return $this;
		}
	}
	
	public function __call($name,$args) {
		if(substr($name,0,3)=='set' && isset($args[0])) {
			if(empty(self::$curlConstantsTable)) {
				self::loadCurlConstantsTable();
			}
			$const=strtoupper(substr($name,3));			
			
			if(isset(self::$curlConstantsTable[$const])) {
				return $this->set(self::$curlConstantsTable[$const], $args[0]);
			} else return false;
		}
	}
	
	/**
	 * Get information regarding a current transfer
	 *
	 * @param int $opt One of the CURLINFO_* constants
	 *
	 * @return mixed    If opt is given, returns its value as a string. Otherwise, returns an associative array with the following elements (which correspond to opt), or FALSE on failure.
	 */
	public function getInfo($opt = 0)
	{
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
	public function execute()
	{
		return curl_exec($this->ch);
	}
	
	/**
	 * Returns the content of a cURL handle if CURLOPT_RETURNTRANSFER is set.
	 * Equivalent to curl_multi_getcontent().
	 * Use it only when making parallel connections.
	 *
	 * @return string    Content of a cURL handle if CURLOPT_RETURNTRANSFER is set.
	 */
	public function getContent()
	{
		return curl_multi_getcontent($this->ch);
	}
	
	/**
	 * Closes cURL resource and frees the memory.
	 * It is neccessary when you make a lot of handles
	 * and you want to avoid fill up the memory.
	 *
	 * @return void
	 */
	public function close()
	{
		if (isset($this->ch)) curl_close($this->ch);
	}
}
