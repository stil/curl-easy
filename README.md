#Table of contents
* [Introduction](#introduction)
    * [Description](#description)
	* [Main Features](#mainfeatures)
* [Installation](#installation)
* [Examples](#examples)
    * [Single request](#singlerequest)
    * [Requests in parallel](#requestsinparallel)
* [cURL\Request](#curlrequest)
    * [Request::__construct](#request__construct)
    * [Request::getOptions](#requestgetoptions)
    * [Request::setOptions](#requestsetoptions)
    * [Request::getContent](#requestgetcontent)
    * [Request::getInfo](#requestgetinfo)
    * [Request::send](#requestsend)
* [cURL\RequesstQueue](#curlrequestsqueue)
    * [RequestsQueue::__construct](#requestsqueue__construct)
    * [RequestsQueue::getDefaultOptions](#requestsqueuegetdefaultoptions)
    * [RequestsQueue::setDefaultOptions](#requestsqueuesetdefaultoptions)
    * [RequestsQueue::socketPerform](#requestsqueuesocketperform)
    * [RequestsQueue::socketSelect](#requestsqueuesocketselect)
    * [RequestsQueue::send](#requestsqueuesend)
* [cURL\Options](#curloptions)
    * [Options::set](#optionsset)
    * [Options::toArray](#optionstoarray)

##Introduction
###Description
This is small but powerful and robust library which speeds the things up. If you are tired of using PHP cURL extension with its procedural interface, but you want also keep control about script execution - it's great choice for you!
###Main features
* lightweight library with moderate level interface. It's not all-in-one library.
* parallel/asynchronous connections with very simple interface.
* attaching/detaching requests in parallel on run time!
* support for callbacks, so you can control execution process.
* intelligent setters as alternative to CURLOPT_* constants.
* if you know the cURL php extension, you don't have to learn things from beginning.
##Installation
In order to use cURL-PHP library you need to install the » libcurl package.
It also requires PHP 5.3 or newer.

You can provide autoloading to your classes if you don't want to include them manually.
```php
<?php
spl_autoload_register(function ($class) {
    require 'D:/lib/'.strtr($class, '\\', '/') . '.php';
});
```
##Examples
###Single request
```php
<?php
// We will download info about YouTube video: http://youtu.be/_PsdGQ96ah4
$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/PsdGQ96ah4?v=2&alt=json');
$request->getOptions()
	->set(CURLOPT_TIMEOUT, 5)
	->set(CURLOPT_RETURNTRANSFER, true);
$json = $request->send();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
// The title is: "Karmah - Just be good to me"
```
###Requests in parallel
##cURL\Request
###Request::__construct
###Request::getOptions
###Request::setOptions
###Request::getContent
###Request::getInfo
###Request::send
##cURL\RequesstQueue
###RequestsQueue::__construct
###RequestsQueue::getDefaultOptions
###RequestsQueue::getDefaultOptions
###RequestsQueue::setDefaultOptions
###RequestsQueue::socketPerform
###RequestsQueue::socketSelect
###RequestsQueue::send
##cURL\Options
###Options::set
###Options::toArray