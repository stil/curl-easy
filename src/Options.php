<?php
namespace cURL;

class Options extends Collection
{
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
            $const = strtoupper(substr($name, 3));
            $numericValue = ConstantsTable::findNumericValue($const);
            $this->set($numericValue, $args[0]);
        }
        return $this;
    }
}
