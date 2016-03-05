=== block-wpscan ===
Contributors: rluisr
Donate link: https://luispc.com/
Tags: wpscan, proxy, tor, block
Requires at least: 4.0.0
Tested up to: 4.4.2
Stable tag: 0.0.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Block wpscan, proxy and tor access.

== Description ==

Block wpscan, proxy and tor access.
This plugin calls c.xyz.pw to detect if a user is on a TOR.
https://c.xzy.pw/judgementAPI-for-Tor/index.html

== Installation ==

1. Upload the folder `block-wpscan` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings slide menu 'block-wpscan' page.
4. Please setting and save.

== Screenshots ==

1. block wpscan
2. block tor

== Changelog ==

= 0.0.5 =
* Fix code.

= 0.0.4 =
* Fix Access from 127.0.0.1 is judged "BLOCK" by this plugin.
* Fix Exception IP when it has plural IPs.

= 0.0.3 =
* Fix if API Server is down, it can normal operation.

= 0.0.2 =
* Add exception function with ip.
* Change criterion for Googlebot.

= 0.0.1 =
* First version.