var page = require('webpage').create();

page.settings.loadImages = true;
page.settings.resourceTimeout = 3000;
page.settings.userAgent = 'Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36';
page.viewportSize = {width: 1280, height: 1024};

function onPageReady() {
    var htmlContent = page.evaluate(function () {
        return document.documentElement.outerHTML;
    });
    console.log(htmlContent);
    phantom.exit();
}

page.open('https://lustron.ru/sortament/lyustri/', function (status) {
    /*setTimeout(function() {
        console.log(page.content);
        //page.render('phantom.png');
        phantom.exit();
    }, 5000);*/


    function checkReadyState() {
        setTimeout(function () {
            var readyState = page.evaluate(function () {
                return document.readyState;
            });

            if ("complete" === readyState) {
                onPageReady();
            } else {
                checkReadyState();
            }
        });
    }

    checkReadyState();
});

