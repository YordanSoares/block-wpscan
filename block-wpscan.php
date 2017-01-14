<?php
/*
Plugin Name: block-wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, Proxy and Tor. * If you using cache plugin or cache system, this plugin doesn't run properly. *
Author: rluisr
Version: 0.7.6
Author URI: https://luispc.com/
*/

/*  Calling inet-ip.info for get ownserver's global ip
*/

/* Block direct access */
if (!defined('ABSPATH')) {
    die('Direct access not allowed!');
}

/* Set Timezone */
if (get_option('timezone') !== false) {
    date_default_timezone_set(get_option('timezone'));
}

/* Translate */
load_plugin_textdomain('block-wpscan', false, plugin_basename(dirname(__FILE__)) . '/assets/languages');

/**
 * If success captcha, Access is allowed permanently.
 */
if (isset($_POST['captcha_code']) === true) {
    require_once plugin_dir_path(__FILE__) . 'assets/securimage/securimage.php';

    $securimage = new Securimage();

    /* Success Captcha */
    if ($securimage->check($_POST['captcha_code']) === true) {
        global $wpdb;
        $ip = trim($_SERVER['REMOTE_ADDR']);
        $temp_ip = get_option('ip');

        if (empty($temp_ip) === true) {
            $add_exception_ip = $ip;

        } else {
            $temp_ip = get_option('ip');
            $add_exception_ip = "${temp_ip},${ip}";
        }

        /* UPDATE column `ip` */
        $wpdb->query("UPDATE wp_options SET option_value = '{$add_exception_ip}' WHERE option_name = 'ip'");

        toSetReport($ip, date("Y-m-d H:i"));
        header('Location: ' . home_url());
        exit();

    } else {
        wp_die(_e('<p>One more time</p> <input type="button" onClick=\'history.back();\' value="back">',
            'block-wpscan'), get_bloginfo('name') . " | " . "block-wpscan");
        exit;
    }
}
/***************************************************************************************************/

add_action('init', 'init_block_wpscan');
add_action('admin_menu', 'toGetOwnIP');
add_action('admin_notices', 'admin_first_setting');
add_action('admin_notices', 'admin_curl_error');
add_action('admin_menu', 'admin_block_wpscan');
add_action('admin_enqueue_scripts', 'register_frontend');
add_action('admin_init', 'check_cache');

/**
 * Check wp-content/block-wpscan/cache_status.
 * 0 : Run block-wpscan
 * 1 : Through block-wpscan / Do nothing
 */
function init_block_wpscan()
{
    $status = file_get_contents(WP_CONTENT_DIR . '/block-wpscan/cache_status');

    if ($status == 0) {
        block_wpscan();

    } elseif ($status == 1) {

    }
}

/**
 * Check cache setting.
 * If cache is enable, block-wpscan will be disabled. -> init
 *
 * Save cache_status file wp-content/block-wpscan/cache_status
 * 0 : cache is disable
 * 1 : cache is enable
 */
function check_cache()
{
    $path = preg_replace("/wp-content/", " ", WP_CONTENT_DIR);
    $path = trim($path);
    $wp_config = file_get_contents("${path}wp-config.php");

    if (file_exists(WP_CONTENT_DIR . '/block-wpscan') === false) {
        mkdir(WP_CONTENT_DIR . '/block-wpscan');
    }

    if (preg_match("/^define\('WP_CACHE',\s*true\);/mi", $wp_config) === 1) {
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/cache_status', "1", LOCK_EX);
        add_action('admin_notices', 'admin_cache_enable');

    } else {
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/cache_status', "0", LOCK_EX);
        remove_action('admin_notices', 'admin_cache_enable');
    }
}

/**
 * If cache is enable, Show message on admin page.
 */
