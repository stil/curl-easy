var http = require('http');
var url = require('url');

console.log('Running mock server on localhost:55555');

function randomString(len, charSet) {
    charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
    	var randomPoz = Math.floor(Math.random() * charSet.length);
    	randomString += charSet.substring(randomPoz,randomPoz+1);
    }
    return randomString;
}

http.createServer(function (req, res) {
    var result = {
        status : 'OK',
        time : +new Date(),
        headers : req.headers,
        url: url.parse(req.url),
        noise : randomString(10*1024)
    };
    
    console.log(req.url);
    switch(req.url) {
        case '/':
            result.data = 'blabla'
        break;
        case '/timeout':
            return;
        break;
    };
    
    res.writeHead(200, {'Content-Type': 'application/json'});
    
    setTimeout(function () {res.end(JSON.stringify(result));}, 50);
}).listen(55555);