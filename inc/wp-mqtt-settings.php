<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('WP_MQTT_Settings') ){

	class WP_MQTT_Settings {

		private $current_settings;
		private $fields;

		/**
		 * Start up
		 */
		public function __construct(){
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
		}


		/**
		 * Add options page
		 */
		public function add_plugin_page(){
			// create the new options page
			add_options_page(
				__( 'WP-MQTT Settings', 'wp-mqtt' ),
				__( 'WP-MQTT', 'wp-mqtt' ), 
				'manage_options', 
				'wp-mqtt-settings', 
				array( $this, 'create_admin_page' )
			);
		}

		/**
		 * Options page callback
		 */
		public function create_admin_page(){
			// get the current settings for use in the callback functions that render the fields
			$this->current_settings = get_option('wp_mqtt_settings');
			// start output
			echo '<div class="wrap">';
			echo '<h1>' . __( 'WP-MQTT settings', 'wp-mqtt') . '</h1>';
			// render the form
			echo '<form method="post" action="options.php">';
			settings_fields( 'wp_mqtt_option_group' );   
			do_settings_sections( 'wp-mqtt-settings' );
			submit_button();
			echo '</form>';
			//var_dump( $this->current_settings );
			// wrap up
			echo '</div>';
		}

		/**
		 * Register and add settings
		 */
		public function page_init(){

			register_setting(
				'wp_mqtt_option_group', // Option group
				'wp_mqtt_settings', // Option name
				array( $this, 'sanitize' ) // Sanitize
			);

			add_settings_section(
				'wp_mqtt_broker_settings', // ID
				__( 'Broker settings', 'wp-mqtt' ), // Title
				array( $this, 'print_broker_section_info' ), // Callback
				'wp-mqtt-settings' // Page
			);

			add_settings_section(
				'wp_mqtt_event_settings', // ID
				__( 'Common events', 'wp-mqtt' ), // Title
				array( $this, 'print_event_section_info' ), // Callback
				'wp-mqtt-settings' // Page
			);

			add_settings_section(
				'wp_mqtt_custom_events', // ID
				__( 'Custom events', 'wp-mqtt' ), // Title
				array( $this, 'print_custom_events_section_info' ), // Callback
				'wp-mqtt-settings' // Page
			);

			$this->fields = array(
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_url', 'label'=>__('Broker URL', 'wp-mqtt' ), 'callback'=>'broker_url_callback' ),
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_port', 'label'=>__('Broker Port', 'wp-mqtt' ), 'callback'=>'broker_port_callback' ),
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_qos', 'label'=>__('QoS', 'wp-mqtt' ), 'callback'=>'broker_qos_callback' ),
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_client_id', 'label'=>__('Client ID', 'wp-mqtt' ), 'callback'=>'broker_client_id_callback' ),
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_username', 'label'=>__('Username', 'wp-mqtt' ), 'callback'=>'broker_username_callback' ),
				array( 'section'=>'wp_mqtt_broker_settings', 'id'=>'broker_password', 'label'=>__('Password', 'wp-mqtt' ), 'callback'=>'broker_password_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_pageview', 'label'=>__('Pageview', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_login', 'label'=>__('User login', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_login_failed', 'label'=>__('Failed user login', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_new_post', 'label'=>__('Post published', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_new_page', 'label'=>__('Page published', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_event_settings', 'id'=>'event_new_comment', 'label'=>__('New comment', 'wp-mqtt' ), 'callback'=>'event_callback' ),
				array( 'section'=>'wp_mqtt_custom_events', 'id'=>'custom_events_enable', 'label'=>__('Enable custom events', 'wp-mqtt' ), 'callback'=>'custom_events_enable_callback' ),
				array( 'section'=>'wp_mqtt_custom_events', 'id'=>'custom_events', 'label'=>__('Custom events', 'wp-mqtt' ), 'callback'=>'custom_events_callback' ),
			);

			foreach( $this->fields as $field ){
				add_settings_field(
					$field['id'], // id
					$field['label'], //title
					array( $this, $field['callback'] ), // callback
					'wp-mqtt-settings', // page
					$field['section'], // section
					array( 'event_id' => $field['id'] ) // pass the id for use in the callback function
				);		
			}

		}


		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys
		 */
		public function sanitize( $input )
		{
			$new_input = array();

			if( isset( $input['broker_url'] ) ){
				$sanitized_url = esc_url_raw( $input['broker_url'] );
				$new_input['broker_url'] = preg_replace('#^https?://#', '', rtrim( $sanitized_url, '/' ) );
			}

			if( isset( $input['broker_port'] ) ){
				$new_input['broker_port'] = max( min( intval( $input['broker_port'] ), 65535 ), 0 );
			}

			if( isset( $input['broker_qos'] ) ){
				$new_input['broker_qos'] = max( min( intval( $input['broker_qos'] ), 2 ), 0 );
			}

			if( isset( $input['broker_client_id'] ) ){
				$new_input['broker_client_id'] = sanitize_text_field( $input['broker_client_id'] );
			}

			if( isset( $input['broker_username'] ) ){
				$new_input['broker_username'] = sanitize_text_field( $input['broker_username'] );
			}

			if( isset( $input['broker_password'] ) ){
				$new_input['broker_password'] = sanitize_text_field( $input['broker_password'] );
			}

			// use the fields array to loop through all events
			foreach( $this->fields as $field ){
				if( $field['section'] == 'wp_mqtt_event_settings' ){
					$new_input[ $field['id'] ] = $this->sanitize_event_fields( $input[ $field['id'] ] );
				}
			}

			$new_input['custom_events_enable'] = ( isset( $input['custom_events_enable'] ) && $input['custom_events_enable'] == 'true' );

			// sanitize the custom events array
			if( isset( $input['custom_events'] ) && is_array( $input['custom_events'] ) ){
				$events_clean = array();
				foreach( $input['custom_events'] as $event ){
					$events_clean[] = $this->sanitize_event_fields( $event, true );
				}
				$new_input['custom_events'] = $events_clean;
			}

			return $new_input;
		}

		/**
		 * Sanitize the fields associated with a event
		 */
		public function sanitize_event_fields( $event, $is_custom = false ){
			$new = array();
			$new['checkbox'] = ( isset( $event['checkbox'] ) && $event['checkbox'] == 'true' ) ? true : false;
			if( $is_custom ){
				$new['hook'] = empty( $event['hook'] ) ? __( 'hook', 'wp-mqtt' ) : trim( sanitize_text_field( $event['hook'] ) );
			}
			$new['subject'] = empty( $event['subject'] ) ? __( 'subject', 'wp-mqtt' ) : trim( sanitize_text_field( $event['subject'] ) );
			$new['message'] = empty( $event['message'] ) ? __( 'message', 'wp-mqtt' ) : trim( sanitize_text_field( $event['message'] ) );
			return $new;
		}

		/** 
		 * Print the Section text
		 */
		public function print_broker_section_info(){
			print __( 'Please provide information about the MQTT broker you want to connect to:', 'wp-mqtt' );
		}

		/** 
		 * Print the Section text
		 */
		public function print_event_section_info(){
			print __( 'Select common WordPress events that should trigger MQTT messages', 'wp-mqtt' );
		}

		/** 
		 * Print the Section text
		 */
		public function print_custom_events_section_info(){
			print __( "Trigger MQTT message using WordPress hooks. Please don't enable this unless you're familiar with WordPress's filter/action hook system.", 'wp-mqtt' );
		}

		/** 
		 * Callback for the broker URL
		 */
		function broker_url_callback() { 
			$current_setting = isset( $this->current_settings['broker_url'] ) ? $this->current_settings['broker_url'] : 'mqtt.example.com';
			echo '<input id="broker_url" name="wp_mqtt_settings[broker_url]" size="60" type="text" value="' . $current_setting . '" />';
		}

		/** 
		 * Callback for the broker port
		 */
		function broker_port_callback() { 
			$current_setting = isset( $this->current_settings['broker_port'] ) ? $this->current_settings['broker_port'] : 1883;
			echo '<input id="broker_port" name="wp_mqtt_settings[broker_port]" size="6" type="text" value="' . $current_setting . '" />';
		}

		/** 
		 * Callback for the broker QoS setting
		 */
		function broker_qos_callback() { 
			$current_setting = isset( $this->current_settings['broker_qos'] ) ? $this->current_settings['broker_qos'] : 0;
			$qos_options = array(
				array( 'value' => 0, 'label' => '0 - At most once' ),
				array( 'value' => 1, 'label' => '1 - At least once' ),
				array( 'value' => 2, 'label' => '2 - Exactly once' ),
			);
			echo '<select id="broker_qos" name="wp_mqtt_settings[broker_qos]" >';
			foreach( $qos_options as $qos ){
				echo '<option value="' . $qos['value'] . '" ' . selected( $qos['value'], $current_setting, false ) . '>' . $qos['label'] . '</option>';
			}
			echo '</select>';
		}

		/** 
		 * Callback for the client ID
		 */
		function broker_client_id_callback() {
			$current_setting = ( isset( $this->current_settings['broker_client_id'] ) && !empty( $this->current_settings['broker_client_id'] ) ) ? $this->current_settings['broker_client_id'] : $this->generate_default_client_id();
			echo '<input id="broker_client_id" name="wp_mqtt_settings[broker_client_id]" size="60" type="text" value="' . $current_setting . '" />';
		}

		/** 
		 * Callback for the username
		 */
		function broker_username_callback() {
			$current_setting = isset( $this->current_settings['broker_username'] ) ? $this->current_settings['broker_username'] : '';
			echo '<input id="broker_username" name="wp_mqtt_settings[broker_username]" size="30" type="text" value="' . $current_setting . '" />';
		}

		/** 
		 * Callback for the password
		 */
		function broker_password_callback() {
			$current_setting = isset( $this->current_settings['broker_password'] ) ? $this->current_settings['broker_password'] : '';
			echo '<input id="broker_password" name="wp_mqtt_settings[broker_password]" size="30" type="password" value="' . $current_setting . '" />';
		}

		/** 
		 * Callback for the event settings
		 */
		function event_callback( $args ){
			if( is_array( $args ) && !empty( $args['event_id'] ) ){
				$this->render_event_fields( $args['event_id'] );
			}
		}

		/**
		 * Render an event settings sub-form
		 */
		function render_event_fields( $event_name ){
			$event = isset( $this->current_settings[ $event_name ] ) ? $this->current_settings[ $event_name ] : array( 'checkbox'=>false, 'subject'=>__( 'subject', 'wp-mqtt' ), 'message'=>__( 'message', 'wp-mqtt' ) );
			echo '<input id="' . $event_name . '_checkbox" name="wp_mqtt_settings[' . $event_name . '][checkbox]" type="checkbox" value="true" ' . checked( $event['checkbox'], true, false ) . ' />&nbsp;';
			echo '<input id="' . $event_name . '_subject" name="wp_mqtt_settings[' . $event_name . '][subject]" size="30" type="text" value="' . $event['subject'] . '" />&nbsp;';
			echo '<input id="' . $event_name . '_message" name="wp_mqtt_settings[' . $event_name . '][message]" size="90" type="text" value="' . $event['message'] . '" />';
		}


		/** 
		 * Callback the 'enable custom events' checkbox
		 */
		function custom_events_enable_callback() { 
			$current_setting = ( isset( $this->current_settings['custom_events_enable'] ) && $this->current_settings['custom_events_enable'] == true );
			echo '<input id="custom_events_enable" name="wp_mqtt_settings[custom_events_enable]" type="checkbox" value="true" ' . checked( $current_setting, true, false ) . ' />';
			echo '<label for="custom_events_enable">' . __( "I know what I'm doing, enable custom events", 'wp-mqtt' ) . '</label>';
		}

		/** 
		 * Callback for custom event settings
		 */
		function custom_events_callback( $args ){
			echo '<div id="wp-mqtt-custom-events-container">';
			if( isset( $this->current_settings['custom_events'] ) && is_array( $this->current_settings['custom_events'] ) ){
				foreach( $this->current_settings['custom_events'] as $key => $event ){
					$i = intval( $key );
					echo '<div class="wp-mqtt-custom-event" id="wp-mqtt-custom_event-' . $i . '">';
					echo '<input id="custom_event_' . $i . '_checkbox" name="wp_mqtt_settings[custom_events][' . $i . '][checkbox]" type="checkbox" value="true" ' . checked( $event['checkbox'], true, false ) . ' />&nbsp;';
					echo '<input id="custom_event_' . $i . '_hook" name="wp_mqtt_settings[custom_events][' . $i . '][hook]" size="20" type="text" value="' . $event['hook'] . '" />&nbsp;';
					echo '<input id="custom_event_' . $i . '_subject" name="wp_mqtt_settings[custom_events][' . $i . '][subject]" size="20" type="text" value="' . $event['subject'] . '" />&nbsp;';
					echo '<input id="custom_event_' . $i . '_message" name="wp_mqtt_settings[custom_events][' . $i . '][message]" size="80" type="text" value="' . $event['message'] . '" />&nbsp;';
					echo '<a href="#" class="wp-mqtt-delete-custom-event button"><span class="dashicons dashicons-no" style="line-height: inherit;"></span></a>';
					echo '</div>';
				}
			}
			echo '</div>';
			echo '<p><a href="#"" class="button" id="wp-mqtt-add-custom-event">' . __('Add new custom event', 'wp-mqtt') . '</a></p>';
		}

		/**
		 * Generate a default client ID based on the site's URL
		 */
		function generate_default_client_id(){
			$home_url = preg_replace('#^https?://#', '', get_home_url() );
			return 'wp-mqtt-' . str_replace( array('/','.','?','&'), '-', trim( $home_url, '/' ) );
		}

	}
}

// create an instance
if( is_admin() ){
	$wp_mqtt_settings_instance = new WP_MQTT_Settings();
}

?>