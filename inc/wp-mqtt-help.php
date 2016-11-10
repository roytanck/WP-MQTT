<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('WP_MQTT_Help') ){

	class WP_MQTT_Help {

		/**
		 * Start up
		 */
		public function __construct(){
			add_action( 'load-settings_page_wp-mqtt-settings', array( $this, 'my_plugin_add_help' ) );
		}


		function my_plugin_add_help() {

			$screen = get_current_screen();
						
			$broker = '<h3>' . __( 'Setting up the broker', 'wp-mqtt' ) . '</h3>';
			$broker .= '<p>' . __( 'An MQTT broker is a server that distrubutes MQTT messages to subscribed devices.', 'wp-mqtt' ) . ' ';
			$broker .= __( "In order for WP-MQTT to connect to a broker, it needs the following information.", 'wp-mqtt' ) . '</p>';
			$broker .= '<ul>';
			$broker .= '<li>' . __( 'URL (for example: "iot.somedomain.com")', 'wp-mqtt' ) . '</li>';
			$broker .= '<li>' . __( 'Port number (defaults to 1883, or 8883 for connections using TLS)', 'wp-mqtt' ) . '</li>';
			$broker .= '<li>' . __( 'QoS ("Quality of Service", defaults to 0)', 'wp-mqtt' ) . '</li>';
			$broker .= '<li>' . __( 'Client ID (must be unique for each device that connects to a broker)', 'wp-mqtt' ) . '</li>';
			$broker .= '<li>' . __( 'Username (optional, only needed if required by the broker)', 'wp-mqtt' ) . '</li>';
			$broker .= '<li>' . __( 'Password (optional, only needed if required by the broker)', 'wp-mqtt' ) . '</li>';
			$broker .= '</ul>';

			$screen->add_help_tab( array( 'id' => 'mqtt-broker-help', 'title' => __( 'Broker settings', 'wp-mqtt' ), 'content' => $broker ));
			
			$events = '<h3>' . __( 'Common events', 'wp-mqtt' ) . '</h3>';
			$events .= '<p>' . __( 'This section allows you to set up messages for common WordPress events. Simply activate them using the checkbox and supply a subject and message.', 'wp-mqtt' ) . '</p>';
			$events .= '<ul>';
			$events .= '<li>' . __( 'Pageview (fires on each page view on the front end of your site)', 'wp-mqtt' ) . '</li>';
			$events .= '<li>' . __( 'User login (fires when a user logs in successfully)', 'wp-mqtt' ) . '</li>';
			$events .= '<li>' . __( 'Failed user login (fires on failed login attempts)', 'wp-mqtt' ) . '</li>';
			$events .= '<li>' . __( 'Post published (fires when a new post is published)', 'wp-mqtt' ) . '</li>';
			$events .= '<li>' . __( 'Page published (fires when a new page is published)', 'wp-mqtt' ) . '</li>';
			$events .= '<li>' . __( 'New comment (fires when a new comment is submitted)', 'wp-mqtt' ) . '</li>';
			$events .= '</ul>';

			$screen->add_help_tab( array( 'id' => 'mqtt-events-help', 'title' => __( 'Common events', 'wp-mqtt' ), 'content' => $events ));

			$placeholders = '<h3>' . __( 'Content placeholders', 'wp-mqtt' ) . '</h3>';
			$placeholders .= '<p>' . __( 'Some common events support placeholders, that will be replace with actual content in the sent messages.', 'wp-mqtt' ) . '</p>';
			$placeholders .= '<h4>' . __( 'Content placeholders for "Post published" and "Page published"', 'wp-mqtt' ) . '</h4>';
			$placeholders .= '<ul>';
			$placeholders .= '<li>' . __( "%POST_AUTHOR% (the post author's display name)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '<li>' . __( "%POST_TITLE% (the post's title)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '<li>' . __( "%POST_EXCERPT% (the post excerpt, if available)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '<li>' . __( "%POST_CONTENT% (full post content, could be rather long and usually contains HTML markup)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '<li>' . __( "%POST_TYPE% (the post's type)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '</ul>';
			$placeholders .= '<h4>' . __( 'Content placeholders for "New Comment"', 'wp-mqtt' ) . '</h4>';
			$placeholders .= '<ul>';
			$placeholders .= '<li>' . __( "%COMMENT_AUTHOR% (the comment author's display name)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '<li>' . __( "%COMMENT_CONTENT% (the comment's content)", 'wp-mqtt' ) . '</li>';
			$placeholders .= '</ul>';
			$placeholders .= '<p>' . __( 'Need other placeholder? Please feel free to contact me.', 'wp-mqtt' ) . '</p>';

			$screen->add_help_tab( array( 'id' => 'mqtt-placeholders-help', 'title' => __( 'Content placeholders', 'wp-mqtt' ), 'content' => $placeholders ));

			$about = '<h3>' . __( 'About WP-MQTT</h3>', 'wp-mqtt' ) . '</h3>';
			$about .= '<p>' . __( 'Connect WordPress to the Internet of Things. WP-MQTT allows you to automatically send MQTT messages when something happens on your WordPress website.', 'wp-mqtt' ) . '</p>';
			$about .= '<p>' . __( 'WP-MQTT was created by Roy Tanck and released under the GPL license.', 'wp-mqtt' ) . '</p>';

			$screen->add_help_tab( array( 'id' => 'mqtt-about', 'title' => __( 'About WP-MQTT', 'wp-mqtt' ), 'content'  => $about ));

			// Help sidebars are optional
			$screen->set_help_sidebar(
				'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
				'<p><a href="http://wordpress.org/support/" target="_blank">' . __( 'Support Forums', 'wp-mqtt' ) . '</a><br />' .
				'<a href="http://mqtt.org" target="_blank">' . __( 'MQTT.org', 'wp-mqtt' ) . '</a><br />' .
				'<a href="http://mqtt.org/faq" target="_blank">' . __( 'MQTT.org FAQ', 'wp-mqtt' ) . '</a></p>'
			);
		}

	}
}

// create an instance
if( is_admin() ){
	$wp_mqtt_help_instance = new WP_MQTT_Help();
}

?>