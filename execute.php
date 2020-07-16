<?php
set_time_limit(0);
define('APP', __DIR__  . '/app');
define('SITE', 'https://lustron.ru/');

foreach (glob(APP . '/classes/*.php') as $file) require_once($file);

$results = [];

$storage = new SQLite3(APP . '/data.db');
$cache = new Cache();
$casper = new Casper();

$casper->setOptions([
    'ignore-ssl-errors' => 'yes'
]);

$cache->setCachePath(APP . '/cache/');

$res = $storage->query("SELECT id, url, pages, title FROM categories WHERE parent = 0");

while ($row = $res->fetchArray(SQLITE3_NUM)) {
    for ($i = 0; $i <= $row[2]; $i++) {
        $url = SITE . $row[1] . '?p=' . $i;

        /*$casper->start($url);
        $casper->run();
        $output = $casper->getHTML();*/

        $response = getWebPage('phantom', $cache, $url, false, 'vpn'); $output = $response['content'];

        //echo $output . "\n";

        $file = APP . '/cache/' . substr(md5(openssl_random_pseudo_bytes(20)),- 32) . ".html";
        file_put_contents($file, $output);
        $dom = file_get_html($file);
        unlink($file);

        foreach($dom->find('div.product-block') as $item) {
            foreach($item->find('span.name_good_item') as $li) {
                if(!empty($li->outertext)) {
                    $stm = $storage->prepare("INSERT INTO products(title) VALUES (?)");
                    $stm->bindParam(1, $li->innertext);
                    $stm->execute();
                    array_push($results, $li->innertext);
                }

            }
        }

        print("<pre>".print_r($results,true)."</pre>");
        print("<pre>".print_r(array(
                "Категория" => $row[3],
                "Страница" => $i . ' из ' . $row[2],
                "URL" => $url,
                /*"Прокси" => $response['proxy'],*/
                "Всего наименований" => count($results)
            ),true)."</pre>");

        die();

    }
}
?>

