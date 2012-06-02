<?php
namespace cURL;

interface RequestInterface {
    public function getOptions();
    public function setOptions(Options $options);
    
    public function getHandle();
    public function getUID();
    
    public function getInfo($opt);
    public function getErrorMessage();
    public function getErrorCode();
    public function getContent();
    
    public function send();
}
