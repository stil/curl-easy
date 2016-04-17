[![Latest Stable Version](https://poser.pugx.org/stil/curl-easy/v/stable)](https://packagist.org/packages/stil/curl-easy) [![Total Downloads](https://poser.pugx.org/stil/curl-easy/downloads)](https://packagist.org/packages/stil/curl-easy) [![License](https://poser.pugx.org/stil/curl-easy/license)](https://packagist.org/packages/stil/curl-easy)

#Table of contents
* [Introduction](#introduction)
    * [Description](#description)
	* [Main Features](#main-features)
* [Installation](#installation)
* [Examples](#examples)
* [cURL\Request](#curlrequest)
    * [Request::__construct](#request__construct)
    * [Request::getOptions](#requestgetoptions)
    * [Request::setOptions](#requestsetoptions)
    * [Request::getContent](#requestgetcontent)
    * [Request::getInfo](#requestgetinfo)
    * [Request::send](#requestsend)
* [cURL\RequestQueue](#curlrequestsqueue)
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
If you need high speed crawling in your project, you might be interested in stil/curl-easy extension - [stil/curl-robot](https://github.com/stil/curl-robot).
###Main features
* widely unit tested.
* lightweight library with moderate level interface. It's not all-in-one library.
* parallel/asynchronous connections with very simple interface.
* attaching/detaching requests in parallel on run time!
* support for callbacks, so you can control execution process.
* intelligent setters as alternative to CURLOPT_* constants.
* if you know the cURL php extension, you don't have to learn things from beginning

##Installation
In order to use cURL-PHP library you need to install the » libcurl package.
It also requires PHP 5.3 or newer and Symfony's EventDispatcher 2.1.* or newer.

[Composer](http://getcomposer.org) is recommended for installation.
```json
{
    "require": {
        "stil/curl-easy": "*"
    }
}
```
##Examples
###Single request with blocking
```php
<?php
// We will download info about YouTube video: http://youtu.be/_PsdGQ96ah4
$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/_PsdGQ96ah4?v=2&alt=json');
$request->getOptions()
	->set(CURLOPT_TIMEOUT, 5)
	->set(CURLOPT_RETURNTRANSFER, true);
$response = $request->send();
$feed = json_decode($response->getContent(), true);
echo $feed['entry']['title']['$t'];
```
The above example will output:
`Karmah - Just be good to me`
###Single request without blocking
```php
<?php
// We will download info about YouTube video: http://youtu.be/_PsdGQ96ah4
$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/_PsdGQ96ah4?v=2&alt=json');
$request->getOptions()
	->set(CURLOPT_TIMEOUT, 5)
	->set(CURLOPT_RETURNTRANSFER, true);
$request->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
    $feed = json_decode($response->getContent(), true);
    echo $feed['entry']['title']['$t'];
});


while ($request->socketPerform()) {
    // do anything else when the requests are processed
    $request->socketSelect();
    // line below pauses execution until there's new data on socket
}
```
The above example will output:
`Karmah - Just be good to me`
###Requests in parallel
```php
<?php
// We will download info about 2 YouTube videos:
// http://youtu.be/XmSdTa9kaiQ and
// http://youtu.be/6dC-sm5SWiU

// Init queue of requests
$queue = new \cURL\RequestsQueue;
// Set default options for all requests in queue
$queue->getDefaultOptions()
	->set(CURLOPT_TIMEOUT, 5)
	->set(CURLOPT_RETURNTRANSFER, true);
// Set function to be executed when request will be completed
$queue->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
	$json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo $feed['entry']['title']['$t'] . "\n";
});

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/XmSdTa9kaiQ?v=2&alt=json');
// Add request to queue
$queue->attach($request);

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/6dC-sm5SWiU?v=2&alt=json');
$queue->attach($request);

// Execute queue
$queue->send();
```
The above example will output:
```
Kool & The Gang - Fresh - 2004
U2 - With Or Without You
```
###Non-blocking requests in parallel
```php
<?php
// We will download info about 2 YouTube videos:
// http://youtu.be/XmSdTa9kaiQ and
// http://youtu.be/6dC-sm5SWiU

// Init queue of requests
$queue = new \cURL\RequestsQueue;
// Set default options for all requests in queue
$queue->getDefaultOptions()
	->set(CURLOPT_TIMEOUT, 5)
	->set(CURLOPT_RETURNTRANSFER, true);
// Set function to be executed when request will be completed
$queue->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
	$json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo $feed['entry']['title']['$t'] . "\n";
});

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/XmSdTa9kaiQ?v=2&alt=json');
// Add request to queue
$queue->attach($request);

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/6dC-sm5SWiU?v=2&alt=json');
$queue->attach($request);

// Execute queue
while ($queue->socketPerform()) {
    echo '*';
    $queue->socketSelect();
}
```
The above example will output something like that:
```
***Kool & The Gang - Fresh - 2004
**U2 - With Or Without You
```
###Adding new requests on runtime
```php
$requests = array();
$videos = array('tv0IEwypXkY', 'p8EH1_jZBl4', 'pXxwxEb3akc', 'Fh-O6nvQr9Q', '31vXOeV67PQ');
foreach ($videos as $id) {
    $requests[] = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/'.$id.'?v=2&alt=json');
}

$queue = new \cURL\RequestsQueue;
$queue
    ->getDefaultOptions()
    ->set(CURLOPT_RETURNTRANSFER, true);

$queue->addListener('complete', function (\cURL\Event $event) use (&$requests) {
    $response = $event->response;
    $json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo $feed['entry']['title']['$t'] . "\n";
    
    $next = array_pop($requests);
    if ($next) {
        $event->queue->attach($next);
    }
});

$queue->attach(array_pop($requests));
$queue->send();
```
The above example will output something like that:
```
Kid Cudi - Cudi Zone
Kid Cudi-I Be High
Kid Cudi - Marijuana
Kid Cudi - Trapped In My Mind (HQ)
KiD Cudi - Don't Play This Song **LYRICS** [ Man On The Moon II ]
```
###Intelligent Options setting
Replace `CURLOPT_*` with `set*()` and you will receive method name.
Examples:
```php
$opts = new \cURL\Options;

$opts->set(CURLOPT_URL, $url);
// it is equivalent to
// $opts->setUrl($url);

$opts->set(CURLOPT_RETURNTRANSFER, true);
// it is equivalent to
// $opts->setReturnTransfer(true);
// or
// $opts->setReTURNTranSFER(true);
// character case does not matter

$opts->set(CURLOPT_TIMEOUT, 5);
// it is equivalent to
// $opts->setTimeout(5);

// then you can assign options to Request

$request = new \cURL\Request;
$request->setOptions($opts);

// or make it default in RequestsQueue

$queue = new \cURL\RequestsQueue;
$queue->setDefaultOptions($opts);
```
###Error handling
You can access cURL error codes in Response class.
Examples:
```php
$request = new \cURL\Request('http://non-existsing-page/');
$response = $request->send();

if ($response->hasError()) {
    $error = $response->getError();
    echo
        'Error code: '.$error->getCode()."\n".
        'Message: "'.$error->getMessage().'"';
}
```
Probably above example will output
```
Error code: 6
Message: "Could not resolve host: non-existsing-page; Host not found"
```
You can find all of CURLE_* error codes [here](http://php.net/manual/en/curl.constants.php).
##cURL\Request
###Request::__construct
###Request::getOptions
###Request::setOptions
###RequestsQueue::socketPerform
###RequestsQueue::socketSelect
###Request::send
##cURL\RequestQueue
###RequestsQueue::__construct
###RequestsQueue::getDefaultOptions
###RequestsQueue::setDefaultOptions
###RequestsQueue::socketPerform
###RequestsQueue::socketSelect
###RequestsQueue::send
##cURL\Response
###Response::getContent
###Response::getInfo
###Response::hasError
###Response::getError
##cURL\Options
###Options::set
###Options::toArray
##cURL\Error
###Error::getCode
###Error::getMessage
