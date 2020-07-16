<?php
    set_time_limit(0);
    define('APP', __DIR__  . '/app');
    define('SITE', 'https://lustron.ru/');

    require_once APP . '/classes/unlock.php';
    require_once APP . '/classes/curl.php';
    require_once APP . '/classes/dom.php';

    //$output = getWebPage('https://lustron.ru/sortament/lyustri/')['content'];
    $output = shell_exec("./vendor/ariya/phantomjs/bin/phantomjs --web-security=no ./execute.js");
    echo $output . "\n";

    /*$url_unlock = getUnlockUrl($output);
    if($url_unlock !== false) {
        echo 'unlock' . "\n";
        echo $url_unlock . "\n";
        $output = getWebPage($url_unlock)['content'];
    }*/

    $file = APP . '/cache/' . substr(md5(openssl_random_pseudo_bytes(20)),- 32) . ".html";
    file_put_contents($file, $output);
    $dom = file_get_html($file); //$dom = str_get_html($output);
    unlink($file);

    $results = [];
    //$storage = new SQLite3(APP . '/data.db');

    foreach($dom->find('div.product-block') as $item) {
        foreach($item->find('span.name_good_item') as $li) {
            /*$stm = $storage->prepare("INSERT INTO products(title) VALUES (?)");
            $stm->bindParam(1, $li->innertext);
            $stm->execute();*/

            array_push($results, $li->innertext);
        }
    }

    print("<pre>".print_r($results,true)."</pre>");
?>