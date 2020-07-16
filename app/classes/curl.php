<?php
function getProxyNordVpn( $cache )
{
    $cache->setCache('proxy_array');
    $cache_key = 'nordvpn';

    if (!$cache->isCached($cache_key)) {
        $proxy_string = shell_exec("curl --silent \"https://api.nordvpn.com/v1/servers/recommendations?filters\[servers_groups\]\[identifier\]=legacy_standard&filters\[servers_groups\]\[identifier\]=legacy_obfuscated_servers\" | jq --raw-output --slurp ' .[] | sort_by(.load) | limit(5;.[]) | [.hostname, .load] | \"\(.[0])\"'");
        $proxy_array = array_filter(explode("\n", $proxy_string));
        $cache->store($cache_key, $proxy_array, 60*60);
    } else {
        $proxy_array = $cache->retrieve($cache_key);
    }

    return $proxy_array;
}

function getWebPage($type = 'curl', $cache, $url, $proxy = false, $service = 'tor')
{
    $useragent_array = [
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0',
        'Mozilla/5.0 (X11; U; Linux Core i7-4980HQ; de; rv:32.0; compatible; JobboerseBot; https://www.jobboerse.com/bot.htm) Gecko/20100101 Firefox/38.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.10) Gecko/20050716 Firefox/1.0.6',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0',
        'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:57.0) Gecko/20100101 Firefox/57.0',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0 ',
        'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0',
        'Mozilla/5.0 (Windows NT 5.1; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
        'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:63.0) Gecko/20100101 Firefox/63.0',
        'Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0',
        'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)'];

    $referer_array = [
        'https://yandex.ru/',
        'https://www.google.com/',
        'https://www.google.com/',
        'https://www.rambler.ru/',
        'https://www.google.com/',
        'https://www.bing.com/'
    ];

    $proxy_array = getProxyNordVpn($cache);
    $proxy_auth = 'profidela.com@gmail.com:huj2ov4f';
    $proxy_server = $proxy_array[array_rand($proxy_array, 1)].':80';
    $proxy_server = 'de693.nordvpn.com:80';
    $proxy_cmd = ($proxy && $service == 'vpn' ? "--proxy=".$proxy_server." --proxy-auth=" . $proxy_auth .' ' : '');

    if($type == 'phantom') {
        $output = shell_exec("./vendor/ariya/phantomjs/bin/phantomjs --web-security=no ".$proxy_cmd."./execute.js");
        $header['content'] = $output;
        return $header;
    }

    $options = array(
        CURLOPT_CUSTOMREQUEST  => "GET",
        CURLOPT_POST           => false,
        CURLOPT_USERAGENT      => $useragent_array[array_rand($useragent_array, 1)],
        CURLOPT_REFERER        => $referer_array[array_rand($referer_array, 1)],
        CURLOPT_COOKIEFILE     =>"cookie.txt",
        CURLOPT_COOKIEJAR      =>"cookie.txt",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => "UTF-8",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_FOLLOWLOCATION => true
    );

    if($proxy) {
        $proxy = $service;
        switch ($service) {
            default:
            case 'tor':
                $options = $options + array(
                        CURLOPT_HEADER => 1,
                        CURLOPT_HTTPPROXYTUNNEL => 1,
                        CURLOPT_PROXY => 'socks5h://127.0.0.1:9050'
                    );
                break;
            case 'vpn':
                $options = $options + array(
                        CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
                        CURLOPT_PROXY => $proxy_server,
                        CURLOPT_PROXYUSERPWD => $proxy_auth
                    );
                break;
        }
    }

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    $header['proxy'] = $proxy;
    return $header;
}
?>

