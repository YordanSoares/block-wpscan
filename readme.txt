=== block-wpscan ===
Contributors: rluisr
Donate link: https://luispc.com/
Tags: wpscan, proxy, tor, block, user, enumerate
Requires at least: 4.0.0
Tested up to: 4.5.2
Stable tag: 0.5.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin can block "Tor access", "Proxy Access", "Commandline Access" and more.
When it blocks unauthorized access.

== Description ==

This plugin can block "Tor access", "Proxy Access", "Commandline Access" and more.
When it blocks unauthorized access, you can choose setting "Show message" or "Redirect".

Googlebot,Twitterbot and other crawler can access own wordpress.

= list of exception. =
"google","msn","yahoo","bing","hatena",
"data-hotel","twttr.com","eset.com","linkedin.com",
"ahrefs.com","webmeup.com","grapeshot.co.uk","blogmura.com",
"apple.com","microad.jp","linode.com","shadowserver.org","ezweb.ne.jp"

You can add exceptions IP or UserAgent.
See more "Secreenshot".

== Installation ==

1. Upload the folder `block-wpscan` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings slide menu 'block-wpscan' page.
4. Please setting and save.

== Screenshots ==

1. block wpscan
2. block tor
3. setting menu

== Changelog ==

= 0.5.3 =
* Tested up Wordpress 4.5.2
* Modify Code.
* Update Tornodelist
* Remove Information Panel

= 0.5.2 =
* Modify Exception IP.

= 0.5.1 =
* Add Report function.
* Add Set Timezone function.
* Add Exception UserAgent function.
* Add Exception Host. see more Description.
* Update TorNodeList.

= 0.4.5 =
* Tested up Wordpress 4.5
* Update Tornodelist

= 0.4.4 =
* Change Abolish API for Tor.
* Optimization the code.

= 0.4.3 =
* Add Exception access to /rss, /feed.

= 0.4.2 =
* Add Exception access from twitter-bot.
* Add Judgement result on Log function.

= 0.4.1 =
* Add Search function on Log function.
* Add Request_url on Log function.
* Add Link whois(http://www.domaintools.com/) on Log function.
* Add Exception livedoor(crawler) access.
* Modify Display file size on Log function.

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