<?php
require __DIR__.'/autoload.php';

/**
 * EXAMPLE 1
 * SINGLE REQUEST WITH CURLOPT_* SETTINGS
 */

$videoId = 'uCg2BoKiuOM';

/**
 * Create new cURL handler.
 * We will download information about YouTube video from Google API.
 */
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/' . $videoId . '?v=2&alt=json');

/*
 * Set essential options to handler.
 */
$ch->set(CURLOPT_TIMEOUT, 5);
$ch->set(CURLOPT_RETURNTRANSFER, true);

/*
 * Execute and assign result to $json.
 */
$json = $ch->execute();


/*
 * Parse result and print title of video.
 */
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
