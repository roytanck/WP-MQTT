=== WP-MQTT ===
Contributors: roytanck
Tags: MQTT, IoT, internet-of-things
Requires at least: 4.4
Tested up to: 4.7
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP-MQTT connects WordPress to the Internet of Things. Use this plugin to automatically send MQTT messages when something happens on your website.

== Description ==

Setting up WP-MQTT is easy. Simply supply your MQTT broker's details and configure which WordPress events should trigger messages.

A number of events are predefined. Simply check the right checkbox and fill in the message's subject and text.

* Pageview
* User login
* Failed user login
* Post published
* Page published
* New comment

Other events can be added using the "custom events" section. This allows you to use any WordPress hook (actions and filters) to trigger messages.

= Credits =
* WP-MQTT uses [phpMQTT]:http://github.com/bluerhinos/phpMQTT by Andrew Milsted.
* Thanks to [Peter Martin]:https://twitter.com/pe7er for his inspirational talk at WordCamp NL 2016.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-mqtt` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->WP-MQTT screen to set up your broker and configure messages

== Frequently Asked Questions ==

= Can I use this plugin to subscribe to MQTT subjects? =

I'm sorry, but no. This plugin's purpose is to send messages to a broker, not the other way around. Subscribing to an
MQTT subject would require WordPress to remain active continuously, instead of running only when pages are being served.

= Will this slow down my site? =

WP-MQTT does it's very best to only connect to the broker when needed, so it depends on the messages you configure.
Sending messages on events that occur very often may slow down your site. But MQTT itself was designed to have a very
low performance impact.

= My site is acting funny, and I think this plugin has something to do with it =
Please disable custom events and see if that makes a difference. If it does not, please deactivate the plugin to see if
that helps. If any of these steps fixes the issue, please post your findings on the wp.org support forums. I'll try to
look into them.

== Screenshots ==

1. PW-MQTT's settings screen contains the broker settings, allows you to set up common events, or specify your own.

== Changelog ==

= 0.9 =
Initial version.
