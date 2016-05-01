<?php
/*
Plugin Name: block-wpscan
Plugin URI: https://luispc.com/
Description: This plugin block wpscan, Proxy and Tor.
Author: rluisr
Version: 0.4.7
Author URI: https://luispc.com/
*/

/* Copyright 2016 rluisr (contact: @lu_iskun)

    Licence is GPLv2 (http://www.gnu.org/licenses/gpl-2.0.html)

    This plugin block Tor, Proxy, Command Line access and wpscan. But it can't block all unauthorized access.
    Tor access is blocked by Tornodelist. If Tor access is isn't registration of Tornodelist, It can't block Tor access.
    About 80% can block.

    * Exception IPs.
    * Proxy, Tor block ON / OFF.
    * Edit message.
    * Log function.

    You should add server's global ip for other plugins. ex)Broken Link Checker.
    Googlebot and more can access own server.

    If you have any problems or requests, Please contact me with github or twitter.
    Twitter : https://twitter.com/lu_iskun
    Github  : https://github.com/rluisr/block-wpscan
*/

/*  Using jquery-searcher for search on Log function
    License is MIT. https://github.com/lloiser/jquery-searcher/blob/master/LICENSE
*/

/* Block direct access */
if (!defined('ABSPATH')) {
    die('Direct access not allowed!');
}

/* Set Timezone */
date_default_timezone_set(get_option('timezone'));

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
        wp_enqueue_script('bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',
            array(), null, false);
        wp_enqueue_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        wp_enqueue_script('bw.js', plugin_dir_url(__FILE__) . 'assets/js/style.js', array('jquery'), null, false);
        wp_enqueue_script('quick.js', plugin_dir_url(__FILE__) . 'assets/js/jquery.searcher.js', array('jquery'), null,
            true);
        wp_enqueue_script('search.js', plugin_dir_url(__FILE__) . 'assets/js/search.js', array('quick.js'), null, true);
    }
}

/**
 * 管理関連の設定
 */
function admin_block_wpscan()
{
    add_menu_page('block-wpscan', 'block-wpscan', 'administrator', 'block-wpscan', 'menu_block_wpscan',
        plugin_dir_url(__FILE__) . 'assets/images/icon.png');
}

/**
 * 管理画面
 */
