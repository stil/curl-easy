<?php
namespace cURL;

class Collection
{
    /**
     * @var array Collection
     */
    protected $data = array();
    
    /**
     * Converts current object to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Sets value
     *
     * @param mixed $key Key   
     * @param mixed $value Value
     * @return self
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }
    
    /**
     * Checks if key does exist
     *
     * @param mixed $key Key
     * @return bool    TRUE if exists, FALSE otherwise
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }
    
    /**
     * Returns value of $key
     *
     * @param mixed $key Key
     * @throws Exception Key does not exist
     * @return mixed    Value of key
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->data[$key];
        } else {
            throw new Exception('Key does not exist.');
        }
    }
    
    /**
     * Removes key
     *
     * @param mixed $key Key to remove
     * @return self
     */
    public function remove($key)
    {
        unset($this->data[$key]);
        return $this;
    }
}
