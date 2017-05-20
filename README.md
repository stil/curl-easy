[![Travis](https://img.shields.io/travis/stil/curl-easy.svg)](https://travis-ci.org/stil/curl-easy)
[![Latest Stable Version](https://poser.pugx.org/stil/curl-easy/v/stable)](https://packagist.org/packages/stil/curl-easy) [![Total Downloads](https://poser.pugx.org/stil/curl-easy/downloads)](https://packagist.org/packages/stil/curl-easy) [![License](https://poser.pugx.org/stil/curl-easy/license)](https://packagist.org/packages/stil/curl-easy)

# Table of contents
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
* [cURL\RequestQueue](#curlrequestqueue)
    * [RequestsQueue::__construct](#requestsqueue__construct)
    * [RequestsQueue::getDefaultOptions](#requestsqueuegetdefaultoptions)
    * [RequestsQueue::setDefaultOptions](#requestsqueuesetdefaultoptions)
    * [RequestsQueue::socketPerform](#requestsqueuesocketperform)
    * [RequestsQueue::socketSelect](#requestsqueuesocketselect)
    * [RequestsQueue::send](#requestsqueuesend)
* [cURL\Options](#curloptions)
    * [Options::set](#optionsset)
    * [Options::toArray](#optionstoarray)

## Introduction
### Description
This is small but powerful and robust library which speeds the things up. If you are tired of using PHP cURL extension with its procedural interface, but you want also keep control about script execution - it's great choice for you!
If you need high speed crawling in your project, you might be interested in stil/curl-easy extension - [stil/curl-robot](https://github.com/stil/curl-robot).
### Main features
* widely unit tested.
* lightweight library with moderate level interface. It's not all-in-one library.
* parallel/asynchronous connections with very simple interface.
* attaching/detaching requests in parallel on run time!
* support for callbacks, so you can control execution process.
* intelligent setters as alternative to CURLOPT_* constants.
* if you know the cURL php extension, you don't have to learn things from beginning

## Installation
In order to use cURL-PHP library you need to install the » libcurl package.

Install this library as [Composer](http://getcomposer.org) package with following command:
```bash
composer require stil/curl-easy
```
## Examples
### Single blocking request
```php
<?php
// We will check current Bitcoin price via API.
$request = new \cURL\Request('https://bitpay.com/rates/USD');
$request->getOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);
$response = $request->send();
$feed = json_decode($response->getContent(), true);
echo "Current Bitcoin price: " . $feed['data']['rate'] . " " . $feed['data']['code'] . "\n";
```
The above example will output:
```
Current Bitcoin price: 1999.97 USD
```

### Single non-blocking request
```php
<?php
// We will check current Bitcoin price via API.
$request = new \cURL\Request('https://bitpay.com/rates/USD');
$request->getOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);
$request->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
    $feed = json_decode($response->getContent(), true);
    echo "\nCurrent Bitcoin price: " . $feed['data']['rate'] . " " . $feed['data']['code'] . "\n";
});

while ($request->socketPerform()) {
    usleep(1000);
    echo '*';
}
```
The above example will output:
```
********************
Current Bitcoin price: 1997.48 USD
```

### Requests in parallel
```php
<?php
// We will download Bitcoin rates for both USD and EUR in parallel.

// Init requests queue.
$queue = new \cURL\RequestsQueue;
// Set default options for all requests in queue.
$queue->getDefaultOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);
// Set function to execute when request is complete.
$queue->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
    $json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo "Current Bitcoin price: " . $feed['data']['rate'] . " " . $feed['data']['code'] . "\n";
});

$request = new \cURL\Request('https://bitpay.com/rates/USD');
// Add request to queue
$queue->attach($request);

$request = new \cURL\Request('https://bitpay.com/rates/EUR');
$queue->attach($request);

// Execute queue
$timeStart = microtime(true);
$queue->send();
$elapsedMs = (microtime(true) - $timeStart) * 1000;
echo 'Elapsed time: ' . round($elapsedMs) . " ms\n";
```
The above example will output:
```
Current Bitcoin price: 1772.850062 EUR
Current Bitcoin price: 1987.01 USD
Elapsed time: 284 ms
```

### Non-blocking requests in parallel
```php
<?php
// We will download Bitcoin rates for both USD and EUR in parallel.

// Init requests queue.
$queue = new \cURL\RequestsQueue;
// Set default options for all requests in queue.
$queue->getDefaultOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);
// Set function to execute when request is complete.
$queue->addListener('complete', function (\cURL\Event $event) {
    $response = $event->response;
    $json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo "\nCurrent Bitcoin price: " . $feed['data']['rate'] . " " . $feed['data']['code'] . "\n";
});

$request = new \cURL\Request('https://bitpay.com/rates/USD');
// Add request to queue
$queue->attach($request);

$request = new \cURL\Request('https://bitpay.com/rates/EUR');
$queue->attach($request);

// Execute queue
$timeStart = microtime(true);
while ($queue->socketPerform()) {
    usleep(1000);
    echo '*';
}
$elapsedMs = (microtime(true) - $timeStart) * 1000;
echo 'Elapsed time: ' . round($elapsedMs) . " ms\n";
```
The above example will output something like that:
```
*****************************************************************************************************************************************************
Current Bitcoin price: 1772.145208 EUR
************************************************************************
Current Bitcoin price: 1986.22 USD
Elapsed time: 374 ms
```

### Processing queue of multiple requests while having maximum 2 at once executed at the moment
```php
$requests = [];
$currencies = ['USD', 'EUR', 'JPY', 'CNY'];
foreach ($currencies as $code) {
    $requests[] = new \cURL\Request('https://bitpay.com/rates/' . $code);
}

$queue = new \cURL\RequestsQueue;
$queue
    ->getDefaultOptions()
    ->set(CURLOPT_RETURNTRANSFER, true);

$queue->addListener('complete', function (\cURL\Event $event) use (&$requests) {
    $response = $event->response;
    $json = $response->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo "Current Bitcoin price: " . $feed['data']['rate'] . " " . $feed['data']['code'] . "\n";

    if ($next = array_pop($requests)) {
        $event->queue->attach($next);
    }
});

$queue->attach(array_pop($requests));
$queue->attach(array_pop($requests));
$queue->send();
```
The above example will output something like that:
```
Current Bitcoin price: 220861.025 JPY
Current Bitcoin price: 13667.81675 CNY
Current Bitcoin price: 1771.0567 EUR
Current Bitcoin price: 1985 USD
```
### Intelligent Options setting
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
### Error handling
You can access cURL error codes in Response class.
Examples:
```php
$request = new \cURL\Request('http://non-existsing-page/');
$response = $request->send();

if ($response->hasError()) {
    $error = $response->getError();
    echo 'Error code: ' . $error->getCode() . "\n";
    echo 'Message: "' . $error->getMessage() . '"' . "\n";
}
```
Probably above example will output
```
Error code: 6
Message: "Could not resolve host: non-existsing-page; Host not found"
```
You can find all of CURLE_* error codes [here](http://php.net/manual/en/curl.constants.php).
## cURL\Request
### Request::__construct
### Request::getOptions
### Request::setOptions
### RequestsQueue::socketPerform
### RequestsQueue::socketSelect
### Request::send
## cURL\RequestQueue
### RequestsQueue::__construct
### RequestsQueue::getDefaultOptions
### RequestsQueue::setDefaultOptions
### RequestsQueue::socketPerform
### RequestsQueue::socketSelect
### RequestsQueue::send
## cURL\Response
### Response::getContent
### Response::getInfo
### Response::hasError
### Response::getError
## cURL\Options
### Options::set
### Options::toArray
## cURL\Error
### Error::getCode
### Error::getMessage
