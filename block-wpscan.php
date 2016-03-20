<?php
/*
Plugin Name: block-wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, Proxy and Tor.
Author: rluisr
Version: 0.3.3
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
    * Log function.

    You should add server's global ip for other plugins. ex)Broken Link Checker.
    Googlebot can access own server.

    If you have any problems or requests, Please contact me with github or twitter.
    Twitter : https://twitter.com/lu_iskun
    Github  : https://github.com/rluisr/block-wpscan
*/

/* Block direct access */
if (!defined('ABSPATH')) {
    die('Direct access not allowed!');
}

add_action('admin_menu', 'admin_block_wpscan');
add_action('admin_enqueue_scripts', 'register_frontend');
add_action('init', 'block_wpscan');

/**
 * スクリプト、スタイルシートの読み込み
 * Wordpress標準の jquery を使わずにCDNから読み込む
 */
function register_frontend($hook_suffix)
{
    if ($hook_suffix == 'toplevel_page_block-wpscan') {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-2.2.1.min.js');
        wp_enqueue_script('bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array(), NULL, false);
        wp_enqueue_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_script('bw.js', plugin_dir_url(__FILE__) . 'assets/js/style.js', array('jquery'), NULL, false);
        wp_enqueue_script('quick.js', plugin_dir_url(__FILE__) . 'assets/js/jquery.searcher.js', array('jquery'), NULL, true);
        wp_enqueue_script('search.js', plugin_dir_url(__FILE__) . 'assets/js/search.js', array('quick.js'), NULL, true);
    }
}

/**
 * 管理関連の設定
 */
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

/**
 * 管理画面
 */
function menu_block_wpscan()
{
    if (isset($_POST['msg']) || isset($_POST['proxy']) || isset($_POST['tor']) || isset($_POST['ip']) || isset($_POST['log']) && check_admin_referer('check_admin_referer')) {
        update_option('first', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'first', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        @update_option('msg', $_POST['msg']);
        @update_option('redirect', esc_html(filter_input(INPUT_POST, 'redirect', FILTER_VALIDATE_URL)));
        update_option('proxy', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('tor', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'tor', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('ip', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('log', esc_html(htmlspecialchars(filter_input(INPUT_POST, 'log', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
    }

    $msg = get_option('msg');
    $redirect = get_option('redirect');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
    $ip = get_option('ip');
    $log = get_option('log');

    /* Delete Block list */
    if (isset($_POST['delete'])) {
        unlink(plugin_dir_path(__FILE__) . 'block.list');
    }

    /* Search log */
    if (isset($_POST['search'])) {
        foreach (toCreateArray() as $row) {
            if ($row['ip'] == filter_input(INPUT_POST, 'search', FILTER_SANITIZE_SPECIAL_CHARS) || $row['host'] == filter_input(INPUT_POST, 'search', FILTER_SANITIZE_SPECIAL_CHARS) || $row['ua'] == filter_input(INPUT_POST, 'search', FILTER_SANITIZE_SPECIAL_CHARS)) {
                echo "見つけた";
            } else {
                echo "見つからない";
            }
        }
    } ?>

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
                        <form action="" method="post">
                            <div class="form-group">
                                <h3>What do you want to do, when the access is blocked.</h3>
                                <?php if (get_option('first') == 'msg') {
                                    echo "<label class=\"radio-inline\">";
                                    echo "<input type=\"radio\" name=\"first\" value=\"msg\" checked>Message";
                                    echo "</label >";
                                } else {
                                    echo "<label class=\"radio-inline\">";
                                    echo "<input type=\"radio\" name=\"first\" value=\"msg\">Message";
                                    echo "</label >";
                                }
                                if (get_option('first') == 'redirect') {
                                    echo "<label class=\"radio-inline\">";
                                    echo "<input type=\"radio\" name=\"first\" value=\"redirect\" checked>Redirect";
                                    echo "</label >";
                                } else {
                                    echo "<label class=\"radio-inline\">";
                                    echo "<input type=\"radio\" name=\"first\" value=\"redirect\">Redirect";
                                    echo "</label >";
                                } ?>

                                <br>
                                <br>

                                <?php if (get_option('first') == 'msg') {
                                    echo "<textarea class=\"input_x form-control\" name=\"msg\" placeholder=\"What message do you want to display? It can use HTML.\">" . esc_html($msg) . "</textarea>";
                                } else {
                                    echo "<textarea class=\"input_x form-control\" style=\"display:none\" name=\"msg\" placeholder=\"What message do you want to display?\">" . esc_html($msg) . "</textarea>";
                                }
                                if (get_option('first') == 'redirect') {
                                    echo "<input class=\"input_x form-control\" type=\"text\" name=\"redirect\" placeholder=\"Example: https://luispc.com/\" value=\"$redirect \">";
                                } else {
                                    echo "<input class=\"input_x form-control\" style=\"display:none\" type=\"text\" name=\"redirect\" placeholder=\"Example: https://luispc.com/\" value=\"$redirect \">";
                                } ?>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3> Block Proxy ON / OFF </h3>
                                <label class="radio-inline">
                                    <?php echo $proxy == "ON" ? "<input type=\"radio\" name=\"proxy\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"proxy\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $proxy == "OFF" ? "<input type=\"radio\" name=\"proxy\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"proxy\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>Block Tor ON / OFF</h3>
                                <h5>If you check ON, It takes a bit of a while load time. Please test.</h5>
                                <label class="radio-inline">
                                    <?php echo $tor == "ON" ? "<input type=\"radio\" name=\"tor\" value=\"ON\" checked>ON" :
                                        "<input type=\"radio\" name=\"tor\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $tor == "OFF" ? "<input type=\"radio\" name=\"tor\" value=\"OFF\" checked>OFF" :
                                        "<input type=\"radio\" name=\"tor\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>Exception IP</h3>
                                <h5>If you have many exception IPs,Please sprit with ","<br>
                                    You should add server's ip
                                    <global> for other plugins. ex)Broken Link Checker<br>
                                        Example: 1.1.1.1,2.2.2.2,3.3.3.3
                                </h5>
                                <input class="form-control" type="text" name="ip" value="<?php echo $ip ?>">
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>Log fnction</h3>
                                <h5>If you check on, It takes a bit of a while load time. Please test.</h5>
                                <label class="radio-inline">
                                    <?php echo $log == "ON" ? "<input type=\"radio\" name=\"log\" value=\"ON\" checked>ON" :
                                        "<input type=\"radio\" name=\"log\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $log == "OFF" ? "<input type=\"radio\" name=\"log\" value=\"OFF\" checked>OFF" :
                                        "<input type=\"radio\" name=\"log\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <input class="btn btn-default" type="submit" value="Save all">
                            <?php wp_nonce_field('check_admin_referer'); ?>
                        </form>
                    </div>

                    <div class="col-sm-1"></div>

                    <div class="col-sm-4">
                        <br>
                        <div class="panel panel-primary">
                            <div class="panel-heading">Information</div>
                            <div class="panel-body"><?php echo htmlspecialchars(toGetInfo()->msg); ?></div>
                            <div class="panel-footer"><?php echo htmlspecialchars(toGetInfo()->date); ?></div>
                        </div>

                        <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/icon-256x256.png' ?>"
                             class="img-rounded img-responsive">
                        <h3>block-wpscan</h3>
                        <p>This plugin block Tor, Proxy, Command Line access and wpscan.<br>
                            But it can't block all unauthorized access.<br>
                            Tor is judged by API Server. If Tor's node isn't registration of API Server's node list, It
                            can't block Tor access.<br>
                            About 80% can block.<br>
                            <br>
                            * Exception IPs.<br>
                            * Proxy, Tor block ON / OFF.<br>
                            * Edit message.<br>
                            * Log function.<br>
                            <br>
                            You should add own server's global ip for other plugins. ex)Broken Link Checker.<br>
                            Googlebot and other crawler can access own server.<br>
                            <br>
                            If you have any problems or requests, Please contact me with github or twitter.<br>
                            Twitter : https://twitter.com/lu_iskun<br>
                            Github : https://github.com/rluisr/block-wpscan<br></p>
                    </div>

                </div>
            </div>
            <!-- END Setting PAGE -->

            <!-- START Log PAGE -->
            <div class="tab-pane" id="tab2">
                <input id="tablesearchinput">
                <form action="" method="post">
                    <h3>Blocked list <span class="small"></h3>
                    <span class="text-info">Blocked:</span><?php echo count(toGetLog()); ?>
                    <span
                        class="text-info">filesize:</span><?php echo filesize(plugin_dir_path(__FILE__) . 'block.list') / 1024 / 1024 ?>
                    Mbytes <span
                        class="text-info">Path:</span><?php echo plugin_dir_path(__FILE__) . 'block.list' ?></span>
                    <!-- DELETE FUNCTION
                    <span><input type="submit" class="btn btn-danger" name="delete" value="Delete"></span>
                    -->
                </form>
                <table id="tabledata" class="table table-responsive">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>IP address</th>
                        <th>Hostname</th>
                        <th>UserAgent(UA can camouflage. You shouldn't trust it.)</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (toGetLog() as $row) {
                        echo $row;
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php }

/**
 * APIサーバーからメッセージを受信する。
 *
 * @return mixed APIサーバーから受信したメッセージ
 */
function toGetInfo()
{
    $url = 'https://c.xzy.pw/judgementAPI-for-Tor/message.php';
    $options = array(
        'http' => array(
            'method' => 'POST'
        ),
    );
    $context = stream_context_create($options);
    $result = json_decode(file_get_contents($url, false, $context));
    return $result;
}

/**
 * ログを保存する。
 * 保存先は wp-content
 *
 * @param $ip IPアドレス
 * @param $host ホストネーム
 * @param $ua ユーザーエージェント
 * @param $date 日付
 */
function toSetLog($ip, $host, $ua, $date)
{
    file_put_contents(plugin_dir_path(__FILE__) . 'block.list', "${ip}|${host}|${ua}|${date}\r\n", FILE_APPEND | LOCK_EX);
}

/**
 * ログファイルから連想配列化
 *
 * @return array ログファイルから多次元配列を返す
 */
function toCreateArray()
{
    $b = 1;

    if ($file = array_reverse(file(plugin_dir_path(__FILE__) . 'block.list'))) {
        foreach ($file as $row) {
            $a = explode("|", $row);
            $array[] = array('count' => $b, 'ip' => $a[0], 'host' => $a[1], 'ua' => $a[2], 'date' => $a[3]);
            $b++;
        }
    }
    return $array;
}

/**
 * ログ情報の取得　既にHTML整形済み
 *
 * @return array HTMLで整形されたログの情報
 */
function toGetLog()
{
    if (is_array(toCreateArray()) === true) {
        foreach (toCreateArray() as $row) {
            $array[] = "<tr>
                  <td>${row['count']}</td>
                  <td>${row['ip']}</td>
                  <td>${row['host']}</td>
                  <td>${row['ua']}</td>
                  <td>${row['date']}</td>
                  </tr>";
        }
    } else {
        echo "No data yet. or Log function setting is off";
    }
    return $array;
}

/**
 * コア的な部分
 */
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

    /* Googlebot, msnbot */
    $bot = array("google", "msn", "yahoo", "bing", "hatena");
    $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    foreach ($bot as $row) {
        if (strpos($host, $row) !== false) {
            $bot_result = 1;
            break;
        } else {
            $bot_result = 0;
        }
    }

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

    //echo "Result: $result\r\nIP: $ip\r\nHOST: $host\r\nException: $exception_result\r\nBrowser: $browser_result\r\nBot: $bot_result\r\nUA:$ua_result\r\nProxy1: $proxy_result1\r\nProxy2: $proxy_result2\r\nTor: $tor_result\r\nREMOTE_ADDR:{$_SERVER['REMOTE_ADDR']}\r\nSERVER_ADDR: {$_SERVER['SERVER_ADDR']}";

    if ($result === 0) {
        if (get_option('log') == "ON") {
            toSetLog($_SERVER['REMOTE_ADDR'], gethostbyaddr($_SERVER['REMOTE_ADDR']), filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS), date("Y-m-d H:i"));
        }
        if (get_option('first') == "msg") {
            header("HTTP / 1.0 406 Not Acceptable");
            die(get_option('msg'));
        } elseif (get_option('first') == "redirect") {
            header('Location: ' . get_option('redirect'));
        }
    }
}

/*
* 0.3.1はHTMLの表示、リダイレクトの機能
*/