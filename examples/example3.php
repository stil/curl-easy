<?php
require __DIR__.'/autoload.php';

/**
 * EXAMPLE 3
 * PARALLEL (ASYNCHRONOUS) REQUESTS
 */

/**
 * Prepare default cURL options
 */
$defaultOptions = array(
    CURLOPT_TIMEOUT => 5,
    CURLOPT_RETURNTRANSFER => true
);

/**
 * Initialize cURL-Multi handler
 */
$mh = new \cURL\MultiHandler;

/**
 * Prepare first handler
 */
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->set($defaultOptions);
/**
 * Attach first handler to queue
 */
$mh->attach($ch);


/**
 * Prepare second handler
 */
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/IofN_sunFvo?v=2&alt=json');
$ch->set($defaultOptions);
/**
 * Attach second handler to queue
 */
$mh->attach($ch);


/**
 * Add callback function when request will be complete
 */
$mh->onComplete(function(\cURL\MultiHandler $mh, \cURL\Handler $ch) {
    $json = $ch->getContent(); // Returns content of response
    $feed = json_decode($json, true);
    echo $feed['entry']['title']['$t'] . "\n";
});

$mh->execute();