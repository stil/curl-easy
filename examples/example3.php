<?php
require __DIR__ . '/autoload.php';
/**
 * We will download info about 10 YouTube videos
 * I set the limit to 3 requests in parallel at once
 */

define('CONNECTIONS_LIMIT',3);

// IDs of videos to download
$videos=array(
    '6JnGBs88sL0',
    'XmSdTa9kaiQ',
    '6dC-sm5SWiU',
    'EqKpF6PsnSM',
    'oxqnFJ3lp5k',
    'fWNaR-rxAic',
    'uxo4D3K4988',
    'k4xjSzOX6PM',
    'bpOR_HuHRNs',
    'KRaWnd3LJfs'
);
 
// Init queue of requests
$queue = new \cURL\RequestsQueue;

// Set default options for all requests in queue
$queue->getDefaultOptions()
    ->set(CURLOPT_TIMEOUT, 5)
    ->set(CURLOPT_RETURNTRANSFER, true);

$counter = 0;

// Set function to be executed when request will be completed
$queue->onRequestComplete(function($queue, $request) use (&$counter) {
    $json = $request->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo ++$counter.'. '.$feed['entry']['title']['$t'] . PHP_EOL;
    
    // We will add remaining videos to queue if there are any
    addVideoToQueue();
});

function addVideoToQueue() {
    global $videos, $queue;
    $id = array_shift($videos);
    if($id) {
        $request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/'.$id.'?v=2&alt=json');
        $queue->attach($request);
    }
}

// We will add 3 videos to queue
for($i=0;$i<=CONNECTIONS_LIMIT;$i++) {
    addVideoToQueue();
}

$timeStart = microtime(true);
// Execute queue
$queue->send();
echo 'Time elapsed: '.round((microtime(true)-$timeStart)*1000,0).' miliseconds'.PHP_EOL;