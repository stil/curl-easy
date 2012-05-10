#cURL wrapper for PHP
##Description
This is small but powerful and robust library which speeds the things up.
If you are tired of using PHP cURL extension with its procedural interface, but you want also keep control about script execution - it's great choice for you!
##Main features
* lightweight library with moderate level interface. It's not all-in-one library.
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

#Options CURLOPT_* mapping
CURLOPT_*  | Intelligent setter
------------- | -------------
`$ch->set(CURLOPT_IPRESOLVE, $value)` | `$ch->setIpResolve($value)`
`$ch->set(CURLOPT_DNS_USE_GLOBAL_CACHE, $value)` | `$ch->setDnsUseGlobalCache($value)`
`$ch->set(CURLOPT_DNS_CACHE_TIMEOUT, $value)` | `$ch->setDnsCacheTimeout($value)`
`$ch->set(CURLOPT_PORT, $value)` | `$ch->setPort($value)`
`$ch->set(CURLOPT_FILE, $value)` | `$ch->setFile($value)`
`$ch->set(CURLOPT_READDATA, $value)` | `$ch->setReadData($value)`
`$ch->set(CURLOPT_INFILE, $value)` | `$ch->setInFile($value)`
`$ch->set(CURLOPT_INFILESIZE, $value)` | `$ch->setInFileSize($value)`
`$ch->set(CURLOPT_URL, $value)` | `$ch->setUrl($value)`
`$ch->set(CURLOPT_PROXY, $value)` | `$ch->setProxy($value)`
`$ch->set(CURLOPT_VERBOSE, $value)` | `$ch->setVerbose($value)`
`$ch->set(CURLOPT_HEADER, $value)` | `$ch->setHeader($value)`
`$ch->set(CURLOPT_HTTPHEADER, $value)` | `$ch->setHttpHeader($value)`
`$ch->set(CURLOPT_NOPROGRESS, $value)` | `$ch->setNoProgress($value)`
`$ch->set(CURLOPT_PROGRESSFUNCTION, $value)` | `$ch->setProgressFunction($value)`
`$ch->set(CURLOPT_NOBODY, $value)` | `$ch->setNoBody($value)`
`$ch->set(CURLOPT_FAILONERROR, $value)` | `$ch->setFailOnError($value)`
`$ch->set(CURLOPT_UPLOAD, $value)` | `$ch->setUpload($value)`
`$ch->set(CURLOPT_POST, $value)` | `$ch->setPost($value)`
`$ch->set(CURLOPT_FTPLISTONLY, $value)` | `$ch->setFtpListOnly($value)`
`$ch->set(CURLOPT_FTPAPPEND, $value)` | `$ch->setFtpAppend($value)`
`$ch->set(CURLOPT_NETRC, $value)` | `$ch->setNetrc($value)`
`$ch->set(CURLOPT_FOLLOWLOCATION, $value)` | `$ch->setFollowLocation($value)`
`$ch->set(CURLOPT_PUT, $value)` | `$ch->setPut($value)`
`$ch->set(CURLOPT_USERPWD, $value)` | `$ch->setUserPwd($value)`
`$ch->set(CURLOPT_PROXYUSERPWD, $value)` | `$ch->setProxyUserPwd($value)`
`$ch->set(CURLOPT_RANGE, $value)` | `$ch->setRange($value)`
`$ch->set(CURLOPT_TIMEOUT, $value)` | `$ch->setTimeout($value)`
`$ch->set(CURLOPT_TIMEOUT_MS, $value)` | `$ch->setTimeoutMs($value)`
`$ch->set(CURLOPT_POSTFIELDS, $value)` | `$ch->setPostFields($value)`
`$ch->set(CURLOPT_REFERER, $value)` | `$ch->setReferer($value)`
`$ch->set(CURLOPT_USERAGENT, $value)` | `$ch->setUserAgent($value)`
`$ch->set(CURLOPT_FTPPORT, $value)` | `$ch->setFtpPort($value)`
`$ch->set(CURLOPT_FTP_USE_EPSV, $value)` | `$ch->setFtpUseEpsv($value)`
`$ch->set(CURLOPT_LOW_SPEED_LIMIT, $value)` | `$ch->setLowSpeedLimit($value)`
`$ch->set(CURLOPT_LOW_SPEED_TIME, $value)` | `$ch->setLowSpeedTime($value)`
`$ch->set(CURLOPT_RESUME_FROM, $value)` | `$ch->setResumeFrom($value)`
`$ch->set(CURLOPT_COOKIE, $value)` | `$ch->setCookie($value)`
`$ch->set(CURLOPT_COOKIESESSION, $value)` | `$ch->setCookieSession($value)`
`$ch->set(CURLOPT_AUTOREFERER, $value)` | `$ch->setAutoReferer($value)`
`$ch->set(CURLOPT_SSLCERT, $value)` | `$ch->setSslCert($value)`
`$ch->set(CURLOPT_SSLCERTPASSWD, $value)` | `$ch->setSslCertPasswd($value)`
`$ch->set(CURLOPT_WRITEHEADER, $value)` | `$ch->setWriteHeader($value)`
`$ch->set(CURLOPT_SSL_VERIFYHOST, $value)` | `$ch->setSslVerifyhost($value)`
`$ch->set(CURLOPT_COOKIEFILE, $value)` | `$ch->setCookieFile($value)`
`$ch->set(CURLOPT_SSLVERSION, $value)` | `$ch->setSslVersion($value)`
`$ch->set(CURLOPT_TIMECONDITION, $value)` | `$ch->setTimeCondition($value)`
`$ch->set(CURLOPT_TIMEVALUE, $value)` | `$ch->setTimeValue($value)`
`$ch->set(CURLOPT_CUSTOMREQUEST, $value)` | `$ch->setCustomRequest($value)`
`$ch->set(CURLOPT_STDERR, $value)` | `$ch->setStderr($value)`
`$ch->set(CURLOPT_TRANSFERTEXT, $value)` | `$ch->setTransferText($value)`
`$ch->set(CURLOPT_RETURNTRANSFER, $value)` | `$ch->setReturnTransfer($value)`
`$ch->set(CURLOPT_QUOTE, $value)` | `$ch->setQuote($value)`
`$ch->set(CURLOPT_POSTQUOTE, $value)` | `$ch->setPostQuote($value)`
`$ch->set(CURLOPT_INTERFACE, $value)` | `$ch->setInterface($value)`
`$ch->set(CURLOPT_KRB4LEVEL, $value)` | `$ch->setKrb4level($value)`
`$ch->set(CURLOPT_HTTPPROXYTUNNEL, $value)` | `$ch->setHttpProxyTunnel($value)`
`$ch->set(CURLOPT_FILETIME, $value)` | `$ch->setFileTime($value)`
`$ch->set(CURLOPT_WRITEFUNCTION, $value)` | `$ch->setWriteFunction($value)`
`$ch->set(CURLOPT_READFUNCTION, $value)` | `$ch->setReadFunction($value)`
`$ch->set(CURLOPT_HEADERFUNCTION, $value)` | `$ch->setHeaderFunction($value)`
`$ch->set(CURLOPT_MAXREDIRS, $value)` | `$ch->setMaxRedirs($value)`
`$ch->set(CURLOPT_MAXCONNECTS, $value)` | `$ch->setMaxConnects($value)`
`$ch->set(CURLOPT_CLOSEPOLICY, $value)` | `$ch->setClosePolicy($value)`
`$ch->set(CURLOPT_FRESH_CONNECT, $value)` | `$ch->setFreshConnect($value)`
`$ch->set(CURLOPT_FORBID_REUSE, $value)` | `$ch->setForbidReuse($value)`
`$ch->set(CURLOPT_RANDOM_FILE, $value)` | `$ch->setRandomFile($value)`
`$ch->set(CURLOPT_EGDSOCKET, $value)` | `$ch->setEgdSocket($value)`
`$ch->set(CURLOPT_CONNECTTIMEOUT, $value)` | `$ch->setConnectTimeout($value)`
`$ch->set(CURLOPT_CONNECTTIMEOUT_MS, $value)` | `$ch->setConnectTimeoutMs($value)`
`$ch->set(CURLOPT_SSL_VERIFYPEER, $value)` | `$ch->setSslVerifypeer($value)`
`$ch->set(CURLOPT_CAINFO, $value)` | `$ch->setCainfo($value)`
`$ch->set(CURLOPT_CAPATH, $value)` | `$ch->setCapath($value)`
`$ch->set(CURLOPT_COOKIEJAR, $value)` | `$ch->setCookieJar($value)`
`$ch->set(CURLOPT_SSL_CIPHER_LIST, $value)` | `$ch->setSslCipherList($value)`
`$ch->set(CURLOPT_BINARYTRANSFER, $value)` | `$ch->setBinaryTransfer($value)`
`$ch->set(CURLOPT_NOSIGNAL, $value)` | `$ch->setNoSignal($value)`
`$ch->set(CURLOPT_PROXYTYPE, $value)` | `$ch->setProxyType($value)`
`$ch->set(CURLOPT_BUFFERSIZE, $value)` | `$ch->setBufferSize($value)`
`$ch->set(CURLOPT_HTTPGET, $value)` | `$ch->setHttpGet($value)`
`$ch->set(CURLOPT_HTTP_VERSION, $value)` | `$ch->setHttpVersion($value)`
`$ch->set(CURLOPT_SSLKEY, $value)` | `$ch->setSslKey($value)`
`$ch->set(CURLOPT_SSLKEYTYPE, $value)` | `$ch->setSslKeyType($value)`
`$ch->set(CURLOPT_SSLKEYPASSWD, $value)` | `$ch->setSslKeyPasswd($value)`
`$ch->set(CURLOPT_SSLENGINE, $value)` | `$ch->setSslEngine($value)`
`$ch->set(CURLOPT_SSLENGINE_DEFAULT, $value)` | `$ch->setSslEngineDefault($value)`
`$ch->set(CURLOPT_SSLCERTTYPE, $value)` | `$ch->setSslCertType($value)`
`$ch->set(CURLOPT_CRLF, $value)` | `$ch->setCrlf($value)`
`$ch->set(CURLOPT_ENCODING, $value)` | `$ch->setEncoding($value)`
`$ch->set(CURLOPT_PROXYPORT, $value)` | `$ch->setProxyPort($value)`
`$ch->set(CURLOPT_UNRESTRICTED_AUTH, $value)` | `$ch->setUnrestrictedAuth($value)`
`$ch->set(CURLOPT_FTP_USE_EPRT, $value)` | `$ch->setFtpUseEprt($value)`
`$ch->set(CURLOPT_TCP_NODELAY, $value)` | `$ch->setTcpNodelay($value)`
`$ch->set(CURLOPT_HTTP200ALIASES, $value)` | `$ch->setHttp200Aliases($value)`
`$ch->set(CURLOPT_MAX_RECV_SPEED_LARGE, $value)` | `$ch->setMaxRecvSpeedLarge($value)`
`$ch->set(CURLOPT_MAX_SEND_SPEED_LARGE, $value)` | `$ch->setMaxSendSpeedLarge($value)`
`$ch->set(CURLOPT_HTTPAUTH, $value)` | `$ch->setHttpAuth($value)`
`$ch->set(CURLOPT_PROXYAUTH, $value)` | `$ch->setProxyAuth($value)`
`$ch->set(CURLOPT_FTP_CREATE_MISSING_DIRS, $value)` | `$ch->setFtpCreateMissingDirs($value)`
`$ch->set(CURLOPT_PRIVATE, $value)` | `$ch->setPrivate($value)`
`$ch->set(CURLOPT_FTPSSLAUTH, $value)` | `$ch->setFtpSslAuth($value)`
`$ch->set(CURLOPT_FTP_SSL, $value)` | `$ch->setFtpSsl($value)`
`$ch->set(CURLOPT_CERTINFO, $value)` | `$ch->setCertInfo($value)`
`$ch->set(CURLOPT_POSTREDIR, $value)` | `$ch->setPostRedir($value)`
`$ch->set(CURLOPT_SSH_AUTH_TYPES, $value)` | `$ch->setSshAuthTypes($value)`
`$ch->set(CURLOPT_KEYPASSWD, $value)` | `$ch->setKeyPasswd($value)`
`$ch->set(CURLOPT_SSH_PUBLIC_KEYFILE, $value)` | `$ch->setSshPublicKeyFile($value)`
`$ch->set(CURLOPT_SSH_PRIVATE_KEYFILE, $value)` | `$ch->setSshPrivateKeyFile($value)`
`$ch->set(CURLOPT_SSH_HOST_PUBLIC_KEY_MD5, $value)` | `$ch->setSshHostPublicKeyMd5($value)`
`$ch->set(CURLOPT_REDIR_PROTOCOLS, $value)` | `$ch->setRedirProtocols($value)`
`$ch->set(CURLOPT_PROTOCOLS, $value)` | `$ch->setProtocols($value)`
`$ch->set(CURLOPT_FTP_FILEMETHOD, $value)` | `$ch->setFtpFileMethod($value)`
`$ch->set(CURLOPT_FTP_SKIP_PASV_IP, $value)` | `$ch->setFtpSkipPasvIp($value)`