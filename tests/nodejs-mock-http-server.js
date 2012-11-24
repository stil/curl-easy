var http = require('http');
var url = require('url');

console.log('Running mock server on localhost:55555');

http.createServer(function (req, res) {
    var result = {
        status : 'OK',
        time : +new Date(),
        headers : req.headers,
        url: url.parse(req.url)
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
    
    setTimeout(function () {res.end(JSON.stringify(result));}, 200);
}).listen(55555);