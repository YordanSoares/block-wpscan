=== block-wpscan ===
Contributors: rluisr
Donate link: https://luispc.com/
Tags: wpscan, proxy, tor, block
Requires at least: 4.0.0
Tested up to: 4.4.2
Stable tag: 0.3.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Block wpscan, proxy and tor access.

== Description ==

Block wpscan, proxy and tor access.
This plugin calls c.xyz.pw to detect if a user is on a Tor →　https://c.xzy.pw/judgementAPI-for-Tor/index.html
And https://maxcdn.bootstrapcdn.com/bootstrap for UI.

== Installation ==

1. Upload the folder `block-wpscan` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings slide menu 'block-wpscan' page.
4. Please setting and save.

== Screenshots ==

1. block wpscan
2. block tor

== Changelog ==

= 0.3.3 =
* Add Search function on Log function.

= 0.3.2 =
* Fix When you check ON log function, Don't reflect it.

= 0.3.1 =
* Add User-Agent on Log function.
* Add HTML function.
* Add Redirect function.
* Add Delete log list function.
* Modify descending sort on Log list.

= 0.2.5 =
* Add Exception access from msn, yahoo, bing, hatena

= 0.2.4 =
* Correspond less than PHP5.3

= 0.2.3 =
* Require PHP5.4 ~

= 0.2.2 =
* Fix css of admin page is overwrited by this plugin.

= 0.2.1 =
* Change UI.
* Add logging function.
* Add Information function from my server.
* Fix code of Googlebots.

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