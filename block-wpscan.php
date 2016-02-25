<?php
/*
Plugin Name: block-Wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, proxy, tor and foreign ip.
Author: rluisr
Version: 1.0.0
Author URI: http://luispc.com/
*/

add_action('admin_menu', 'admin_block_wpscan');
add_action('init', 'block_wpscan');


function toGetTorIpList()
{
    $fgt = strip_tags(file_get_contents("https://www.dan.me.uk/tornodes"));
    $pattern = array("/\|(.*)/", "/[a-zA-Z]/");
    $ip = preg_replace($pattern, "", $fgt);
    if (file_put_contents(WP_PLUGIN_DIR . '/block-wpscan/toriplist', $ip) === false) {
        echo "Cannot create \"toriplist\". Please check your permission.";
    }

    $ip = file(WP_PLUGIN_DIR . '/block-wpscan/toriplist');
    foreach ($ip as $row) {
        if (preg_match("/^\\d/", $row)) {
            $list[] = $row;
        }
    }
    file_put_contents(WP_PLUGIN_DIR . '/block-wpscan/toriplist', $list);
}

add_action('block-wpscan_cron', 'toGetTorIpList');

if (!wp_next_scheduled('block-wpscan_cron')) {
    @wp_schedule_event(time(), get_option('cron'), 'toGetTorIpList');
}


function admin_block_wpscan()
{
    add_menu_page(
        'block-wpscan', //サイトタイトル的なやつ
        'block-wpscan', //管理画面に表示されるやつ
        'administrator',
        'block-wpscan',
        'menu_block_wpscan'
    );
}

function menu_block_wpscan()
{
    if (isset($_POST['msg']) && check_admin_referer('check_referer')) {
        update_option('msg', $_POST['msg']);
        update_option('proxy', $_POST['proxy']);
        update_option('tor', $_POST['tor']);
        update_option('cron', $_POST['cron']);
        wp_schedule_event(time(), get_option('cron'), 'toGetTorIpList');
    }

    $msg = get_option('msg');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
    $cron = get_option('cron');
    $wp_n = wp_nonce_field('check_referer');

    echo <<<EOF
<div>
    <h1>Setting | block-wpscan</h1>
    <p>----------------------------------------------------------------------------------------------</p>
    <form action="" method="post">
    ${wp_n}
    <h2>When block the access, What message do you want to display?</h2>
    <p>Example: Fuck U</p>
    <input type="text" name="msg" value="${msg}">
    <br>
    <br>
    <h2>Block Proxy ON / OFF</h2>
EOF;
    if ($proxy == "ON") {
        echo "<input type=\"radio\" name=\"proxy\" value=\"ON\" checked>ON";
    } else {
        echo "<input type=\"radio\" name=\"proxy\" value=\"ON\">ON";
    }
    if ($proxy == "OFF") {
        echo "<input type=\"radio\" name=\"proxy\" value=\"OFF\" checked>OFF";
    } else {
        echo "<input type=\"radio\" name=\"proxy\" value=\"OFF\">OFF";
    }
    echo <<<EOF
    <br>
    <br>
    <br>
    <h2>Block Tor ON / OFF</h2>
    <p>If you check ON, It takes a bit of a while load time. Please test.</p>
EOF;
    if ($tor == "ON") {
        echo "<input type=\"radio\" name=\"tor\" value=\"ON\" checked>ON";
    } else {
        echo "<input type=\"radio\" name=\"tor\" value=\"ON\">ON";
    }
    if ($tor == "OFF") {
        echo "<input type=\"radio\" name=\"tor\" value=\"OFF\" checked>OFF";
    } else {
        echo "<input type=\"radio\" name=\"tor\" value=\"OFF\">OFF";
    }
    echo <<<EOF
    <br>
    <h3>Update frenquency Tor IP List.</h3>
    <select name="cron">
EOF;
    if ($cron == "hourly") {
        echo "<option value=\"hourly\" selected>hourly</option>";
    } else {
        echo "<option value=\"hourly\">hourly</option>";
    }
    if ($cron == "twicedaily") {
        echo "<option value=\"twicedaily\" selected>twicedaily</option>";
    } else {
        echo "<option value=\"twicedaily\">twicedaily</option>";
    }
    if ($cron == "daily") {
        echo "<option value=\"daily\" selected>daily</option>";
    } else {
        echo "<option value=\"daily\">daily</option>";
    }
    if (!isset($cron)) {
        echo "<option value=\"hourly\">hourly</option>";
        echo "<option value=\"twicedaily\" selected>twicedaily</option>";
        echo "<option value=\"daily\">daily</option>";
    }
    echo <<<EOF
    </select >
    <br>
    <br>
    <br>
    <input type="submit" value="Save all">
    <br>
    <br>
    <p>This plugin is developing.<p>
    <p>Now this plugin discriminate User-agent, Request header, Browser language[※1], and others.</p>
    <p>Dont worry. This plugin allow googlebots. (Image, News, adsense and others)</p>
    <p>※1 : only Eng or Jp[a] see - https://www.w3.org/International/questions/qa-lang-priorities.en.php</p>
    <p>----------------------------------------------------------------------------------------------</p >
    <p>If you have any problems or requests, Please contact me <a href="https://twitter.com/lu_iskun">@lu_iskun</a> or <a href="https://github.com/rluisr/block-wpscan">github</a>.</p>
</div>
EOF;
}

