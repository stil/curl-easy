<?php
namespace cURL;

interface RequestInterface {
    public function getOptions();
    public function setOptions(Options $options);
    
    public function getHandle();
    public function getUID();
    
    public function getInfo($opt);
    public function getContent();
    
    public function send();
}
