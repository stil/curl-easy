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
<?php
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->set(CURLOPT_TIMEOUT,5);
$ch->set(CURLOPT_RETURNTRANSFER,true);
$json = $ch->execute();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```
its equivalent to:
```php
<?php
$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->setTimeout(5);
$ch->setReturnTransfer(true);
$json = $ch->execute();
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```
and to plain old procedural cURL:
```php
<?php
$ch = curl_init('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json = curl_exec($ch);
$feed = json_decode($json, true);
echo $feed['entry']['title']['$t'];
```

##3 ways to set handler parameters
###Plain old cURL constants CURLOPT_*
Same as original cURL extension.
```php
<?php
$ch->set(CURLOPT_TIMEOUT,5);
$ch->set(CURLOPT_RETURNTRANSFER,true);
$ch->set(CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.1; WOW64; U; IBM EVV/3.0/EAK01AG9/LE; pl) Presto/2.10.229 Version/11.62');
```
###Intelligent setters based on constants
Just cut the *CURLOPT_* from constant name and prepend it with "set" to get name of method.
```php
<?php
$ch->setTimeout(5);
$ch->setReturnTransfer(true);
$ch->setUserAgent('Opera/9.80 (Windows NT 6.1; WOW64; U; IBM EVV/3.0/EAK01AG9/LE; pl) Presto/2.10.229 Version/11.62');
```
It is case insensitive. You can either do $ch->setUsErAgEnT() and $ch->setUSERAGENT() - it doesn't matter.

###And last method allows you set many parameters at once.
```php
<?php
$options=array(
	CURLOPT_TIMEOUT=>5,
	CURLOPT_RETURNTRANSFER=>true,
	CURLOPT_USERAGENT=>'Opera/9.80 (Windows NT 6.1; WOW64; U; IBM EVV/3.0/EAK01AG9/LE; pl) Presto/2.10.229 Version/11.62'
);
$ch->set($options);
```
##Parallel connections
```php
<?php
$defaultOptions = array( #Prepare default options
	CURLOPT_TIMEOUT => 5,
	CURLOPT_RETURNTRANSFER => true
);

$mh = new \cURL\MultiHandler; # Initialize cURL-multi

$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/uCg2BoKiuOM?v=2&alt=json');
$ch->set($defaultOptions);
$mh->attach($ch); # Attach first handler to queue

$ch = new \cURL\Handler('http://gdata.youtube.com/feeds/api/videos/IofN_sunFvo?v=2&alt=json');
$ch->set($defaultOptions);
$mh->attach($ch); # Attach second handler to queue

$mh->onComplete(function(\cURL\MultiHandler $mh, \cURL\Handler $ch) { # Callback on complete request
	$json = $ch->getContent(); # Returns content of response
	$feed = json_decode($json, true);
	echo $feed['entry']['title']['$t'] . "\n";
});

$mh->execute();
```