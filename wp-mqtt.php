<?php
/*
	Plugin Name: WP-MQTT
	Plugin URI:  http://www.roytanck.com
	Description: Send MQTT messages from WordPress
	Version:     1.0
	Author:      Roy Tanck
	Author URI:  http://www.roytanck.com
	Text Domain: wp-mqtt
	Domain path: /languages
	License:     GPL
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }

// require the phpMQTT library
require_once( 'lib/vendor/phpMQTT/phpMQTT.php' );

// require the setting page
require_once( 'inc/wp-mqtt-settings.php' );

// require the settings page contextual help
require_once( 'inc/wp-mqtt-help.php' );


if( !class_exists('WP_MQTT') ){

	class WP_MQTT {

		public $client_id = 'wp-mqtt';
		public $settings = null;
		public $mqtt = null;
		public $connected = false;

		/**
		 * Constructor
		 */
		function __construct() {

			// load the plugin's text domain			
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			// enque the admin js
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
			// shut down the broker connection when WordPress is about to finish
			add_action( 'shutdown', array( $this, 'close_connection' ) );

			// get the plugin's settings, and use them to connect to the server
			$settings = get_option('wp_mqtt_settings');
			if( $settings ){
				$this->settings = $settings;

				// set up the pageview event
				if( isset( $settings['event_pageview']['checkbox'] ) && $settings['event_pageview']['checkbox'] == 'true' ){
					add_action( 'template_redirect', array( $this, 'event_pageview' ) );
				}

				// set up the login event
				if( isset( $settings['event_login']['checkbox'] ) && $settings['event_login']['checkbox'] == 'true' ){
					add_action( 'wp_login', array( $this, 'event_login' ) );
				}

				// set up the failed login event
				if( isset( $settings['event_login_failed']['checkbox'] ) && $settings['event_login_failed']['checkbox'] == 'true' ){
					add_action( 'wp_login_failed', array( $this, 'event_login_failed' ) );
				}

				// set up the new post event
				if( isset( $settings['event_new_post']['checkbox'] ) && $settings['event_new_post']['checkbox'] == 'true' ){
					add_action( 'publish_post', array( $this, 'event_new_post' ), 10, 2 );
				}

				// set up the new page event
				if( isset( $settings['event_new_page']['checkbox'] ) && $settings['event_new_page']['checkbox'] == 'true' ){
					add_action( 'publish_page', array( $this, 'event_new_page' ), 10, 2 );
				}

				// set up the new comment event
				if( isset( $settings['event_new_comment']['checkbox'] ) && $settings['event_new_comment']['checkbox'] == 'true' ){
					add_action( 'wp_insert_comment', array( $this, 'event_new_comment' ), 10 ,2 );
				}

				// set up custom events, checking for valid settings
				if( $settings['custom_events_enable'] == true ){
					if( isset( $settings['custom_events'] ) && is_array( $settings['custom_events'] ) ){
						// loop through the custom events
						foreach( $settings['custom_events'] as $key => $event ){
							// if no hook, skip
							if( !empty( $event['hook'] ) ){
								//  use a closure to be able to read the settings
								add_action( $event['hook'], function( $arg ) use ( $key ) {
									//  get the event's settings from the array
									$custom_event = $this->settings['custom_events'][$key];
									// check if the event is active
									if( $custom_event['checkbox'] == true ){
										$subject = $custom_event['subject'];
										$message = $custom_event['message'];
										// publish the message
										$this->publish( $subject, $message );
									}
									// this could be a filter action, so return the first argument
									if( $arg ){
										return $arg;
									}
								}, 10 );
							}
						}
					}
				}

			}

		}


		/**
		 * Load the translated strings
		 */
		function load_textdomain(){
			load_plugin_textdomain( 'wp-mqtt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}


		public function enqueue_admin_js( $hook ){
			if ( 'settings_page_wp-mqtt-settings' != $hook ) {
				return;
			}
			wp_enqueue_script( 'wp-mqtt-admin-js', plugins_url( 'js/wp-mqtt-admin.js', __FILE__ ), array('jquery') );
		}


		/**
		 * Connect to the MQTT broker (if mor already connected)
		 */
		public function connect(){
			if( $this->mqtt == null ){
				$this->mqtt = new phpMQTT( $this->settings['broker_url'], $this->settings['broker_port'], $this->settings['broker_client_id'] );
				if( $this->mqtt->connect( true, null, $this->settings['broker_username'], $this->settings['broker_password'] ) ){
					$this->connected = true;
				}
			}
		}


		/**
		 * Close the connection just before WordPress shuts down
		 */
		public function close_connection(){
			if( $this->connected ){
				$this->mqtt->close();
			}
		}


		/**
		 * Send the MQTT message
		 */
		public function publish( $subject, $message ){
			// apply filters to the subject
			$subject = apply_filters( 'wp_mqtt_filter_subject', $subject, $message );
			// apply filters to the message
			$message = apply_filters( 'wp_mqtt_filter_message', $message, $subject );
			// attempt to connect to the broker
			$this->connect();
			// check if the connection was made
			if( $this->connected ){
				// publish the message
				$this->mqtt->publish( $subject, $message, $this->settings['broker_qos'] );
			}			
		}


		public function event_pageview(){
			$subject = isset( $this->settings['event_pageview']['subject'] ) ? $this->settings['event_pageview']['subject'] : 'default';
			$message = isset( $this->settings['event_pageview']['message'] ) ? $this->settings['event_pageview']['message'] : 'default';
			$this->publish( $subject, $message );
		}

		public function event_login(){
			$subject = isset( $this->settings['event_login']['subject'] ) ? $this->settings['event_login']['subject'] : 'default';
			$message = isset( $this->settings['event_login']['message'] ) ? $this->settings['event_login']['message'] : 'default';
			$this->publish( $subject, $message );
		}

		public function event_login_failed(){
			$subject = isset( $this->settings['event_login_failed']['subject'] ) ? $this->settings['event_login_failed']['subject'] : 'default';
			$message = isset( $this->settings['event_login_failed']['message'] ) ? $this->settings['event_login_failed']['message'] : 'default';
			$this->publish( $subject, $message );
		}

		public function event_new_post( $id, $post ){
			$subject = isset( $this->settings['event_new_post']['subject'] ) ? $this->settings['event_new_post']['subject'] : 'default';
			$message = isset( $this->settings['event_new_post']['message'] ) ? $this->settings['event_new_post']['message'] : 'default';
			// replace some placeholders with actual content
			$message = $this->replace_post_placeholders( $message, $post );
			$this->publish( $subject, $message );
		}

		public function event_new_page( $id, $post ){
			$subject = isset( $this->settings['event_new_page']['subject'] ) ? $this->settings['event_new_page']['subject'] : 'default';
			$message = isset( $this->settings['event_new_page']['message'] ) ? $this->settings['event_new_page']['message'] : 'default';
			// replace some placeholders with actual content
			$message = $this->replace_post_placeholders( $message, $post );
			$this->publish( $subject, $message );
		}

		public function event_new_comment( $id, $comment ){
			$subject = isset( $this->settings['event_new_comment']['subject'] ) ? $this->settings['event_new_comment']['subject'] : 'default';
			$message = isset( $this->settings['event_new_comment']['message'] ) ? $this->settings['event_new_comment']['message'] : 'default';
			// replace some placeholders with actual content
			$message = $this->replace_comment_placeholders( $message, $comment );
			$this->publish( $subject, $message );
		}


		public function replace_post_placeholders( $message, $post ){
			$search = array(
				'%POST_AUTHOR%',
				'%POST_TITLE%',
				'%POST_EXCERPT%',
				'%POST_CONTENT%',
				'%POST_TYPE%'
			);
			$replace = array(
				get_the_author_meta( 'display_name', $post->post_author ),
				$post->post_title,
				get_the_excerpt( $post->ID ),
				$post->post_content,
				$post->post_type,
			);
			$message = str_replace( $search, $replace, $message );
			return $message;
		}


		public function replace_comment_placeholders( $message, $comment ){
			$search = array(
				'%COMMENT_AUTHOR%',
				'%COMMENT_CONTENT%',
			);
			$replace = array(
				$comment->comment_author,
				$comment->comment_content,

			);
			$message = str_replace( $search, $replace, $message );
			return $message;
		}

	}

}


$wp_mqtt_instance = new WP_MQTT();