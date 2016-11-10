jQuery(document).ready( function( $ ){

	$('#wp-mqtt-add-custom-event').click(function( e ){
		
		// don't actually go anywhere
		e.preventDefault();
		
		// find the highest current event number, so we can add ours after that
		var last_input_id = $('#wp-mqtt-custom-events-container').find('div.wp-mqtt-custom-event:last input:first').attr('id');
		if( last_input_id ){
			var id = parseInt( last_input_id.split('_')[2] ) + 1;
		} else {
			var id = 0;
		}

		// add the new row
		var html = '<div class="wp-mqtt-custom-event" id="wp-mqtt-custom_event-' + id + '">';
		html += '<input id="custom_event_' + id + '_checkbox" name="wp_mqtt_settings[custom_events][' + id + '][checkbox]" type="checkbox" value="true" />&nbsp;';
		html += '<input id="custom_event_' + id + '_hook" name="wp_mqtt_settings[custom_events][' + id + '][hook]" size="20" type="text" value="hook" />&nbsp;';
		html += '<input id="custom_event_' + id + '_subject" name="wp_mqtt_settings[custom_events][' + id + '][subject]" size="20" type="text" value="subject" />&nbsp;';
		html += '<input id="custom_event_' + id + '_message" name="wp_mqtt_settings[custom_events][' + id + '][message]" size="80" type="text" value="message" />&nbsp;';
		html += '<a href="#" class="wp-mqtt-delete-custom-event button"><span class="dashicons dashicons-no" style="line-height: inherit;" /></a>';
		html += '</div>';
		$('#wp-mqtt-custom-events-container').append( html );

	});


	$('#wp-mqtt-custom-events-container').on( 'click', '.wp-mqtt-delete-custom-event', function(e){
	
		// don't actually go anywhere
		event.preventDefault();
	
		// remove the parent element
		$(this).closest('.wp-mqtt-custom-event').remove();

	});

});