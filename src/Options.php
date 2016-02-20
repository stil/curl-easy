<?php
namespace cURL;

class Options extends Collection
{
    /**
     * @var array Array of cURL constants required for intelligent setters
     */
    protected static $curlConstantsTable = array();
    
    /**
     * Applies options to Request object
     *
     * @param Request $request
     * @return self
     */
    public function applyTo(Request $request)
    {
        if (!empty($this->data)) {
            curl_setopt_array($request->getHandle(), $this->data);
        }
        
        return $this;
    }
    
    /**
     * Prepares array for intelligent setters
     *
     * @return void
     */
    public static function loadCurlConstantsTable()
    {
        $constants = get_defined_constants(true);
        $table = array();
        foreach ($constants['curl'] as $key => $value) {
            if (strpos($key, 'CURLOPT_') === 0) {
                $key = str_ireplace(array('CURLOPT', '_'), '', $key);
                $table[$key] = $value;
            }
        }
        self::$curlConstantsTable = $table;
    }
    
    /**
     * Intelligent setters
     *
     * @param string $name Function name
     * @param array $args Arguments
     * @throws Exception Invalid CURLOPT_ constant has been specified
     * @return self
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 3) == 'set' && isset($args[0])) {
            if (empty(self::$curlConstantsTable)) {
                self::loadCurlConstantsTable();
            }
            $const = strtoupper(substr($name, 3));
            if (isset(self::$curlConstantsTable[$const])) {
                $this->data[self::$curlConstantsTable[$const]] = $args[0];
                return $this;
            } else {
                throw new Exception('Constant CURLOPT_'.$const.' does not exist.');
            }
        }
    }
}