function block_wpscan()
{
    /**
     * wpscan は HTTP_ACCEPT_LANGUAGE がないから拒否
     * Proxy と Tor は ON / OFF で拒否するか決めよう
     * 0 : reject
     * 1 : accept
     */
    $result = 1;

    /**
     * ブラウザの優先言語で判別。
     */
    $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $languages = array_reverse($languages);

    foreach ($languages as $language) {
        if (preg_match('/^ja/i', $language)) {
            $browser_result = 1;
        } elseif (preg_match('/^en/i', $language)) {
            $browser_result = 1;
        } else {
            $browser_result = 0;
        }
    }
    /**
     * Googlebot 判別
     */
    if (strpos($_SERVER['HTTP_USER_AGENT'], "Google") === false) {
        $bot_result = 0;
    } else {
        $bot_result = 1;
    }
    /**
     * ユーザーエージェントで判別
     */
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua, "Mozilla") === false) {
        $ua_result = 0;
    } else {
        $ua_result = 1;
    }
    /**
     * Header - Proxy
     */
    if (get_option('proxy') == "ON") {
        $proxy_result1 = isset($_SERVER['HTTP_VIA']) ? 0 : 1;
        $proxy_result2 = isset($_SERVER['HTTP_CLIENT_IP']) ? 0 : 1;
    }

    /**
     * IP - Tor
     */
    if (get_option('tor') == "ON") {
        $tor_ip = $_SERVER['REMOTE_ADDR'];
        $tor_hostname = gethostbyaddr($tor_ip);
        $tor_list = file_get_contents(WP_PLUGIN_DIR . '/block-wpscan/toriplist');

        //echo $tor_ip . "<br>";
        //echo $tor_hostname . "<br>";
        //echo gettype($tor_ip);

        if (strpos($tor_list, $tor_ip) !== false || strpos($tor_hostname, "tor") !== false || preg_match("/[a-zA-Z]/", $tor_hostname) === 0) {
            $tor_result = 0;
        } else {
            $tor_result = 1;
        }
    }

    if ($bot_result === 0 && $browser_result === 0) {
        $result = 0;
    } else {
        $result = 1;
    }

    if ($ua_result === 0 || @$proxy_result1 === 0 || @$proxy_result2 === 0 || @$tor_result === 0) {
        $result = 0;
    }

    //echo "${browser_result}\r\n${ua_result}\r\n${proxy_result1}\r\n${proxy_result2}\r\n${tor_result}\r\n";

    if ($result === 0) {
        header("HTTP/1.0 406 Not Acceptable");
        die(get_option('msg'));
    }
}