#cURL wrapper for PHP
This is small but powerful and robust library which speeds the things up.
##Main features:
* lightweight library with low-level interface. It's not all-in-one library.
* parallel/asynchronous connections with very simple interface.
* attaching/detaching requests in parallel on run time!
* support for callbacks, so you can control execution process.
* intelligent setters as alternative to CURLOPT_* constants.
* if you know the cURL php extension, you don't have to learn things from beginning.

##Single request example
```php
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->setTimeout(5);
$ch->setReturnTransfer(true);
$json = $ch->execute();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```
its equivalent to:
```php
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->set(CURLOPT_TIMEOUT,5);
$ch->set(CURLOPT_RETURNTRANSFER,true);
$json = $ch->execute();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```
and to plain old procedural cURL:
```php
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->set(CURLOPT_TIMEOUT,5);
$ch->set(CURLOPT_RETURNTRANSFER,true);
$json = $ch->execute();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```