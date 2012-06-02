<?php
require __DIR__ . '/autoload.php';
/**
 * Single request example
 * Will download title of YouTube video:
 * http://youtu.be/6dC-sm5SWiU
 */
$request = new \cURL\Request('http://gdata.youtube.com/feeds/api/videos/_PsdGQ96ah4?v=2&alt=json');
$request->getOptions()
        ->set(CURLOPT_TIMEOUT, 5)
        ->set(CURLOPT_RETURNTRANSFER, true);
/* Also possible:
$request->getOptions()
        ->setTimeout(5)
        ->setReturnTransfer(true); */
$json = $request->send();
$feed = json_decode($json, true);

echo 'Title: ' . $feed['entry']['title']['$t'] . PHP_EOL;

if ($feed['entry']['id']['$t'] == 'tag:youtube.com,2008:video:_PsdGQ96ah4') {
    echo 'Completed successfully';
} else {
    echo 'Example failed';
}

echo PHP_EOL;
