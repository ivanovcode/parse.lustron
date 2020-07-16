var page = require('webpage').create();

page.settings.loadImages = false;
page.settings.resourceTimeout = 3000;
page.settings.userAgent = 'Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36';
page.viewportSize = { width: 1280, height: 800 };

page.onError = function(msg, trace) {
    console.log('error');
    return;
};

phantom.addCookie({
    'name': 'mycookie',
    'value': 'something really important',
    'domain': 'example.com'
})

page.open('https://lustron.ru/sortament/lyustri/', function () {
    setTimeout(function(){
        console.log(page.content);
        phantom.exit();
    }, 5000);
});