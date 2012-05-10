<?php
require __DIR__.'/autoload.php';

/**
 * EXAMPLE 2
 * SINGLE REQUEST WITH INTELLIGENT SETTERS
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
$ch->setTimeout(5);
$ch->setReturnTransfer(true);

/*
 * Execute and assign result to $json.
 */
$json = $ch->execute();


/*
 * Parse result and print title of video.
 */
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