function admin_cache_enable()
{
    echo "<div class=\"notice notice-error\">";
    echo "<h4>block-wpscan</h4>";
    echo _e("<p>This wordpress enables cache setting.<br>
            block-wpscan doesn't support under enabling cache.<br>
			block-wpscan is disabled automatically.
			If you disable cache plugin, block-wpscan is enable automatically.</p>
			</div><br>", 'block-wpscan');
}

/**
 * Call script and stylesheets.
 * Don't use basically jquery on wordpress.
 */
function register_frontend($hook_suffix)
{
    if ($hook_suffix == 'toplevel_page_block-wpscan') {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-2.2.1.min.js');
        wp_enqueue_script('bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',
            array(), null, false);
        wp_enqueue_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_script('bw.js', plugin_dir_url(__FILE__) . 'assets/js/style.js', array('jquery'), null, false);
        wp_enqueue_script('disable_enter.js', plugin_dir_url(__FILE__) . 'assets/js/disable_enter.js',
            array('jquery'), null, true);
    }
}

/**
 * Setting for Admin
 */
function admin_block_wpscan()
{
    add_menu_page('block-wpscan', 'block-wpscan', 'administrator', 'block-wpscan', 'menu_block_wpscan',
        plugin_dir_url(__FILE__) . 'assets/images/icon.png');
}

/**
 * Notices first setting
 */
function admin_first_setting()
{
    if (get_option('proxy') === false) {
        $html = "
            <div class=\"updated notice\">
			<h3>block-wpscan</h3>
			<p style='font-weight:700;color:red'>Thank you for installing block-wpscan. First you have to setup!!! in Sidebar.</p>
			</div>
			<br>
			";

        echo $html;
    }
}

/**
 * Notices curl module error
 */
function admin_curl_error()
{
    if (get_option('curl_module') === "0") {
        $html = "
				<div class=\"notice notice-error is-dismissable\">
				<h4>block-wpscan</h4>" . _e("<p>This server didn't load `curl` module. Please check your `php.ini` or install `php-common`.<br>
				When you installed `php-common` and others, disable the plugin one time and enable it again.<br>
				If you can't resolve this, you should add your server's global ip address on `Exception IP`.</p>
				</div>", 'block-wpscan');

        echo $html;
    }
}

/**
 * Admin
 */
function menu_block_wpscan()
{
    if (isset($_POST['msg']) || isset($_POST['proxy']) || isset($_POST['tor']) || isset($_POST['ip']) || isset($_POST['log']) || isset($_POST['timezone']) && check_admin_referer('check_admin_referer')) {
        update_option('timezone', filter_input(INPUT_POST, 'timezone', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('first', filter_input(INPUT_POST, 'first', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('msg', filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('redirect', filter_input(INPUT_POST, 'redirect', FILTER_VALIDATE_URL));
        update_option('proxy', filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('tor', filter_input(INPUT_POST, 'tor', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('ip', filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('ua', filter_input(INPUT_POST, 'ua', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        update_option('log', filter_input(INPUT_POST, 'log', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }

    @$msg = get_option('msg');
    @$redirect = get_option('redirect');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
    $ip = get_option('ip');
    $ua = get_option('ua');
    $log = get_option('log');

    /* Delete Block list */
    if (isset($_POST['delete'])) {
        unlink(WP_CONTENT_DIR . '/block-wpscan/block.list');
    }

    /* Delete Reported list */
    if (isset($_POST['delete_reported'])) {
        unlink(WP_CONTENT_DIR . '/block-wpscan/report.list');
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
                                <h3><?php echo _e("1. Set your Timezone.", 'block-wpscan'); ?></h3>
                                <select name="timezone" size="1">
                                    <?php
                                    $tz_list = file_get_contents(plugin_dir_path(__FILE__) . 'assets/timezone');
                                    @$timezone = get_option('timezone');

                                    if (strpos($tz_list, $timezone) !== false) {
                                        $tz_list = str_replace("{$timezone}\"", "{$timezone}\" selected", $tz_list);
                                    } else {
                                        echo $tz_list;
                                    }

                                    echo $tz_list; ?>
                                </select>

                                <br>
                                <br>

                                <h3><?php echo _e("2. What do you want to do, when the access is blocked.",
                                        'block-wpscan'); ?></h3>
                                <?php if (get_option('first') == 'msg') {
                                    echo "<label class=\"radio-inline\">";
                                    echo _e("<input type=\"radio\" name=\"first\" value=\"msg\" checked>Message",
                                        'block-wpscan');
                                    echo "</label >";
                                } else {
                                    echo "<label class=\"radio-inline\">";
                                    echo _e("<input type=\"radio\" name=\"first\" value=\"msg\">Message",
                                        'block-wpscan');
                                    echo "</label >";
                                }
                                if (get_option('first') == 'redirect') {
                                    echo "<label class=\"radio-inline\">";
                                    echo _e("<input type=\"radio\" name=\"first\" value=\"redirect\" checked>Redirect",
                                        'block-wpscan');
                                    echo "</label >";
                                } else {
                                    echo "<label class=\"radio-inline\">";
                                    echo _e("<input type=\"radio\" name=\"first\" value=\"redirect\">Redirect",
                                        'block-wpscan');
                                    echo "</label >";
                                } ?>

                                <br>
                                <br>

                                <?php if (get_option('first') == 'msg') {
                                    echo "<textarea class=\"input_x form-control\" name=\"msg\" placeholder=\"What message do you want to display?\">" . esc_html($msg) . "</textarea>";
                                } else {
                                    echo "<textarea class=\"input_x form-control\" style=\"display:none\" name=\"msg\" placeholder=\"What message do you want to display?\">" . esc_html($msg) . "</textarea>";
                                }
                                if (get_option('first') == 'redirect') {
                                    echo "<input class=\"input_x form-control\" type=\"text\" name=\"redirect\" placeholder=\"Example: https://luispc.com/\" value=\"$redirect\">";
                                } else {
                                    echo "<input class=\"input_x form-control\" style=\"display:none\" type=\"text\" name=\"redirect\" placeholder=\"Example: https://luispc.com/\" value=\"$redirect\">";
                                } ?>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>3. Block Proxy ON / OFF</h3>
                                <h5><?php echo _e("If you are using CDN service, you must not check ON.",
                                        'block-wpscan'); ?></h5>
                                <label class="radio-inline">
                                    <?php echo $proxy == "ON" ? "<input type=\"radio\" name=\"proxy\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"proxy\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $proxy == "OFF" ? "<input type=\"radio\" name=\"proxy\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"proxy\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>4. Block Tor ON / OFF</h3>
                                <h5><?php echo _e("If you check ON, It takes a bit of a while load time. Please test.",
                                        'block-wpscan'); ?></h5>
                                <label class="radio-inline">
                                    <?php echo $tor == "ON" ? "<input type=\"radio\" name=\"tor\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"tor\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $tor == "OFF" ? "<input type=\"radio\" name=\"tor\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"tor\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3><?php echo _e("5. Exception IP", 'block-wpscan'); ?></h3>
                                <h5><?php echo _e("If you have many exception IPs, Please split with ','",
                                        'block-wpscan'); ?><br>
                                    Example: 1.1.1.1,2.2.2.2,3.3.3.3
                                </h5>
                                <input class="form-control" type="text" name="ip"
                                       value="<?php echo $ip ?>">
                            </div>

                            <br>

                            <div class="form-group">
                                <h3><?php echo _e("6. Exception UserAgent", 'block-wpscan'); ?></h3>
                                <h5><?php echo _e("If you have many exception IPs,Please sprit with ','",
                                        'block-wpscan'); ?><br>
                                    Example: crawler,crawler_1
                                </h5>
                                <input class="form-control" type="text" name="ua" value="<?php echo $ua ?>">
                            </div>

                            <br>

                            <div class="form-group">
                                <h3><?php echo _e("7. Log function", 'block-wpscan'); ?></h3>
                                <label class="radio-inline">
                                    <?php echo $log == "ON" ? "<input type=\"radio\" name=\"log\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"log\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $log == "OFF" ? "<input type=\"radio\" name=\"log\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"log\" value=\"OFF\">OFF"; ?>
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

                        <form action="" method="post">
                            <div class="panel panel-danger">
                                <div class="panel-heading"><?php echo _e("List of through captcha access (Latest 5)",
                                        'block-wpscan'); ?>
                                    <input type="submit" class="btn btn-danger" name="delete_reported" value="Delete">
                        </form>
                    </div>
                    <div class="panel-body">
                        <table id="tabledata" class="table table-responsive">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>IP address</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $reported = toGetReportLog();

                            if ($reported !== false) {
                                foreach (toGetReportLog() as $row) {
                                    echo $row;
                                }
                            } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <?php echo _e("List of through captcha access is maybe spam.",
                            'block-wpscan'); ?><br>
                    </div>
                </div>

                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/icon-256x256.png' ?>"
                     class="img-rounded img-responsive">

                <h3>block-wpscan</h3>
                <p><?php echo _e("This plugin block Tor, Proxy, Command Line access and wpscan. But it can't block all
					unauthorized access.", 'block-wpscan'); ?><br>
                    <?php echo _e("Tor access is blocked by Tornodelist. If Tor access is isn't registration of Tornodelist, It
					can't block Tor access.", 'block-wpscan'); ?><br>
                    <?php echo _e("About 80percent can block.", 'block-wpscan'); ?><br>
                    <br>
                    * <?php echo _e("Exception IPs.", 'block-wpscan'); ?><br>
                    * <?php echo _e("Exception UserAgent.", 'block-wpscan'); ?><br>
                    * <?php echo _e("Proxy, Tor block ON / OFF.", 'block-wpscan'); ?><br>
                    * <?php echo _e("Edit message.", 'block-wpscan'); ?><br>
                    * <?php echo _e("Log function.", 'block-wpscan'); ?><br>
                    <br>
                    <?php echo _e("Googlebot and more can access own server.", 'block-wpscan'); ?><br>
                    <br>
                    <?php echo _e("If you have any problems or requests, Please contact me with github or twitter.",
                        'block-wpscan'); ?><br>
                    Twitter : https://twitter.com/lu_iskun<br>
                    Github : https://github.com/rluisr/block-wpscan"<br></p>
            </div>
        </div>
    </div>
    <!-- END Setting PAGE -->

    <!-- START Log PAGE -->
    <div class="tab-pane" id="tab2">
        <h3>Blocked list</h3>

        <span class="text-info"><strong>Blocked:</strong></span><?php echo @count(toGetLog()); ?>
        <span class="text-info">
				<strong>filesize:</strong>
			</span><?php echo @size_format(filesize(WP_CONTENT_DIR . '/block-wpscan/block.list'), 1) ?>
        <span class="text-info">
				<strong>Path:</strong>
			</span><?php echo WP_CONTENT_DIR . '/block-wpscan/block.list' ?>

        <p style="font-weight:bolder">
            <span style="color:#bd081c">※NBA</span> = Not Browser Access.
            <span style="color:#00AFF0">※CUA</span> =Corrupt User Agent.
            <span style="color:#3aaf85">※TA</span> = Tor Access.
            <span style="color:#410093">※PA</span> = Proxy Access.
        </p>

        <form action="" method="post">
            <input type="submit" class="btn btn-danger" name="delete" value="DeleteLog"/>
            <a class="btn btn-success" target="_blank"
               href="https://wordpress.org/support/view/plugin-reviews/block-wpscan">Review :)</a>
            <a class="btn btn-info" target="_blank" href="https://twitter.com/lu_iskun">Twitter</a>
            <a class="btn btn-warning" target="_blank" href="https://github.com/rluisr/block-wpscan">GitHub</a>
        </form>
        <hr>
        <table id="tabledata" class="table table-responsive">
            <thead>
            <tr>
                <th>#</th>
                <th>Judge</th>
                <th>IP address</th>
                <th>Hostname</th>
                <th>UserAgent(UA can camouflage. You shouldn't trust it.)</th>
                <th>Request URI</th>
                <th>Date</th>
                <th>Whois</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (toGetLog() !== false) {
                foreach (toGetLog() as $row) {
                    echo $row;
                }
            } else {
                echo "No data yet.";
            } ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>
<?php }

/**
 * curlモジュールが有効か確認
 * 入ってなければ curl_module に 0
 *
 * 自サーバーのIPを取得して例外IPに追加
 * パラメータは wp_options -> ownserverip に保存
 *
 * inet-ip.info がエラーのときは 0 を入れて再度実行
 * IPがきちんと入っているときは以降スルー
 *
 * @return string error curlがない
 * @return bool true 成功
 * @return bool false 失敗
 */
function toGetOwnIP()
{
    if (extension_loaded('curl') === false) {
        update_option('curl_module', "0");

    } elseif (extension_loaded('curl') === true && get_option('ownserverip') === false || get_option('ownserverip') === "0") {
        $url = "http://inet-ip.info/ip";
        $curl = curl_init($url);

        $options = array(
            CURLOPT_HTTPGET => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "curl/7.43.0"
        );

        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $header = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header = explode("\r\n", $header);
        /* Respnse code */
        $response_code = $header[0];
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        /* Server ip */
        $ownserverip = substr($result, $header_size);

        if ($response_code == 200) {
            update_option('ownserverip', trim($ownserverip));
            update_option('curl_extension', "1");

            return true;

        } else {
            update_option('ownserverip', "0");
            update_option('curl_module', "1");

            return false;
        }
    }
}


/**
 * ログを保存する。
 * 保存先は wp-content/block-wpscan/block.list
 *
 * @param $ip IPアドレス
 * @param $host ホストネーム
 * @param $ua ユーザーエージェント
 * @param $date 日付
 */
function toSetLog($judgement, $ip, $host, $ua, $request_url, $date, $whois)
{
    if (file_exists(WP_CONTENT_DIR . '/block-wpscan')) {
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/block.list',
            "${judgement}|${ip}|${host}|${ua}|${request_url}|${date}|${whois}\r\n", FILE_APPEND | LOCK_EX);
    } else {
        mkdir(WP_CONTENT_DIR . '/block-wpscan');
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/block.list',
            "${judgement}|${ip}|${host}|${ua}|${request_url}|${date}|${whois}\r\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * レポートされたIPを保存する。
 * 保存先は wp-content/block-wpscan/report.list
 *
 * @param $ip IPアドレス
 * @param $date 日付
 */
function toSetReport($ip, $date)
{
    if (file_exists(WP_CONTENT_DIR . '/block-wpscan')) {
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/report.list', "${ip}|${date}\r\n", FILE_APPEND | LOCK_EX);
    } else {
        mkdir(WP_CONTENT_DIR . '/block-wpscan');
        file_put_contents(WP_CONTENT_DIR . '/block-wpscan/report.list', "${ip}|${date}\r\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * リポートされたログファイルから連想配列か
 *
 * @return array リポートされたログファイルから多次元配列を返す
 */
function toCreateArrayReport()
{
    $b = 1;

    if (file_exists(WP_CONTENT_DIR . '/block-wpscan/report.list') === false) {
        return false;
    } else {
        $file = array_reverse(file(WP_CONTENT_DIR . '/block-wpscan/report.list'));
        foreach ($file as $row) {
            $a = explode("|", $row);

            $array[] = array(
                'count' => $b,
                'ip' => $a[0],
                'date' => $a[1]
            );
            $b++;
        }
    }


    return $array;
}

/**
 * リポートされたログを取得、HTML整形済み
 *
 * ログが膨大になってレイアウトが崩れないように最新の５件のみ表示
 * ログの数が5以下なら、foreach
 * 5以上はforで回してる。
 *
 * @return array HTMLで整形されたリポートログの情報
 */
function toGetReportLog()
{
    $a = toCreateArrayReport();

    if ($a === false) {
        return false;
    } else {
        if (is_array($a) === true) {
            if (count($a) < 5) {
                foreach (toCreateArrayReport() as $row) {
                    $array[] = "<tr>
                  <td>${row['count']}</td>
                  <td>${row['ip']}</td>
                  <td>${row['date']}</td>
                  </tr>";
                }

            } else {
                for ($i = 0; $i < 5; $i++) {
                    $array[] = "<tr>
                  <td>{$a[$i]['count']}</td>
                  <td>{$a[$i]['ip']}</td>
                  <td>{$a[$i]['date']}</td>
                  </tr>";
                }
            }

        } else {
            echo "No data yet.";
        }
    }

    return $array;
}

/**
 * ログファイルから連想配列化
 *
 * @return array ログファイルから多次元配列を返す
 */
function toCreateArray()
{
    $b = 1;
    $tocreatearray = file_exists(WP_CONTENT_DIR . '/block-wpscan/block.list');

    if ($tocreatearray === true) {
        $file = array_reverse(file(WP_CONTENT_DIR . '/block-wpscan/block.list'));
        foreach ($file as $row) {
            $a = explode("|", $row);

            $array[] = array(
                'count' => $b,
                'judgement' => $a[0],
                'ip' => $a[1],
                'host' => $a[2],
                'ua' => $a[3],
                'request_url' => $a[4],
                'date' => $a[5],
                'whois' => $a[6]
            );
            $b++;
        }
    } else {
        return false;
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
                  <td>${row['judgement']}</td>
                  <td>${row['ip']}</td>
                  <td>${row['host']}</td>
                  <td>${row['ua']}</td>
                  <td>${row['request_url']}</td>
                  <td>${row['date']}</td>
                  <td><a href=\"${row['whois']}\" target=\"_blank\">Whois</td>
                  </tr>";
        }

    } else {
        return false;
    }

    return $array;
}

/**
 * 使ってないよ
 * キャッシュ有効になってるときはどうしよっか
 */
function add_header()
{
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
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

    $ip = trim($_SERVER['REMOTE_ADDR']);
    @$ua = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
    @$host = trim(gethostbyaddr($_SERVER['REMOTE_ADDR']));
    @$exception_ip = get_option('ip');
    @$exception_ua = get_option('ua');
    $result = 1;

    /* IP + HOST - Tor */
    if (get_option('tor') == "ON") {
        $file = file_get_contents(plugin_dir_path(__FILE__) . 'tornodelist');

        if (strpos($file, $ip) !== false || strpos($host, 'tor') !== false) {
            $tor_result = 0;

        } else {
            $tor_result = 1;
        }
    }

    /* Exception IP */
    $exception_result = $ip === $_SERVER['SERVER_ADDR'] ? 1 : 0;
    if (isset($exception_ip) == true && $exception_result === 0) {

        $exception_ip_list = array(
            "192.0.64.0/18", // JetPack
            "114.111.64.0/18", // Yahoo
            "66.220.144.0/20", // Facebook
            "173.252.64.0/18", // Facebook
            "199.16.156.0/22", // Twitter
            "199.59.148.0/22", // Twitter
            "66.249.64.0/19" // Google
        );

        foreach ($exception_ip_list as $row) {
            $tmp_array = explode("/", $row);
            $accept_ip = $tmp_array[0];
            $mask = $tmp_array[1];

            $accept_long = ip2long($accept_ip) >> (32 - $mask);
            $remote_long = ip2long($ip) >> (32 - $mask);

            if ($accept_long == $remote_long) {
                $exception_result = 1;
                break;
            }
        }

        $ownserverip = get_option('ownserverip');
        if ($exception_result === 0) {
            if (preg_match("/,/", $exception_ip) != 1 && $ownserverip === false) {
                $exception_ip = array($exception_ip);
                $exception_ip[] = "127.0.0.1"; // for reverse proxy

            } elseif (preg_match("/,/", $exception_ip) != 1 && $ownserverip !== false) {
                $exception_ip = array($exception_ip, $ownserverip);
                $exception_ip[] = "127.0.0.1"; // for reverse proxy

            } elseif (preg_match("/,/", $exception_ip) == 1 && $ownserverip === false) {
                $exception_ip = explode(",", $exception_ip);
                $exception_ip[] = "127.0.0.1"; // for reverse proxy

            } elseif (preg_match("/,/", $exception_ip) == 1 && $ownserverip !== false) {
                $exception_ip = explode(",", $exception_ip);
                $exception_ip[] = "127.0.0.1"; // for reverse proxy
                $exception_ip[] = $ownserverip;
            }

            $exception_ip = array_merge(array_filter($exception_ip));
            foreach ($exception_ip as $row) {
                if (trim($row) == $ip) {
                    $exception_result = 1;
                    break;
                }
            }
        }
    }

    /* Browser's languages */
    if (filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_FULL_SPECIAL_CHARS) !== false) {
        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $languages = array_reverse($languages);

        foreach ($languages as $language) {
            if (preg_match('/^ja/i', $language) !== 0 || preg_match('/^en/i', $language) != 0) {
                $bl_result = 1;

            } else {
                $bl_result = 0;
            }
        }

    } else {
        $bl_result = 0;
    }

    /* HTTP_ACCEPT_LANGUAGE */
    if (filter_input(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_FULL_SPECIAL_CHARS) !== false) {
        $hap = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (strpos($hap, "gzip") !== false || strpos($hap, "deflate") !== false) {
            $hap_result = 1;

        } else {
            $hap_result = 0;
        }

    } else {
        $hap_result = 0;
    }

    /* Exception /feed & /rss access */
    $e = array("feed", "rss");

    foreach ($e as $row) {
        if (strpos(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                $row) !== false
        ) {
            $request_result = 1;
            break;

        } else {
            $request_result = 0;
        }
    }

    /* Exception HOST + BOT */
    $bot = array(
        "google",
        "msn",
        "yahoo",
        "bing",
        "hatena",
        "data-hotel",
        "twttr.com",
        "eset.com",
        "linkedin.com",
        "ahrefs.com",
        "webmeup.com",
        "grapeshot.co.uk",
        "blogmura.com",
        "apple.com",
        "microad.jp",
        "linode.com",
        "shadowserver.org",
        "ezweb.ne.jp"
    );

    foreach ($bot as $row) {
        if (strpos($host, $row) !== false) {
            $bot_result = 1;
            break;

        } else {
            $bot_result = 0;
        }
    }

    /* UserAgent */
    $array_ua = array(
        "Mozilla",
        "Opera",
        "Twitterbot/1.0"
    );
    if (isset($ua) === true) {
        foreach ($array_ua as $row) {
            if (strpos($ua, $row) !== false) {
                $ua_result = 1;
                break;

            } else {
                $ua_result = 0;
            }
        }
    } else {
        $ua_result = 0;
    }

    /* Exception UserAgent */
    if ($ua_result === 0 && $exception_result === 0 && isset($exception_ua) === true && isset($ua) === true) {
        if (preg_match("/,/", $exception_ua) != 1) {
            if (strpos($ua, $exception_ua) !== false) {
                $exception_result = 1;
            } else {
                $exception_result = 0;
            }

        } elseif (preg_match("/,/", $exception_ua) === 1) {
            $ua_array = explode(",", $exception_ua);

            foreach ($ua_array as $row) {
                if (strpos($ua, $row) !== false) {
                    $exception_result = 1;
                    break;

                } else {
                    $exception_result = 0;
                }
            }
        }
    }

    /* Header - Proxy */
    if (get_option('proxy') == "ON") {
        $proxy_result1 = isset($_SERVER['HTTP_VIA']) ? 0 : 1;
        $proxy_result2 = isset($_SERVER['HTTP_CLIENT_IP']) ? 0 : 1;
    }

    if ($bl_result === 0 || $hap_result === 0 || $ua_result === 0 || @$proxy_result1 === 0 || @$proxy_result2 === 0 || @$tor_result === 0) {
        $result = 0;
    }

    if (@$bot_result === 1 || @$exception_result === 1 || $request_result === 1) {
        $result = 1;
    }

    /*
            echo "IP: $ip<br>HOST: $host<br>
            --------------------<br>
            Exception: $exception_result<br>
            Result: $result<br>
            --------------------<br>
            Browser: $bl_result<br>
            AcceptEncoding : $hap_result<br>
            Bot: $bot_result<br>
            UA:$ua_result<br>
            Proxy1: $proxy_result1<br>
            Proxy2: $proxy_result2<br>
            Tor: $tor_result<br>
            <br><br>";
            print_r($_SERVER) . "\r\n";
            echo "<br><br>";
            print_r($exception_ip);
    */

    if ($result === 0) {
        if (get_option('log') == "ON") {
            if ($bl_result === 0) {
                $a = "<span style=\"color:#bd081c\">NBA</span>";
            } elseif ($ua_result === 0) {
                $a = "<span style=\"color:#00AFF0\">CUA</span>";
            } elseif ($proxy_result1 === 0 || $proxy_result2 === 0) {
                $a = "<span style=\"color:#410093\">PA</span>";
            } elseif ($tor_result === 0) {
                $a = "<span style=\"color:#3aaf85\">TA</span>";
            }

            toSetLog($a, $ip, $host, $ua, htmlspecialchars($_SERVER['REQUEST_URI']), date("Y-m-d H:i"),
                "http://whois.domaintools.com/${ip}");
        }

        /* ブロック時の処理 */
        if (get_option('first') === "msg") {
            $msg = get_option('msg');

            $secure_img_path = plugin_dir_url(__FILE__) . 'assets/securimage';
            $secure_img = "<img id=\"captcha\" src=\"{$secure_img_path}/securimage_show.php\" alt=\"CAPTCHA Image\" />";

            $html = <<< EOM
            {$msg}
            <form action="" method="post">
            {$secure_img}
            <input type="text" name="captcha_code" size="10" maxlength="6" />
            <a href="#" onclick="document.getElementById('captcha').src = '{$secure_img_path}/securimage_show.php?' + Math.random(); return false">
<img src="{$secure_img_path}/images/refresh.png" alt="Reload Image" height="32" width="32" onclick="this.blur()" align="bottom" border="0"/>
</a>
            <input type="submit" value="I'm a not BOT">
            </form>
            <br>
            <input type="button" onClick='history.back();' value="back">
EOM;
            wp_die("<h1>Your access is rejected.</h1><br>" . $html, get_bloginfo('name') . " | " . "block-wpscan");

            /* リダイレクト */
        } elseif (get_option('first') === "redirect") {
            header('Location: ' . get_option('redirect'));
            die();
        }
    }
}