function menu_block_wpscan()
{
    if (isset($_POST['msg']) || isset($_POST['proxy']) || isset($_POST['tor']) || isset($_POST['ip']) || isset($_POST['log']) || isset($_POST['timezone']) && check_admin_referer('check_admin_referer')) {
        update_option('timezone',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'timezone', FILTER_SANITIZE_SPECIAL_CHARS),
                ENT_QUOTES)));
        update_option('first',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'first', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        @update_option('msg', $_POST['msg']);
        @update_option('redirect', esc_html(filter_input(INPUT_POST, 'redirect', FILTER_VALIDATE_URL)));
        update_option('proxy',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'proxy', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('tor',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'tor', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('ip',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
        update_option('log',
            esc_html(htmlspecialchars(filter_input(INPUT_POST, 'log', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES)));
    }

    $msg = get_option('msg');
    $redirect = get_option('redirect');
    $proxy = get_option('proxy');
    $tor = get_option('tor');
    $ip = get_option('ip');
    $log = get_option('log');

    /* Delete Block list */
    if (isset($_POST['delete'])) {
        unlink(WP_CONTENT_DIR . '/block-wpscan/block.list');
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
                                <h3>1. Set your Timezone.</h3>
                                <select name="timezone" size="1">
                                    <?php
                                    $tz_list = "<option value=\"Africa/Abidjan\">Africa/Abidjan</option>
                                    <option value=\"Africa/Accra\">Africa/Accra</option>
                                    <option value=\"Africa/Addis_Ababa\">Africa/Addis_Ababa</option>
                                    <option value=\"Africa/Algiers\">Africa/Algiers</option>
                                    <option value=\"Africa/Asmara\">Africa/Asmara</option>
                                    <option value=\"Africa/Bamako\">Africa/Bamako</option>
                                    <option value=\"Africa/Bangui\">Africa/Bangui</option>
                                    <option value=\"Africa/Banjul\">Africa/Banjul</option>
                                    <option value=\"Africa/Bissau\">Africa/Bissau</option>
                                    <option value=\"Africa/Blantyre\">Africa/Blantyre</option>
                                    <option value=\"Africa/Brazzaville\">Africa/Brazzaville</option>
                                    <option value=\"Africa/Bujumbura\">Africa/Bujumbura</option>
                                    <option value=\"Africa/Cairo\">Africa/Cairo</option>
                                    <option value=\"Africa/Casablanca\">Africa/Casablanca</option>
                                    <option value=\"Africa/Ceuta\">Africa/Ceuta</option>
                                    <option value=\"Africa/Conakry\">Africa/Conakry</option>
                                    <option value=\"Africa/Dakar\">Africa/Dakar</option>
                                    <option value=\"Africa/Dar_es_Salaam\">Africa/Dar_es_Salaam</option>
                                    <option value=\"Africa/Djibouti\">Africa/Djibouti</option>
                                    <option value=\"Africa/Douala\">Africa/Douala</option>
                                    <option value=\"Africa/El_Aaiun\">Africa/El_Aaiun</option>
                                    <option value=\"Africa/Freetown\">Africa/Freetown</option>
                                    <option value=\"Africa/Gaborone\">Africa/Gaborone</option>
                                    <option value=\"Africa/Harare\">Africa/Harare</option>
                                    <option value=\"Africa/Johannesburg\">Africa/Johannesburg</option>
                                    <option value=\"Africa/Juba\">Africa/Juba</option>
                                    <option value=\"Africa/Kampala\">Africa/Kampala</option>
                                    <option value=\"Africa/Khartoum\">Africa/Khartoum</option>
                                    <option value=\"Africa/Kigali\">Africa/Kigali</option>
                                    <option value=\"Africa/Kinshasa\">Africa/Kinshasa</option>
                                    <option value=\"Africa/Lagos\">Africa/Lagos</option>
                                    <option value=\"Africa/Libreville\">Africa/Libreville</option>
                                    <option value=\"Africa/Lome\">Africa/Lome</option>
                                    <option value=\"Africa/Luanda\">Africa/Luanda</option>
                                    <option value=\"Africa/Lubumbashi\">Africa/Lubumbashi</option>
                                    <option value=\"Africa/Lusaka\">Africa/Lusaka</option>
                                    <option value=\"Africa/Malabo\">Africa/Malabo</option>
                                    <option value=\"Africa/Maputo\">Africa/Maputo</option>
                                    <option value=\"Africa/Maseru\">Africa/Maseru</option>
                                    <option value=\"Africa/Mbabane\">Africa/Mbabane</option>
                                    <option value=\"Africa/Mogadishu\">Africa/Mogadishu</option>
                                    <option value=\"Africa/Monrovia\">Africa/Monrovia</option>
                                    <option value=\"Africa/Nairobi\">Africa/Nairobi</option>
                                    <option value=\"Africa/Ndjamena\">Africa/Ndjamena</option>
                                    <option value=\"Africa/Niamey\">Africa/Niamey</option>
                                    <option value=\"Africa/Nouakchott\">Africa/Nouakchott</option>
                                    <option value=\"Africa/Ouagadougou\">Africa/Ouagadougou</option>
                                    <option value=\"Africa/Porto-Novo\">Africa/Porto-Novo</option>
                                    <option value=\"Africa/Sao_Tome\">Africa/Sao_Tome</option>
                                    <option value=\"Africa/Tripoli\">Africa/Tripoli</option>
                                    <option value=\"Africa/Tunis\">Africa/Tunis</option>
                                    <option value=\"Africa/Windhoek\">Africa/Windhoek</option>
                                    <option value=\"America/Adak\">America/Adak</option>
                                    <option value=\"America/Anchorage\">America/Anchorage</option>
                                    <option value=\"America/Anguilla\">America/Anguilla</option>
                                    <option value=\"America/Antigua\">America/Antigua</option>
                                    <option value=\"America/Araguaina\">America/Araguaina</option>
                                    <option value=\"America/Argentina/Buenos_Aires\">America/Argentina/Buenos_Aires
                                    </option>
                                    <option value=\"America/Argentina/Catamarca\">America/Argentina/Catamarca</option>
                                    <option value=\"America/Argentina/Cordoba\">America/Argentina/Cordoba</option>
                                    <option value=\"America/Argentina/Jujuy\">America/Argentina/Jujuy</option>
                                    <option value=\"America/Argentina/La_Rioja\">America/Argentina/La_Rioja</option>
                                    <option value=\"America/Argentina/Mendoza\">America/Argentina/Mendoza</option>
                                    <option value=\"America/Argentina/Rio_Gallegos\">America/Argentina/Rio_Gallegos
                                    </option>
                                    <option value=\"America/Argentina/Salta\">America/Argentina/Salta</option>
                                    <option value=\"America/Argentina/San_Juan\">America/Argentina/San_Juan</option>
                                    <option value=\"America/Argentina/San_Luis\">America/Argentina/San_Luis</option>
                                    <option value=\"America/Argentina/Tucuman\">America/Argentina/Tucuman</option>
                                    <option value=\"America/Argentina/Ushuaia\">America/Argentina/Ushuaia</option>
                                    <option value=\"America/Aruba\">America/Aruba</option>
                                    <option value=\"America/Asuncion\">America/Asuncion</option>
                                    <option value=\"America/Atikokan\">America/Atikokan</option>
                                    <option value=\"America/Bahia\">America/Bahia</option>
                                    <option value=\"America/Bahia_Banderas\">America/Bahia_Banderas</option>
                                    <option value=\"America/Barbados\">America/Barbados</option>
                                    <option value=\"America/Belem\">America/Belem</option>
                                    <option value=\"America/Belize\">America/Belize</option>
                                    <option value=\"America/Blanc-Sablon\">America/Blanc-Sablon</option>
                                    <option value=\"America/Boa_Vista\">America/Boa_Vista</option>
                                    <option value=\"America/Bogota\">America/Bogota</option>
                                    <option value=\"America/Boise\">America/Boise</option>
                                    <option value=\"America/Cambridge_Bay\">America/Cambridge_Bay</option>
                                    <option value=\"America/Campo_Grande\">America/Campo_Grande</option>
                                    <option value=\"America/Cancun\">America/Cancun</option>
                                    <option value=\"America/Caracas\">America/Caracas</option>
                                    <option value=\"America/Cayenne\">America/Cayenne</option>
                                    <option value=\"America/Cayman\">America/Cayman</option>
                                    <option value=\"America/Chicago\">America/Chicago</option>
                                    <option value=\"America/Chihuahua\">America/Chihuahua</option>
                                    <option value=\"America/Costa_Rica\">America/Costa_Rica</option>
                                    <option value=\"America/Creston\">America/Creston</option>
                                    <option value=\"America/Cuiaba\">America/Cuiaba</option>
                                    <option value=\"America/Curacao\">America/Curacao</option>
                                    <option value=\"America/Danmarkshavn\">America/Danmarkshavn</option>
                                    <option value=\"America/Dawson\">America/Dawson</option>
                                    <option value=\"America/Dawson_Creek\">America/Dawson_Creek</option>
                                    <option value=\"America/Denver\">America/Denver</option>
                                    <option value=\"America/Detroit\">America/Detroit</option>
                                    <option value=\"America/Dominica\">America/Dominica</option>
                                    <option value=\"America/Edmonton\">America/Edmonton</option>
                                    <option value=\"America/Eirunepe\">America/Eirunepe</option>
                                    <option value=\"America/El_Salvador\">America/El_Salvador</option>
                                    <option value=\"America/Fort_Nelson\">America/Fort_Nelson</option>
                                    <option value=\"America/Fortaleza\">America/Fortaleza</option>
                                    <option value=\"America/Glace_Bay\">America/Glace_Bay</option>
                                    <option value=\"America/Godthab\">America/Godthab</option>
                                    <option value=\"America/Goose_Bay\">America/Goose_Bay</option>
                                    <option value=\"America/Grand_Turk\">America/Grand_Turk</option>
                                    <option value=\"America/Grenada\">America/Grenada</option>
                                    <option value=\"America/Guadeloupe\">America/Guadeloupe</option>
                                    <option value=\"America/Guatemala\">America/Guatemala</option>
                                    <option value=\"America/Guayaquil\">America/Guayaquil</option>
                                    <option value=\"America/Guyana\">America/Guyana</option>
                                    <option value=\"America/Halifax\">America/Halifax</option>
                                    <option value=\"America/Havana\">America/Havana</option>
                                    <option value=\"America/Hermosillo\">America/Hermosillo</option>
                                    <option value=\"America/Indiana/Indianapolis\">America/Indiana/Indianapolis</option>
                                    <option value=\"America/Indiana/Knox\">America/Indiana/Knox</option>
                                    <option value=\"America/Indiana/Marengo\">America/Indiana/Marengo</option>
                                    <option value=\"America/Indiana/Petersburg\">America/Indiana/Petersburg</option>
                                    <option value=\"America/Indiana/Tell_City\">America/Indiana/Tell_City</option>
                                    <option value=\"America/Indiana/Vevay\">America/Indiana/Vevay</option>
                                    <option value=\"America/Indiana/Vincennes\">America/Indiana/Vincennes</option>
                                    <option value=\"America/Indiana/Winamac\">America/Indiana/Winamac</option>
                                    <option value=\"America/Inuvik\">America/Inuvik</option>
                                    <option value=\"America/Iqaluit\">America/Iqaluit</option>
                                    <option value=\"America/Jamaica\">America/Jamaica</option>
                                    <option value=\"America/Juneau\">America/Juneau</option>
                                    <option value=\"America/Kentucky/Louisville\">America/Kentucky/Louisville</option>
                                    <option value=\"America/Kentucky/Monticello\">America/Kentucky/Monticello</option>
                                    <option value=\"America/Kralendijk\">America/Kralendijk</option>
                                    <option value=\"America/La_Paz\">America/La_Paz</option>
                                    <option value=\"America/Lima\">America/Lima</option>
                                    <option value=\"America/Los_Angeles\">America/Los_Angeles</option>
                                    <option value=\"America/Lower_Princes\">America/Lower_Princes</option>
                                    <option value=\"America/Maceio\">America/Maceio</option>
                                    <option value=\"America/Managua\">America/Managua</option>
                                    <option value=\"America/Manaus\">America/Manaus</option>
                                    <option value=\"America/Marigot\">America/Marigot</option>
                                    <option value=\"America/Martinique\">America/Martinique</option>
                                    <option value=\"America/Matamoros\">America/Matamoros</option>
                                    <option value=\"America/Mazatlan\">America/Mazatlan</option>
                                    <option value=\"America/Menominee\">America/Menominee</option>
                                    <option value=\"America/Merida\">America/Merida</option>
                                    <option value=\"America/Metlakatla\">America/Metlakatla</option>
                                    <option value=\"America/Mexico_City\">America/Mexico_City</option>
                                    <option value=\"America/Miquelon\">America/Miquelon</option>
                                    <option value=\"America/Moncton\">America/Moncton</option>
                                    <option value=\"America/Monterrey\">America/Monterrey</option>
                                    <option value=\"America/Montevideo\">America/Montevideo</option>
                                    <option value=\"America/Montserrat\">America/Montserrat</option>
                                    <option value=\"America/Nassau\">America/Nassau</option>
                                    <option value=\"America/New_York\">America/New_York</option>
                                    <option value=\"America/Nipigon\">America/Nipigon</option>
                                    <option value=\"America/Nome\">America/Nome</option>
                                    <option value=\"America/Noronha\">America/Noronha</option>
                                    <option value=\"America/North_Dakota/Beulah\">America/North_Dakota/Beulah</option>
                                    <option value=\"America/North_Dakota/Center\">America/North_Dakota/Center</option>
                                    <option value=\"America/North_Dakota/New_Salem\">America/North_Dakota/New_Salem
                                    </option>
                                    <option value=\"America/Ojinaga\">America/Ojinaga</option>
                                    <option value=\"America/Panama\">America/Panama</option>
                                    <option value=\"America/Pangnirtung\">America/Pangnirtung</option>
                                    <option value=\"America/Paramaribo\">America/Paramaribo</option>
                                    <option value=\"America/Phoenix\">America/Phoenix</option>
                                    <option value=\"America/Port-au-Prince\">America/Port-au-Prince</option>
                                    <option value=\"America/Port_of_Spain\">America/Port_of_Spain</option>
                                    <option value=\"America/Porto_Velho\">America/Porto_Velho</option>
                                    <option value=\"America/Puerto_Rico\">America/Puerto_Rico</option>
                                    <option value=\"America/Rainy_River\">America/Rainy_River</option>
                                    <option value=\"America/Rankin_Inlet\">America/Rankin_Inlet</option>
                                    <option value=\"America/Recife\">America/Recife</option>
                                    <option value=\"America/Regina\">America/Regina</option>
                                    <option value=\"America/Resolute\">America/Resolute</option>
                                    <option value=\"America/Rio_Branco\">America/Rio_Branco</option>
                                    <option value=\"America/Santarem\">America/Santarem</option>
                                    <option value=\"America/Santiago\">America/Santiago</option>
                                    <option value=\"America/Santo_Domingo\">America/Santo_Domingo</option>
                                    <option value=\"America/Sao_Paulo\">America/Sao_Paulo</option>
                                    <option value=\"America/Scoresbysund\">America/Scoresbysund</option>
                                    <option value=\"America/Sitka\">America/Sitka</option>
                                    <option value=\"America/St_Barthelemy\">America/St_Barthelemy</option>
                                    <option value=\"America/St_Johns\">America/St_Johns</option>
                                    <option value=\"America/St_Kitts\">America/St_Kitts</option>
                                    <option value=\"America/St_Lucia\">America/St_Lucia</option>
                                    <option value=\"America/St_Thomas\">America/St_Thomas</option>
                                    <option value=\"America/St_Vincent\">America/St_Vincent</option>
                                    <option value=\"America/Swift_Current\">America/Swift_Current</option>
                                    <option value=\"America/Tegucigalpa\">America/Tegucigalpa</option>
                                    <option value=\"America/Thule\">America/Thule</option>
                                    <option value=\"America/Thunder_Bay\">America/Thunder_Bay</option>
                                    <option value=\"America/Tijuana\">America/Tijuana</option>
                                    <option value=\"America/Toronto\">America/Toronto</option>
                                    <option value=\"America/Tortola\">America/Tortola</option>
                                    <option value=\"America/Vancouver\">America/Vancouver</option>
                                    <option value=\"America/Whitehorse\">America/Whitehorse</option>
                                    <option value=\"America/Winnipeg\">America/Winnipeg</option>
                                    <option value=\"America/Yakutat\">America/Yakutat</option>
                                    <option value=\"America/Yellowknife\">America/Yellowknife</option>
                                    <option value=\"Antarctica/Casey\">Antarctica/Casey</option>
                                    <option value=\"Antarctica/Davis\">Antarctica/Davis</option>
                                    <option value=\"Antarctica/DumontDUrville\">Antarctica/DumontDUrville</option>
                                    <option value=\"Antarctica/Macquarie\">Antarctica/Macquarie</option>
                                    <option value=\"Antarctica/Mawson\">Antarctica/Mawson</option>
                                    <option value=\"Antarctica/McMurdo\">Antarctica/McMurdo</option>
                                    <option value=\"Antarctica/Palmer\">Antarctica/Palmer</option>
                                    <option value=\"Antarctica/Rothera\">Antarctica/Rothera</option>
                                    <option value=\"Antarctica/Syowa\">Antarctica/Syowa</option>
                                    <option value=\"Antarctica/Troll\">Antarctica/Troll</option>
                                    <option value=\"Antarctica/Vostok\">Antarctica/Vostok</option>
                                    <option value=\"Arctic/Longyearbyen\">Arctic/Longyearbyen</option>
                                    <option value=\"Asia/Aden\">Asia/Aden</option>
                                    <option value=\"Asia/Almaty\">Asia/Almaty</option>
                                    <option value=\"Asia/Amman\">Asia/Amman</option>
                                    <option value=\"Asia/Anadyr\">Asia/Anadyr</option>
                                    <option value=\"Asia/Aqtau\">Asia/Aqtau</option>
                                    <option value=\"Asia/Aqtobe\">Asia/Aqtobe</option>
                                    <option value=\"Asia/Ashgabat\">Asia/Ashgabat</option>
                                    <option value=\"Asia/Baghdad\">Asia/Baghdad</option>
                                    <option value=\"Asia/Bahrain\">Asia/Bahrain</option>
                                    <option value=\"Asia/Baku\">Asia/Baku</option>
                                    <option value=\"Asia/Bangkok\">Asia/Bangkok</option>
                                    <option value=\"Asia/Barnaul\">Asia/Barnaul</option>
                                    <option value=\"Asia/Beirut\">Asia/Beirut</option>
                                    <option value=\"Asia/Bishkek\">Asia/Bishkek</option>
                                    <option value=\"Asia/Brunei\">Asia/Brunei</option>
                                    <option value=\"Asia/Chita\">Asia/Chita</option>
                                    <option value=\"Asia/Choibalsan\">Asia/Choibalsan</option>
                                    <option value=\"Asia/Colombo\">Asia/Colombo</option>
                                    <option value=\"Asia/Damascus\">Asia/Damascus</option>
                                    <option value=\"Asia/Dhaka\">Asia/Dhaka</option>
                                    <option value=\"Asia/Dili\">Asia/Dili</option>
                                    <option value=\"Asia/Dubai\">Asia/Dubai</option>
                                    <option value=\"Asia/Dushanbe\">Asia/Dushanbe</option>
                                    <option value=\"Asia/Gaza\">Asia/Gaza</option>
                                    <option value=\"Asia/Hebron\">Asia/Hebron</option>
                                    <option value=\"Asia/Ho_Chi_Minh\">Asia/Ho_Chi_Minh</option>
                                    <option value=\"Asia/Hong_Kong\">Asia/Hong_Kong</option>
                                    <option value=\"Asia/Hovd\">Asia/Hovd</option>
                                    <option value=\"Asia/Irkutsk\">Asia/Irkutsk</option>
                                    <option value=\"Asia/Jakarta\">Asia/Jakarta</option>
                                    <option value=\"Asia/Jayapura\">Asia/Jayapura</option>
                                    <option value=\"Asia/Jerusalem\">Asia/Jerusalem</option>
                                    <option value=\"Asia/Kabul\">Asia/Kabul</option>
                                    <option value=\"Asia/Kamchatka\">Asia/Kamchatka</option>
                                    <option value=\"Asia/Karachi\">Asia/Karachi</option>
                                    <option value=\"Asia/Kathmandu\">Asia/Kathmandu</option>
                                    <option value=\"Asia/Khandyga\">Asia/Khandyga</option>
                                    <option value=\"Asia/Kolkata\">Asia/Kolkata</option>
                                    <option value=\"Asia/Krasnoyarsk\">Asia/Krasnoyarsk</option>
                                    <option value=\"Asia/Kuala_Lumpur\">Asia/Kuala_Lumpur</option>
                                    <option value=\"Asia/Kuching\">Asia/Kuching</option>
                                    <option value=\"Asia/Kuwait\">Asia/Kuwait</option>
                                    <option value=\"Asia/Macau\">Asia/Macau</option>
                                    <option value=\"Asia/Magadan\">Asia/Magadan</option>
                                    <option value=\"Asia/Makassar\">Asia/Makassar</option>
                                    <option value=\"Asia/Manila\">Asia/Manila</option>
                                    <option value=\"Asia/Muscat\">Asia/Muscat</option>
                                    <option value=\"Asia/Nicosia\">Asia/Nicosia</option>
                                    <option value=\"Asia/Novokuznetsk\">Asia/Novokuznetsk</option>
                                    <option value=\"Asia/Novosibirsk\">Asia/Novosibirsk</option>
                                    <option value=\"Asia/Omsk\">Asia/Omsk</option>
                                    <option value=\"Asia/Oral\">Asia/Oral</option>
                                    <option value=\"Asia/Phnom_Penh\">Asia/Phnom_Penh</option>
                                    <option value=\"Asia/Pontianak\">Asia/Pontianak</option>
                                    <option value=\"Asia/Pyongyang\">Asia/Pyongyang</option>
                                    <option value=\"Asia/Qatar\">Asia/Qatar</option>
                                    <option value=\"Asia/Qyzylorda\">Asia/Qyzylorda</option>
                                    <option value=\"Asia/Rangoon\">Asia/Rangoon</option>
                                    <option value=\"Asia/Riyadh\">Asia/Riyadh</option>
                                    <option value=\"Asia/Sakhalin\">Asia/Sakhalin</option>
                                    <option value=\"Asia/Samarkand\">Asia/Samarkand</option>
                                    <option value=\"Asia/Seoul\">Asia/Seoul</option>
                                    <option value=\"Asia/Shanghai\">Asia/Shanghai</option>
                                    <option value=\"Asia/Singapore\">Asia/Singapore</option>
                                    <option value=\"Asia/Srednekolymsk\">Asia/Srednekolymsk</option>
                                    <option value=\"Asia/Taipei\">Asia/Taipei</option>
                                    <option value=\"Asia/Tashkent\">Asia/Tashkent</option>
                                    <option value=\"Asia/Tbilisi\">Asia/Tbilisi</option>
                                    <option value=\"Asia/Tehran\">Asia/Tehran</option>
                                    <option value=\"Asia/Thimphu\">Asia/Thimphu</option>
                                    <option value=\"Asia/Tokyo\">Asia/Tokyo</option>
                                    <option value=\"Asia/Tomsk\">Asia/Tomsk</option>
                                    <option value=\"Asia/Ulaanbaatar\">Asia/Ulaanbaatar</option>
                                    <option value=\"Asia/Urumqi\">Asia/Urumqi</option>
                                    <option value=\"Asia/Ust-Nera\">Asia/Ust-Nera</option>
                                    <option value=\"Asia/Vientiane\">Asia/Vientiane</option>
                                    <option value=\"Asia/Vladivostok\">Asia/Vladivostok</option>
                                    <option value=\"Asia/Yakutsk\">Asia/Yakutsk</option>
                                    <option value=\"Asia/Yekaterinburg\">Asia/Yekaterinburg</option>
                                    <option value=\"Asia/Yerevan\">Asia/Yerevan</option>
                                    <option value=\"Atlantic/Azores\">Atlantic/Azores</option>
                                    <option value=\"Atlantic/Bermuda\">Atlantic/Bermuda</option>
                                    <option value=\"Atlantic/Canary\">Atlantic/Canary</option>
                                    <option value=\"Atlantic/Cape_Verde\">Atlantic/Cape_Verde</option>
                                    <option value=\"Atlantic/Faroe\">Atlantic/Faroe</option>
                                    <option value=\"Atlantic/Madeira\">Atlantic/Madeira</option>
                                    <option value=\"Atlantic/Reykjavik\">Atlantic/Reykjavik</option>
                                    <option value=\"Atlantic/South_Georgia\">Atlantic/South_Georgia</option>
                                    <option value=\"Atlantic/St_Helena\">Atlantic/St_Helena</option>
                                    <option value=\"Atlantic/Stanley\">Atlantic/Stanley</option>
                                    <option value=\"Australia/Adelaide\">Australia/Adelaide</option>
                                    <option value=\"Australia/Brisbane\">Australia/Brisbane</option>
                                    <option value=\"Australia/Broken_Hill\">Australia/Broken_Hill</option>
                                    <option value=\"Australia/Currie\">Australia/Currie</option>
                                    <option value=\"Australia/Darwin\">Australia/Darwin</option>
                                    <option value=\"Australia/Eucla\">Australia/Eucla</option>
                                    <option value=\"Australia/Hobart\">Australia/Hobart</option>
                                    <option value=\"Australia/Lindeman\">Australia/Lindeman</option>
                                    <option value=\"Australia/Lord_Howe\">Australia/Lord_Howe</option>
                                    <option value=\"Australia/Melbourne\">Australia/Melbourne</option>
                                    <option value=\"Australia/Perth\">Australia/Perth</option>
                                    <option value=\"Australia/Sydney\">Australia/Sydney</option>
                                    <option value=\"Europe/Amsterdam\">Europe/Amsterdam</option>
                                    <option value=\"Europe/Andorra\">Europe/Andorra</option>
                                    <option value=\"Europe/Astrakhan\">Europe/Astrakhan</option>
                                    <option value=\"Europe/Athens\">Europe/Athens</option>
                                    <option value=\"Europe/Belgrade\">Europe/Belgrade</option>
                                    <option value=\"Europe/Berlin\">Europe/Berlin</option>
                                    <option value=\"Europe/Bratislava\">Europe/Bratislava</option>
                                    <option value=\"Europe/Brussels\">Europe/Brussels</option>
                                    <option value=\"Europe/Bucharest\">Europe/Bucharest</option>
                                    <option value=\"Europe/Budapest\">Europe/Budapest</option>
                                    <option value=\"Europe/Busingen\">Europe/Busingen</option>
                                    <option value=\"Europe/Chisinau\">Europe/Chisinau</option>
                                    <option value=\"Europe/Copenhagen\">Europe/Copenhagen</option>
                                    <option value=\"Europe/Dublin\">Europe/Dublin</option>
                                    <option value=\"Europe/Gibraltar\">Europe/Gibraltar</option>
                                    <option value=\"Europe/Guernsey\">Europe/Guernsey</option>
                                    <option value=\"Europe/Helsinki\">Europe/Helsinki</option>
                                    <option value=\"Europe/Isle_of_Man\">Europe/Isle_of_Man</option>
                                    <option value=\"Europe/Istanbul\">Europe/Istanbul</option>
                                    <option value=\"Europe/Jersey\">Europe/Jersey</option>
                                    <option value=\"Europe/Kaliningrad\">Europe/Kaliningrad</option>
                                    <option value=\"Europe/Kiev\">Europe/Kiev</option>
                                    <option value=\"Europe/Kirov\">Europe/Kirov</option>
                                    <option value=\"Europe/Lisbon\">Europe/Lisbon</option>
                                    <option value=\"Europe/Ljubljana\">Europe/Ljubljana</option>
                                    <option value=\"Europe/London\">Europe/London</option>
                                    <option value=\"Europe/Luxembourg\">Europe/Luxembourg</option>
                                    <option value=\"Europe/Madrid\">Europe/Madrid</option>
                                    <option value=\"Europe/Malta\">Europe/Malta</option>
                                    <option value=\"Europe/Mariehamn\">Europe/Mariehamn</option>
                                    <option value=\"Europe/Minsk\">Europe/Minsk</option>
                                    <option value=\"Europe/Monaco\">Europe/Monaco</option>
                                    <option value=\"Europe/Moscow\">Europe/Moscow</option>
                                    <option value=\"Europe/Oslo\">Europe/Oslo</option>
                                    <option value=\"Europe/Paris\">Europe/Paris</option>
                                    <option value=\"Europe/Podgorica\">Europe/Podgorica</option>
                                    <option value=\"Europe/Prague\">Europe/Prague</option>
                                    <option value=\"Europe/Riga\">Europe/Riga</option>
                                    <option value=\"Europe/Rome\">Europe/Rome</option>
                                    <option value=\"Europe/Samara\">Europe/Samara</option>
                                    <option value=\"Europe/San_Marino\">Europe/San_Marino</option>
                                    <option value=\"Europe/Sarajevo\">Europe/Sarajevo</option>
                                    <option value=\"Europe/Simferopol\">Europe/Simferopol</option>
                                    <option value=\"Europe/Skopje\">Europe/Skopje</option>
                                    <option value=\"Europe/Sofia\">Europe/Sofia</option>
                                    <option value=\"Europe/Stockholm\">Europe/Stockholm</option>
                                    <option value=\"Europe/Tallinn\">Europe/Tallinn</option>
                                    <option value=\"Europe/Tirane\">Europe/Tirane</option>
                                    <option value=\"Europe/Ulyanovsk\">Europe/Ulyanovsk</option>
                                    <option value=\"Europe/Uzhgorod\">Europe/Uzhgorod</option>
                                    <option value=\"Europe/Vaduz\">Europe/Vaduz</option>
                                    <option value=\"Europe/Vatican\">Europe/Vatican</option>
                                    <option value=\"Europe/Vienna\">Europe/Vienna</option>
                                    <option value=\"Europe/Vilnius\">Europe/Vilnius</option>
                                    <option value=\"Europe/Volgograd\">Europe/Volgograd</option>
                                    <option value=\"Europe/Warsaw\">Europe/Warsaw</option>
                                    <option value=\"Europe/Zagreb\">Europe/Zagreb</option>
                                    <option value=\"Europe/Zaporozhye\">Europe/Zaporozhye</option>
                                    <option value=\"Europe/Zurich\">Europe/Zurich</option>
                                    <option value=\"Indian/Antananarivo\">Indian/Antananarivo</option>
                                    <option value=\"Indian/Chagos\">Indian/Chagos</option>
                                    <option value=\"Indian/Christmas\">Indian/Christmas</option>
                                    <option value=\"Indian/Cocos\">Indian/Cocos</option>
                                    <option value=\"Indian/Comoro\">Indian/Comoro</option>
                                    <option value=\"Indian/Kerguelen\">Indian/Kerguelen</option>
                                    <option value=\"Indian/Mahe\">Indian/Mahe</option>
                                    <option value=\"Indian/Maldives\">Indian/Maldives</option>
                                    <option value=\"Indian/Mauritius\">Indian/Mauritius</option>
                                    <option value=\"Indian/Mayotte\">Indian/Mayotte</option>
                                    <option value=\"Indian/Reunion\">Indian/Reunion</option>
                                    <option value=\"Pacific/Apia\">Pacific/Apia</option>
                                    <option value=\"Pacific/Auckland\">Pacific/Auckland</option>
                                    <option value=\"Pacific/Bougainville\">Pacific/Bougainville</option>
                                    <option value=\"Pacific/Chatham\">Pacific/Chatham</option>
                                    <option value=\"Pacific/Chuuk\">Pacific/Chuuk</option>
                                    <option value=\"Pacific/Easter\">Pacific/Easter</option>
                                    <option value=\"Pacific/Efate\">Pacific/Efate</option>
                                    <option value=\"Pacific/Enderbury\">Pacific/Enderbury</option>
                                    <option value=\"Pacific/Fakaofo\">Pacific/Fakaofo</option>
                                    <option value=\"Pacific/Fiji\">Pacific/Fiji</option>
                                    <option value=\"Pacific/Funafuti\">Pacific/Funafuti</option>
                                    <option value=\"Pacific/Galapagos\">Pacific/Galapagos</option>
                                    <option value=\"Pacific/Gambier\">Pacific/Gambier</option>
                                    <option value=\"Pacific/Guadalcanal\">Pacific/Guadalcanal</option>
                                    <option value=\"Pacific/Guam\">Pacific/Guam</option>
                                    <option value=\"Pacific/Honolulu\">Pacific/Honolulu</option>
                                    <option value=\"Pacific/Johnston\">Pacific/Johnston</option>
                                    <option value=\"Pacific/Kiritimati\">Pacific/Kiritimati</option>
                                    <option value=\"Pacific/Kosrae\">Pacific/Kosrae</option>
                                    <option value=\"Pacific/Kwajalein\">Pacific/Kwajalein</option>
                                    <option value=\"Pacific/Majuro\">Pacific/Majuro</option>
                                    <option value=\"Pacific/Marquesas\">Pacific/Marquesas</option>
                                    <option value=\"Pacific/Midway\">Pacific/Midway</option>
                                    <option value=\"Pacific/Nauru\">Pacific/Nauru</option>
                                    <option value=\"Pacific/Niue\">Pacific/Niue</option>
                                    <option value=\"Pacific/Norfolk\">Pacific/Norfolk</option>
                                    <option value=\"Pacific/Noumea\">Pacific/Noumea</option>
                                    <option value=\"Pacific/Pago_Pago\">Pacific/Pago_Pago</option>
                                    <option value=\"Pacific/Palau\">Pacific/Palau</option>
                                    <option value=\"Pacific/Pitcairn\">Pacific/Pitcairn</option>
                                    <option value=\"Pacific/Pohnpei\">Pacific/Pohnpei</option>
                                    <option value=\"Pacific/Port_Moresby\">Pacific/Port_Moresby</option>
                                    <option value=\"Pacific/Rarotonga\">Pacific/Rarotonga</option>
                                    <option value=\"Pacific/Saipan\">Pacific/Saipan</option>
                                    <option value=\"Pacific/Tahiti\">Pacific/Tahiti</option>
                                    <option value=\"Pacific/Tarawa\">Pacific/Tarawa</option>
                                    <option value=\"Pacific/Tongatapu\">Pacific/Tongatapu</option>
                                    <option value=\"Pacific/Wake\">Pacific/Wake</option>
                                    <option value=\"Pacific/Wallis\">Pacific/Wallis</option>
                                    <option value=\"Africa/Asmera\">Africa/Asmera</option>
                                    <option value=\"Africa/Timbuktu\">Africa/Timbuktu</option>
                                    <option value=\"America/Argentina/ComodRivadavia\">America/Argentina/ComodRivadavia
                                    </option>
                                    <option value=\"America/Atka\">America/Atka</option>
                                    <option value=\"America/Buenos_Aires\">America/Buenos_Aires</option>
                                    <option value=\"America/Catamarca\">America/Catamarca</option>
                                    <option value=\"America/Coral_Harbour\">America/Coral_Harbour</option>
                                    <option value=\"America/Cordoba\">America/Cordoba</option>
                                    <option value=\"America/Ensenada\">America/Ensenada</option>
                                    <option value=\"America/Fort_Wayne\">America/Fort_Wayne</option>
                                    <option value=\"America/Indianapolis\">America/Indianapolis</option>
                                    <option value=\"America/Jujuy\">America/Jujuy</option>
                                    <option value=\"America/Knox_IN\">America/Knox_IN</option>
                                    <option value=\"America/Louisville\">America/Louisville</option>
                                    <option value=\"America/Mendoza\">America/Mendoza</option>
                                    <option value=\"America/Montreal\">America/Montreal</option>
                                    <option value=\"America/Porto_Acre\">America/Porto_Acre</option>
                                    <option value=\"America/Rosario\">America/Rosario</option>
                                    <option value=\"America/Santa_Isabel\">America/Santa_Isabel</option>
                                    <option value=\"America/Shiprock\">America/Shiprock</option>
                                    <option value=\"America/Virgin\">America/Virgin</option>
                                    <option value=\"Antarctica/South_Pole\">Antarctica/South_Pole</option>
                                    <option value=\"Asia/Ashkhabad\">Asia/Ashkhabad</option>
                                    <option value=\"Asia/Calcutta\">Asia/Calcutta</option>
                                    <option value=\"Asia/Chongqing\">Asia/Chongqing</option>
                                    <option value=\"Asia/Chungking\">Asia/Chungking</option>
                                    <option value=\"Asia/Dacca\">Asia/Dacca</option>
                                    <option value=\"Asia/Harbin\">Asia/Harbin</option>
                                    <option value=\"Asia/Istanbul\">Asia/Istanbul</option>
                                    <option value=\"Asia/Kashgar\">Asia/Kashgar</option>
                                    <option value=\"Asia/Katmandu\">Asia/Katmandu</option>
                                    <option value=\"Asia/Macao\">Asia/Macao</option>
                                    <option value=\"Asia/Saigon\">Asia/Saigon</option>
                                    <option value=\"Asia/Tel_Aviv\">Asia/Tel_Aviv</option>
                                    <option value=\"Asia/Thimbu\">Asia/Thimbu</option>
                                    <option value=\"Asia/Ujung_Pandang\">Asia/Ujung_Pandang</option>
                                    <option value=\"Asia/Ulan_Bator\">Asia/Ulan_Bator</option>
                                    <option value=\"Atlantic/Faeroe\">Atlantic/Faeroe</option>
                                    <option value=\"Atlantic/Jan_Mayen\">Atlantic/Jan_Mayen</option>
                                    <option value=\"Australia/ACT\">Australia/ACT</option>
                                    <option value=\"Australia/Canberra\">Australia/Canberra</option>
                                    <option value=\"Australia/LHI\">Australia/LHI</option>
                                    <option value=\"Australia/North\">Australia/North</option>
                                    <option value=\"Australia/NSW\">Australia/NSW</option>
                                    <option value=\"Australia/Queensland\">Australia/Queensland</option>
                                    <option value=\"Australia/South\">Australia/South</option>
                                    <option value=\"Australia/Tasmania\">Australia/Tasmania</option>
                                    <option value=\"Australia/Victoria\">Australia/Victoria</option>
                                    <option value=\"Australia/West\">Australia/West</option>
                                    <option value=\"Australia/Yancowinna\">Australia/Yancowinna</option>
                                    <option value=\"Brazil/Acre\">Brazil/Acre</option>
                                    <option value=\"Brazil/DeNoronha\">Brazil/DeNoronha</option>
                                    <option value=\"Brazil/East\">Brazil/East</option>
                                    <option value=\"Brazil/West\">Brazil/West</option>
                                    <option value=\"Canada/Atlantic\">Canada/Atlantic</option>
                                    <option value=\"Canada/Central\">Canada/Central</option>
                                    <option value=\"Canada/East-Saskatchewan\">Canada/East-Saskatchewan</option>
                                    <option value=\"Canada/Eastern\">Canada/Eastern</option>
                                    <option value=\"Canada/Mountain\">Canada/Mountain</option>
                                    <option value=\"Canada/Newfoundland\">Canada/Newfoundland</option>
                                    <option value=\"Canada/Pacific\">Canada/Pacific</option>
                                    <option value=\"Canada/Saskatchewan\">Canada/Saskatchewan</option>
                                    <option value=\"Canada/Yukon\">Canada/Yukon</option>
                                    <option value=\"CET\">CET</option>
                                    <option value=\"Chile/Continental\">Chile/Continental</option>
                                    <option value=\"Chile/EasterIsland\">Chile/EasterIsland</option>
                                    <option value=\"CST6CDT\">CST6CDT</option>
                                    <option value=\"Cuba\">Cuba</option>
                                    <option value=\"EET\">EET</option>
                                    <option value=\"Egypt\">Egypt</option>
                                    <option value=\"Eire\">Eire</option>
                                    <option value=\"EST\">EST</option>
                                    <option value=\"EST5EDT\">EST5EDT</option>
                                    <option value=\"Etc/GMT\">Etc/GMT</option>
                                    <option value=\"Etc/GMT+0\">Etc/GMT+0</option>
                                    <option value=\"Etc/GMT+1\">Etc/GMT+1</option>
                                    <option value=\"Etc/GMT+10\">Etc/GMT+10</option>
                                    <option value=\"Etc/GMT+11\">Etc/GMT+11</option>
                                    <option value=\"Etc/GMT+12\">Etc/GMT+12</option>
                                    <option value=\"Etc/GMT+2\">Etc/GMT+2</option>
                                    <option value=\"Etc/GMT+3\">Etc/GMT+3</option>
                                    <option value=\"Etc/GMT+4\">Etc/GMT+4</option>
                                    <option value=\"Etc/GMT+5\">Etc/GMT+5</option>
                                    <option value=\"Etc/GMT+6\">Etc/GMT+6</option>
                                    <option value=\"Etc/GMT+7\">Etc/GMT+7</option>
                                    <option value=\"Etc/GMT+8\">Etc/GMT+8</option>
                                    <option value=\"Etc/GMT+9\">Etc/GMT+9</option>
                                    <option value=\"Etc/GMT-0\">Etc/GMT-0</option>
                                    <option value=\"Etc/GMT-1\">Etc/GMT-1</option>
                                    <option value=\"Etc/GMT-10\">Etc/GMT-10</option>
                                    <option value=\"Etc/GMT-11\">Etc/GMT-11</option>
                                    <option value=\"Etc/GMT-12\">Etc/GMT-12</option>
                                    <option value=\"Etc/GMT-13\">Etc/GMT-13</option>
                                    <option value=\"Etc/GMT-14\">Etc/GMT-14</option>
                                    <option value=\"Etc/GMT-2\">Etc/GMT-2</option>
                                    <option value=\"Etc/GMT-3\">Etc/GMT-3</option>
                                    <option value=\"Etc/GMT-4\">Etc/GMT-4</option>
                                    <option value=\"Etc/GMT-5\">Etc/GMT-5</option>
                                    <option value=\"Etc/GMT-6\">Etc/GMT-6</option>
                                    <option value=\"Etc/GMT-7\">Etc/GMT-7</option>
                                    <option value=\"Etc/GMT-8\">Etc/GMT-8</option>
                                    <option value=\"Etc/GMT-9\">Etc/GMT-9</option>
                                    <option value=\"Etc/GMT0\">Etc/GMT0</option>
                                    <option value=\"Etc/Greenwich\">Etc/Greenwich</option>
                                    <option value=\"Etc/UCT\">Etc/UCT</option>
                                    <option value=\"Etc/Universal\">Etc/Universal</option>
                                    <option value=\"Etc/UTC\">Etc/UTC</option>
                                    <option value=\"Etc/Zulu\">Etc/Zulu</option>
                                    <option value=\"Europe/Belfast\">Europe/Belfast</option>
                                    <option value=\"Europe/Nicosia\">Europe/Nicosia</option>
                                    <option value=\"Europe/Tiraspol\">Europe/Tiraspol</option>
                                    <option value=\"Factory\">Factory</option>
                                    <option value=\"GB\">GB</option>
                                    <option value=\"GB-Eire\">GB-Eire</option>
                                    <option value=\"GMT\">GMT</option>
                                    <option value=\"GMT+0\">GMT+0</option>
                                    <option value=\"GMT-0\">GMT-0</option>
                                    <option value=\"GMT0\">GMT0</option>
                                    <option value=\"Greenwich\">Greenwich</option>
                                    <option value=\"Hongkong\">Hongkong</option>
                                    <option value=\"HST\">HST</option>
                                    <option value=\"Iceland\">Iceland</option>
                                    <option value=\"Iran\">Iran</option>
                                    <option value=\"Israel\">Israel</option>
                                    <option value=\"Jamaica\">Jamaica</option>
                                    <option value=\"Japan\">Japan</option>
                                    <option value=\"Kwajalein\">Kwajalein</option>
                                    <option value=\"Libya\">Libya</option>
                                    <option value=\"MET\">MET</option>
                                    <option value=\"Mexico/BajaNorte\">Mexico/BajaNorte</option>
                                    <option value=\"Mexico/BajaSur\">Mexico/BajaSur</option>
                                    <option value=\"Mexico/General\">Mexico/General</option>
                                    <option value=\"MST\">MST</option>
                                    <option value=\"MST7MDT\">MST7MDT</option>
                                    <option value=\"Navajo\">Navajo</option>
                                    <option value=\"NZ\">NZ</option>
                                    <option value=\"NZ-CHAT\">NZ-CHAT</option>
                                    <option value=\"Pacific/Ponape\">Pacific/Ponape</option>
                                    <option value=\"Pacific/Samoa\">Pacific/Samoa</option>
                                    <option value=\"Pacific/Truk\">Pacific/Truk</option>
                                    <option value=\"Pacific/Yap\">Pacific/Yap</option>
                                    <option value=\"Poland\">Poland</option>
                                    <option value=\"Portugal\">Portugal</option>
                                    <option value=\"PRC\">PRC</option>
                                    <option value=\"PST8PDT\">PST8PDT</option>
                                    <option value=\"ROC\">ROC</option>
                                    <option value=\"ROK\">ROK</option>
                                    <option value=\"Singapore\">Singapore</option>
                                    <option value=\"Turkey\">Turkey</option>
                                    <option value=\"UCT\">UCT</option>
                                    <option value=\"Universal\">Universal</option>
                                    <option value=\"US/Alaska\">US/Alaska</option>
                                    <option value=\"US/Aleutian\">US/Aleutian</option>
                                    <option value=\"US/Arizona\">US/Arizona</option>
                                    <option value=\"US/Central\">US/Central</option>
                                    <option value=\"US/East-Indiana\">US/East-Indiana</option>
                                    <option value=\"US/Eastern\">US/Eastern</option>
                                    <option value=\"US/Hawaii\">US/Hawaii</option>
                                    <option value=\"US/Indiana-Starke\">US/Indiana-Starke</option>
                                    <option value=\"US/Michigan\">US/Michigan</option>
                                    <option value=\"US/Mountain\">US/Mountain</option>
                                    <option value=\"US/Pacific\">US/Pacific</option>
                                    <option value=\"US/Pacific-New\">US/Pacific-New</option>
                                    <option value=\"US/Samoa\">US/Samoa</option>
                                    <option value=\"UTC\">UTC</option>
                                    <option value=\"W-SU\">W-SU</option>
                                    <option value=\"WET\">WET</option>
                                    <option value=\"Zulu\">Zulu</option>";

                                    $timezone = get_option('timezone');

                                    if (strpos($tz_list, $timezone) !== false) {
                                        $tz_list = str_replace("{$timezone}\"", "{$timezone}\" selected", $tz_list);
                                    } else {
                                        echo $tz_list;
                                    }
                                    echo $tz_list; ?>
                                </select>

                                <br>
                                <br>

                                <h3>2. What do you want to do, when the access is blocked.</h3>
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
                                <h3>3. Block Proxy ON / OFF </h3>
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
                                <h5>If you check ON, It takes a bit of a while load time. Please test.</h5>
                                <label class="radio-inline">
                                    <?php echo $tor == "ON" ? "<input type=\"radio\" name=\"tor\" value=\"ON\" checked>ON" : "<input type=\"radio\" name=\"tor\" value=\"ON\">ON"; ?>
                                </label>
                                <label class="radio-inline">
                                    <?php echo $tor == "OFF" ? "<input type=\"radio\" name=\"tor\" value=\"OFF\" checked>OFF" : "<input type=\"radio\" name=\"tor\" value=\"OFF\">OFF"; ?>
                                </label>
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>5. Exception IP</h3>
                                <h5>If you have many exception IPs,Please sprit with ","<br>
                                    You should add server's ip
                                    <global> for other plugins. ex)Broken Link Checker<br>
                                        Example: 1.1.1.1,2.2.2.2,3.3.3.3
                                </h5>
                                <input class="form-control" type="text" name="ip" value="<?php echo $ip ?>">
                            </div>

                            <br>

                            <div class="form-group">
                                <h3>6. Log fnction</h3>
                                <h5>If you check on, It takes a bit of a while load time. Please test.</h5>
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
                        <div class="panel panel-primary">
                            <div class="panel-heading">Information</div>
                            <div class="panel-body"><?php echo htmlspecialchars(toGetInfo()->msg); ?></div>
                            <div class="panel-footer">Last Updated
                                : <?php echo htmlspecialchars(toGetInfo()->date); ?></div>
                        </div>

                        <div class="panel panel-danger">
                            <div class="panel-heading">Reported Access</div>
                            <div class="panel-body">
                                <p>aaa</p>
                            </div>
                            <div class="panel-footer">Last Updated
                                : <?php echo htmlspecialchars(toGetInfo()->date); ?></div>
                        </div>

                        <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/icon-256x256.png' ?>"
                             class="img-rounded img-responsive">
                        <h3>block-wpscan</h3>
                        <p>This plugin block Tor, Proxy, Command Line access and wpscan. But it can't block all
                            unauthorized access.<br>
                            Tor access is blocked by Tornodelist. If Tor access is isn't registration of Tornodelist, It
                            can't block Tor access.<br>
                            About 80% can block.<br>
                            <br>
                            * Exception IPs.<br>
                            * Proxy, Tor block ON / OFF.<br>
                            * Edit message.<br>
                            * Log function.<br>
                            <br>
                            You should add server's global ip for other plugins. ex)Broken Link Checker.<br>
                            Googlebot and more can access own server.<br>
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
                <form action="" method="post">
                    <h3>Blocked list<span style="padding:20px;"><input class="small" id="tablesearchinput"
                                                                       placeholder="Search Here"></span><span><input
                                type="submit" class="btn btn-danger" name="delete" value="Delete"></span></h3>
                    <span class="text-info"><strong>Blocked:</strong></span><?php echo count(toGetLog()); ?>
                    <span
                        class="text-info"><strong>filesize:</strong></span><?php echo size_format(filesize(WP_CONTENT_DIR . '/block-wpscan/block.list'),
                        1) ?>
                    <span
                        class="text-info"><strong>Path:</strong></span><?php echo WP_CONTENT_DIR . '/block-wpscan/block.list' ?></span>
                </form>
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
    $options = array('http' => array('method' => 'POST'),);
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
 * ログファイルから連想配列化
 *
 * @return array ログファイルから多次元配列を返す
 */
function toCreateArray()
{
    $b = 1;

    if ($file = array_reverse(file(WP_CONTENT_DIR . '/block-wpscan/block.list'))) {
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
    $ip = trim($_SERVER['REMOTE_ADDR']);
    $host = trim(gethostbyaddr($_SERVER['REMOTE_ADDR']));
    $result = 1;

    /* IP + HOST - Tor */
    if (get_option('tor') == "ON") {
        $file = file_get_contents(plugin_dir_url(__FILE__) . 'tornodelist');
        if (strpos($file, $ip) !== false || strpos('tor', $host) !== false) {
            $tor_result = 0;
        }
    }

    /* Exception IP */
    $exception_result = $ip === $_SERVER['SERVER_ADDR'] ? 1 : 0;
    if (get_option('ip') || $exception_result === 0) {
        $exception_ip = explode(",", get_option('ip'));
        $exception_ip[] = "127.0.0.1"; // for reverse proxy

        foreach ($exception_ip as $row) {
            if ($row == $ip) {
                $exception_result = 1;
                break;
            }
        }
    }

    /* Browser's languages */
    if (filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_SPECIAL_CHARS)) {
        $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $languages = array_reverse($languages);

        foreach ($languages as $language) {
            if (preg_match('/^ja/i', $language) || preg_match('/^en/i', $language)) {
                $browser_result = 1;
            }
        }
    } else {
        $browser_result = 0;
    }

    /* Exception /feed & /rss access */
    $e = array("feed", "rss");
    foreach ($e as $row) {
        if (strpos(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_SPECIAL_CHARS), $row) !== false) {
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
        "microad.jp"
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

    if ($browser_result === 0 || $ua_result === 0 || @$proxy_result1 === 0 || @$proxy_result2 === 0 || @$tor_result === 0) {
        $result = 0;
    }

    if (@$bot_result === 1 || @$exception_result === 1 || $request_result === 1) {
        $result = 1;
    }

    /*
    echo "IP: $ip<br>HOST: $host<br>
    --------------------<br>
    Exception: $exception_result<br>
    --------------------<br>
    Browser: $browser_result<br>
    Bot: $bot_result<br>
    UA:$ua_result<br>
    Proxy1: $proxy_result1<br>
    Proxy2: $proxy_result2<br>
    Tor: $tor_result<br>";
    */

    if ($result === 0) {
        if (get_option('log') == "ON") {
            if ($browser_result === 0) {
                $a = "Not Browser Access";
            } elseif ($ua_result === 0) {
                $a = "Corrupt UserAgent";
            } elseif ($proxy_result1 === 0 || $proxy_result2 === 0) {
                $a = "Proxy Access";
            } elseif ($tor_result === 0) {
                $a = "Tor Access";
            }
            toSetLog($a, $ip, $host,
                filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS),
                filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_SPECIAL_CHARS), date("Y-m-d H:i"),
                "http://whois.domaintools.com/${ip}");
        }

        /* ブロック時の処理 */
        if (get_option('first') == "msg") {
            header("HTTP / 1.0 406 Not Acceptable");
            $c = file_get_contents(plugin_dir_url(__FILE__) . 'assets/index.php');
            die($c);
        } elseif (get_option('first') == "redirect") {
            header('Location: ' . get_option('redirect'));
            die();
        }
    }
}