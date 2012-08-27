<?php
namespace cURL;

class Options {
    /**
     * @var array Array of cURL constants required for intelligent setters
     */
    protected static $curlConstantsTable = array();
    
    /**
     * @var array Array of cURL options
     */
    protected $options=array();
    
    /**
     * Converts current object to array
     * 
     * @return array
     */
    public function toArray() {
        return $this->options;
    }
    
    /**
     * Applies options to Request object
     * 
     * @param Request $request
     * 
     * @return bool    TRUE on success, FALSE on error
     */
    public function applyTo(Request $request) {
        if(!empty($this->options)) {
            //if(isset($this->options[CURLOPT_TIMEOUT])) {
            //    $this->options[CURLOPT_TIMEOUT_MS] = $this->options[CURLOPT_TIMEOUT];
            //    unset($this->options[CURLOPT_TIMEOUT]);
            //}
            //
            //if(isset($this->options[CURLOPT_TIMEOUT_MS])) {
            //    $request->timeout = $this->options[CURLOPT_TIMEOUT_MS];
            //}
            
            return curl_setopt_array($request->getHandle(), $this->options);
        }
        else return true;
    }
    
    /**
     * Prepares array for intelligent setters
     * 
     * @return void
     */
    public static function loadCurlConstantsTable() {
        $constants = get_defined_constants(true);
        $table = array();
        foreach ($constants['curl'] as $key => $value) {
            if (strpos($key, 'CURLOPT_') === 0) {
                //echo '`$ch->set('.$key.', $value)` | `$ch->set';
                //echo str_replace(' ','',ucwords(strtolower(str_ireplace(array('CURLOPT','_'),' ',$key)))).'($value)`'.PHP_EOL;
                
                $key = str_ireplace(array('CURLOPT', '_'), '', $key);
                $table[$key] = $value;
                //var_dump($key,$value);
            }
        }
        self::$curlConstantsTable = $table;
    }
    
    /**
     * Set option
     * 
     * @param mixed $opt   CURLOPT_* constant or array of CURLOPT_* constans
     * @param mixed $value Value for option
     * 
     * @return $this    Fluent interface
     */
    public function set($opt, $value = null) {
        if(is_array($opt)) {
            foreach($opt as $k=>$v) $this->options[$k]=$v;
        } else {
            $this->options[$opt]=$value;
        }
        return $this;
    }
    
    public function remove($opt) {
        unset($this->options[$opt]);
        return $this;
    }
    
    /**
     * Intelligent setters
     * 
     * @return $this    Fluent interface
     */
    public function __call($name, $args) {
        if (substr($name, 0, 3) == 'set' && isset($args[0])) {
            if (empty(self::$curlConstantsTable)) {
                self::loadCurlConstantsTable();
            }
            $const = strtoupper(substr($name, 3));
            if (isset(self::$curlConstantsTable[$const])) {
                $this->options[self::$curlConstantsTable[$const]]=$args[0];
                return $this;
            } else return false;
        }
    }
}