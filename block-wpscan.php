<?php
/*
Plugin Name: block-Wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, proxy, tor and foreign ip.
Author: rluisr
Version: 0.0.1
Author URI: https://luispc.com/
*/

add_action('admin_menu', 'admin_block_wpscan');
add_action('init', 'block_wpscan');
add_action('block-wpscan_cron', 'toGetTorIpList');

function admin_block_wpscan()
{
    add_menu_page(
        'block-wpscan', //サイトタイトル的なやつ
        'block-wpscan', //管理画面に表示されるやつ
        'administrator',
        'block-wpscan',
        'menu_block_wpscan',
        plugins_url('assets/images/icon.png', __FILE__)
    );
}

function menu_block_wpscan()
{
    if (isset($_POST['msg']) && $_POST['proxy'] && $_POST['tor'] && check_admin_referer('check_referer')) {
        update_option('msg', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('proxy', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('tor', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'tor', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
    }

    $msg = get_option('msg');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
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
    if (filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_SPECIAL_CHARS)) {
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

    } else {
        $browser_result = 0;
    }
    /**
     * Googlebot 判別
     */
    if (filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS)) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], "Google") === false) {
            $bot_result = 0;
        } else {
            $bot_result = 1;
        }
    } else {
        $bot_result = 1;
    }
    /**
     * ユーザーエージェントで判別
     */
    if (filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS)) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], "Mozilla") === false) {
            $ua_result = 0;
        } else {
            $ua_result = 1;
        }
    } else {
        $ua_result = 0;
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
        $url = 'https://c.xzy.pw/judgementAPI-for-Tor/api.php';

        if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $data = array(
            "ip" => $ip
        );

        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result = json_decode($result);
        $tor_result = $result->result;
    }

    if ($bot_result === 0 && $browser_result === 0) {
        $result = 0;
    } else {
        $result = 1;
    }

    if ($ua_result === 0 || @$proxy_result1 === 0 || @$proxy_result2 === 0 || @$tor_result === 0) {
        $result = 0;
    }

    if ($result === 0) {
        header("HTTP/1.0 406 Not Acceptable");
        die(esc_html(get_option('msg')));
    }
}