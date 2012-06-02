<?php
require __DIR__ . '/autoload.php';
/**
 * We will download info about 2 YouTube videos:
 * http://youtu.be/XmSdTa9kaiQ and
 * http://youtu.be/6dC-sm5SWiU
 */

// Init queue of requests
$queue = new \cURL\RequestsQueue;
// Set default options for all requests in queue
$queue->getDefaultOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);
// Set function to be executed when request will be completed
$queue->onRequestComplete(function($queue, $request){
    $json = $request->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo $feed['entry']['title']['$t'] . PHP_EOL;
});

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/XmSdTa9kaiQ?v=2&alt=json');
// Add request to queue
$queue->attach($request);

$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/6dC-sm5SWiU?v=2&alt=json');
$queue->attach($request);

// Execute queue
$queue->send();