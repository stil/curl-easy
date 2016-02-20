<?php
namespace cURL;

interface RequestInterface
{
    public function getOptions();
    public function setOptions(Options $options);
    public function getUID();
    public function socketPerform();
    public function socketSelect($timeout);
    public function send();
}
