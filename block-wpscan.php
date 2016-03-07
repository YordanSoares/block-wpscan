<?php
/*
Plugin Name: block-wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, Proxy and Tor.
Author: rluisr
Version: 0.1.1
Author URI: https://luispc.com/
*/

/* Copyright 2016 rluisr (contact: @lu_iskun)

    Licence is GPLv2 (http://www.gnu.org/licenses/gpl-2.0.html)

    This plugin block Tor, Proxy, Command Line access and wpscan. But it can't block all unauthorized access.
    Tor is judged by API Server. If Tor's node isn't registration of API Server's node list, It can't block Tor access.
    About 80% can block.

    * Exception IPs.
    * Proxy, Tor block ON / OFF.
    * Edit message.

    You should add server's global ip for other plugins. ex)Broken Link Checker.
    Googlebot can access own server.

    If you have any problems or requests, Please contact me with github or twitter.
    Twitter : https://twitter.com/lu_iskun
    Github  : https://github.com/rluisr/block-wpscan
*/

/* Block direct access */
if (!defined('ABSPATH')) {
    die('Direct acces not allowed!');
}

add_action('admin_menu', 'admin_block_wpscan');
add_action('admin_enqueue_scripts', 'register_frontend');
add_action('init', 'block_wpscan');

/* Register CSS and JS */
function register_frontend()
{
    wp_register_script('bootstrap_js', plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js', array(), NULL, false);
    wp_register_style('bootstrap_css', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css', array(), NULL, false);
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap_js');
    wp_enqueue_style('bootstrap_css');
}

function admin_block_wpscan()
{
    add_menu_page(
        'block-wpscan', //サイトタイトル的なやつ
        'block-wpscan', //管理画面に表示されるやつ
        'administrator',
        'block-wpscan',
        'menu_block_wpscan',
        plugin_dir_url(__FILE__) . 'assets/images/icon.png'
    );
}

function menu_block_wpscan()
{
    if (isset($_POST['msg']) && check_admin_referer('check_referer')) {
        update_option('msg', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('proxy', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('tor', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'tor', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('ip', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('log', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
    }

    $msg = get_option('msg');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
    $ip = get_option('ip');
    $log = get_option('log');
    $wp_n = wp_nonce_field('check_referer');

    echo <<<HTML
    <h1>block-wpscan</h1>

    <hr>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab1" data-toggle="tab">Setting</a></li>
        <li><a href="#tab2" data-toggle="tab">Log</a></li>
    </ul>

<!-- START Setting PAGE -->
    <div class="container-fluid">
        <div class="tab-content">
            <div class="tab-pane active" id="tab1">
                <div class="row">
                    <div class="col-sm-7">
                        <form action="" method="post" >
                        ${wp_n}
                            <div class="form-group">
                                <h3>What message do you want to display, when the access is blocked.</h3>
                                    <textarea class="form-control" type="text" name="msg" placeholder="Example: Fuck You !">${msg}</textarea>
                                <p class="help-block">Can't use HTML. Coming soon.</p>
                            </div>


            <br>

            <div class="form-group">
                <h3>Block Proxy ON / OFF</h3>
                <label class="radio-inline">
HTML;
                echo $proxy == "ON" ? "<input type=\"radio\" name=\"proxy\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"proxy\" value=\"ON\">ON";
                echo "</label>";
                echo "<label class=\"radio-inline\">";
                echo $proxy == "OFF" ? "<input type=\"radio\" name=\"proxy\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"proxy\" value=\"OFF\">OFF";
    echo <<<HTML
                </label>
            </div>

            <br>

            <div class="form-group">
                <h3>Block Tor ON / OFF</h3>
                <h5>If you check ON, It takes a bit of a while load time. Please test.</h5>
                <label class="radio-inline">
HTML;
                echo $tor == "ON" ? "<input type=\"radio\" name=\"tor\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"tor\" value=\"ON\">ON";
                echo "</label>";
                echo "<label class=\"radio-inline\">";
                echo $tor == "OFF" ? "<input type=\"radio\" name=\"tor\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"tor\" value=\"OFF\">OFF";
    echo <<<HTML
                </label>
            </div>

            <br>

            <div class="form-group">
                <h3>Exception IP</h3>
                <h5>If you have many exception IPs,Please sprit with ","<br>
                You should add server's ip<global> for other plugins. ex)Broken Link Checker<br>
                Example: 1.1.1.1,2.2.2.2,3.3.3.3</h5>
                    <input class="form-control" type="text" name="ip" value="${ip}">
            </div>

            <br>

            <div class="form-group">
                <h3>Log fnction</h3>
                <h5>If you check on, It takes a bit of a while load time. Please test.</h5>
                <label class="radio-inline">
HTML;
                echo $log == "ON" ? "<input type=\"radio\" name=\"log\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"log\" value=\"ON\">ON";
                echo "</label>";
                echo "<label class=\"radio-inline\">";
                echo $log == "OFF" ? "<input type=\"radio\" name=\"log\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"log\" value=\"OFF\">OFF";

    echo <<<HTML
                </label>
            </div>

            <br>

                    <input class="btn btn-default" type="submit" value="Save all">
                </form>
            </div>
            <div class="col-sm-1">
                <div style="background-color:gray; width:2px; height:100%;"></div>
            </div>
            <div class="col-sm-4">
                <p>Here</p>
            </div>
        </div>
    </div>
<!-- END Setting PAGE -->

<!-- START Log PAGE -->
        <div class="tab-pane" id="tab2">
            <h1></h1>
        </div>
    </div>
HTML;
}

function block_wpscan()
{
    /**
     * 0 : reject
     * 1 : accept
     */
    $result = 1;

    /* IP - Tor */
    if (get_option('tor') == "ON") {
        $url = 'https://c.xzy.pw/judgementAPI-for-Tor/api.php';
        $ip = $_SERVER['REMOTE_ADDR'];

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
        $result = json_decode(file_get_contents($url, false, $context));
        $tor_result = $result === null ? 1 : $result->result;
    }
    /* Exception IP */
    $exception_result = $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'] ? 1 : 0;
    if (get_option('ip') || $exception_result === 0) {
        $exception_ip = explode(",", get_option('ip'));
        $exception_ip[] = "127.0.0.1"; // for reverse proxy

        foreach ($exception_ip as $row) {
            if ($row == $_SERVER['REMOTE_ADDR']) {
                $exception_result = 1;
                break;
            } else {
                $exception_result = 0;
            }
        }
    }
    /* Browser's languages */
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
    /* Googlebot */
    $bot_result = strpos(gethostbyaddr($_SERVER['REMOTE_ADDR']), "google.com") === false ? $bot_result = 0 : $bot_result = 1;
    /* UserAgent */
    if (filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS)) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], "Mozilla") === false) {
            $ua_result = 0;
        } else {
            $ua_result = 1;
        }
    } else {
        $ua_result = 0;
    }
    /* Header - Proxy */
    if (get_option('proxy') == "ON") {
        $proxy_result1 = isset($_SERVER['HTTP_VIA']) ? 0 : 1;
        $proxy_result2 = isset($_SERVER['HTTP_CLIENT_IP']) ? 0 : 1;
    }


    if ($browser_result === 0 || $ua_result === 0 || @$proxy_result1 === 0 || @$proxy_result2 === 0 || $tor_result === 0) {
        $result = 0;
    }
    if ($bot_result === 1 || $exception_result === 1) {
        $result = 1;
    }

    //echo "HOST: $ip\r\nException: $exception_result\r\nBrowser: $browser_result\r\nBot: $bot_result\r\nUA: $ua_result\r\nProxy1: $proxy_result1\r\nProxy2: $proxy_result2\r\nTor: $tor_result\r\nREMOTE_ADDR: $remote\r\nSERVER_ADDR: $server";

    if ($result === 0) {
        header("HTTP/1.0 406 Not Acceptable");
        die(esc_html(get_option('msg')));
    }
}

/*
 * 0.1.1はログと、UIの改善
 * 0.2.1はメッセージ表示機能
 * 0.3.1はHTMLの表示、リダイレクトの機能
 */