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

#Options CURLOPT_* mapping
CURLOPT_*  | Intelligent setter
------------- | -------------
CURLOPT_IPRESOLVE | `Ipresolve`
CURLOPT_DNS_USE_GLOBAL_CACHE | `DnsUseGlobalCache`
CURLOPT_DNS_CACHE_TIMEOUT | `DnsCacheTimeout`
CURLOPT_PORT | `Port`
CURLOPT_FILE | `File`
CURLOPT_READDATA | `Readdata`
CURLOPT_INFILE | `Infile`
CURLOPT_INFILESIZE | `Infilesize`
CURLOPT_URL | `Url`
CURLOPT_PROXY | `Proxy`
CURLOPT_VERBOSE | `Verbose`
CURLOPT_HEADER | `Header`
CURLOPT_HTTPHEADER | `Httpheader`
CURLOPT_NOPROGRESS | `Noprogress`
CURLOPT_PROGRESSFUNCTION | `Progressfunction`
CURLOPT_NOBODY | `Nobody`
CURLOPT_FAILONERROR | `Failonerror`
CURLOPT_UPLOAD | `Upload`
CURLOPT_POST | `Post`
CURLOPT_FTPLISTONLY | `Ftplistonly`
CURLOPT_FTPAPPEND | `Ftpappend`
CURLOPT_NETRC | `Netrc`
CURLOPT_FOLLOWLOCATION | `Followlocation`
CURLOPT_PUT | `Put`
CURLOPT_USERPWD | `Userpwd`
CURLOPT_PROXYUSERPWD | `Proxyuserpwd`
CURLOPT_RANGE | `Range`
CURLOPT_TIMEOUT | `Timeout`
CURLOPT_TIMEOUT_MS | `TimeoutMs`
CURLOPT_POSTFIELDS | `Postfields`
CURLOPT_REFERER | `Referer`
CURLOPT_USERAGENT | `Useragent`
CURLOPT_FTPPORT | `Ftpport`
CURLOPT_FTP_USE_EPSV | `FtpUseEpsv`
CURLOPT_LOW_SPEED_LIMIT | `LowSpeedLimit`
CURLOPT_LOW_SPEED_TIME | `LowSpeedTime`
CURLOPT_RESUME_FROM | `ResumeFrom`
CURLOPT_COOKIE | `Cookie`
CURLOPT_COOKIESESSION | `Cookiesession`
CURLOPT_AUTOREFERER | `Autoreferer`
CURLOPT_SSLCERT | `Sslcert`
CURLOPT_SSLCERTPASSWD | `Sslcertpasswd`
CURLOPT_WRITEHEADER | `Writeheader`
CURLOPT_SSL_VERIFYHOST | `SslVerifyhost`
CURLOPT_COOKIEFILE | `Cookiefile`
CURLOPT_SSLVERSION | `Sslversion`
CURLOPT_TIMECONDITION | `Timecondition`
CURLOPT_TIMEVALUE | `Timevalue`
CURLOPT_CUSTOMREQUEST | `Customrequest`
CURLOPT_STDERR | `Stderr`
CURLOPT_TRANSFERTEXT | `Transfertext`
CURLOPT_RETURNTRANSFER | `Returntransfer`
CURLOPT_QUOTE | `Quote`
CURLOPT_POSTQUOTE | `Postquote`
CURLOPT_INTERFACE | `Interface`
CURLOPT_KRB4LEVEL | `Krb4level`
CURLOPT_HTTPPROXYTUNNEL | `Httpproxytunnel`
CURLOPT_FILETIME | `Filetime`
CURLOPT_WRITEFUNCTION | `Writefunction`
CURLOPT_READFUNCTION | `Readfunction`
CURLOPT_HEADERFUNCTION | `Headerfunction`
CURLOPT_MAXREDIRS | `Maxredirs`
CURLOPT_MAXCONNECTS | `Maxconnects`
CURLOPT_CLOSEPOLICY | `Closepolicy`
CURLOPT_FRESH_CONNECT | `FreshConnect`
CURLOPT_FORBID_REUSE | `ForbidReuse`
CURLOPT_RANDOM_FILE | `RandomFile`
CURLOPT_EGDSOCKET | `Egdsocket`
CURLOPT_CONNECTTIMEOUT | `Connecttimeout`
CURLOPT_CONNECTTIMEOUT_MS | `ConnecttimeoutMs`
CURLOPT_SSL_VERIFYPEER | `SslVerifypeer`
CURLOPT_CAINFO | `Cainfo`
CURLOPT_CAPATH | `Capath`
CURLOPT_COOKIEJAR | `Cookiejar`
CURLOPT_SSL_CIPHER_LIST | `SslCipherList`
CURLOPT_BINARYTRANSFER | `Binarytransfer`
CURLOPT_NOSIGNAL | `Nosignal`
CURLOPT_PROXYTYPE | `Proxytype`
CURLOPT_BUFFERSIZE | `Buffersize`
CURLOPT_HTTPGET | `Httpget`
CURLOPT_HTTP_VERSION | `HttpVersion`
CURLOPT_SSLKEY | `Sslkey`
CURLOPT_SSLKEYTYPE | `Sslkeytype`
CURLOPT_SSLKEYPASSWD | `Sslkeypasswd`
CURLOPT_SSLENGINE | `Sslengine`
CURLOPT_SSLENGINE_DEFAULT | `SslengineDefault`
CURLOPT_SSLCERTTYPE | `Sslcerttype`
CURLOPT_CRLF | `Crlf`
CURLOPT_ENCODING | `Encoding`
CURLOPT_PROXYPORT | `Proxyport`
CURLOPT_UNRESTRICTED_AUTH | `UnrestrictedAuth`
CURLOPT_FTP_USE_EPRT | `FtpUseEprt`
CURLOPT_TCP_NODELAY | `TcpNodelay`
CURLOPT_HTTP200ALIASES | `Http200aliases`
CURLOPT_MAX_RECV_SPEED_LARGE | `MaxRecvSpeedLarge`
CURLOPT_MAX_SEND_SPEED_LARGE | `MaxSendSpeedLarge`
CURLOPT_HTTPAUTH | `Httpauth`
CURLOPT_PROXYAUTH | `Proxyauth`
CURLOPT_FTP_CREATE_MISSING_DIRS | `FtpCreateMissingDirs`
CURLOPT_PRIVATE | `Private`
CURLOPT_FTPSSLAUTH | `Ftpsslauth`
CURLOPT_FTP_SSL | `FtpSsl`
CURLOPT_CERTINFO | `Certinfo`
CURLOPT_POSTREDIR | `Postredir`
CURLOPT_SSH_AUTH_TYPES | `SshAuthTypes`
CURLOPT_KEYPASSWD | `Keypasswd`
CURLOPT_SSH_PUBLIC_KEYFILE | `SshPublicKeyfile`
CURLOPT_SSH_PRIVATE_KEYFILE | `SshPrivateKeyfile`
CURLOPT_SSH_HOST_PUBLIC_KEY_MD5 | `SshHostPublicKeyMd5`
CURLOPT_REDIR_PROTOCOLS | `RedirProtocols`
CURLOPT_PROTOCOLS | `Protocols`
CURLOPT_FTP_FILEMETHOD | `FtpFilemethod`
CURLOPT_FTP_SKIP_PASV_IP | `FtpSkipPasvIp`