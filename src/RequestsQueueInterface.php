<?php
namespace cURL;

interface RequestsQueueInterface
{
    public function getDefaultOptions();
    public function setDefaultOptions(Options $defaultOptions);
    public function attach(Request $request);
    public function detach(Request $request);
    public function send();
    public function socketPerform();
    public function socketSelect($timeout);
}
