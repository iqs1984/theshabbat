/**/
jQuery( window ).on(
	'load',
	function( $ )
    {
		var $ = jQuery,
			form_list = cpcff_analytics_settings || {},
			checkEvent = function( event_name, form_settings )
			{
				return (
					typeof form_settings[ 'events' ] != 'undefined' &&
					typeof form_settings[ 'events' ][ event_name ] != 'undefined' &&
					form_settings[ 'events' ][ event_name ]
				);
			};

		window[ 'cpcff_analytics_submit_event' ] = function( form_element, form_id )
			{
				form_obj = $( form_element );
				if( typeof form_obj.data( 'sent_analytics_submit' ) != 'undefined' )
				{
					form_obj.removeData( 'sent_analytics_submit' );
					return true;
				}

				if('ga' in window)
					ga( form_list[ form_id ][ 'tracker' ]+'.send', 'event', 'form', 'submit', 'Form '+form_id+' - Submit Form', { 'hitCallback': function() { form_obj.data( 'sent_analytics_submit', 1 ).submit(); } } );
				if('gtag' in window)
					gtag( 'event', 'submit', { 'event_category' : 'form', 'event_label' : 'Form '+form_id+' - Submit Form', 'event_callback': function() { form_obj.data( 'sent_analytics_submit', 1 ).submit(); } } );
				return false;
			};

		// Captcha failed exception
		$( document ).ajaxComplete(
			function( evt, jqXHR, ajaxOptions )
			{
				if( jqXHR.readyState == 4 && jqXHR.responseText == 'captchafailed' )
				{
					var _matchs = ajaxOptions.url.match( /ps=(_\d*)/ ),
						form_seq, form_id_obj, form_id;

					if( _matchs )
					{
						form_seq = $( '[name="cp_calculatedfieldsf_pform_psequence"][value="'+_matchs[1]+'"]' );

						if( form_seq.length )
						{
							form_id_obj = $( '[name="cp_calculatedfieldsf_pform_psequence"][value="'+_matchs[1]+'"]' )
										.siblings( '[name="cp_calculatedfieldsf_id"]' );

							if( form_id_obj.length )
							{
								form_id = form_id_obj.val();

								// Check if the form is associated to Google Analytics, and if the CAPTCHA Exception is enabled.
								if(
									typeof form_list[ form_id ] == 'undefined' ||
									typeof form_list[ form_id ][ 'exceptions' ] == 'undefined' ||
									typeof form_list[ form_id ][ 'exceptions' ][ 'captcha' ] == 'undefined' ||
									!form_list[ form_id ][ 'exceptions' ][ 'captcha' ]
								) return;

								if('ga' in window)
									ga( form_list[ form_id ][ 'tracker' ]+'.send', 'exception', { 'exDescription': 'Form '+form_id+' - Invalid CAPTCHA Code', 'exFatal': false } );
								if('gtag' in window)
									gtag( 'exception', { 'description': 'Form '+form_id+' - Invalid CAPTCHA Code', 'fatal': false } );
							}
						}
					}
				}
			}
		);

		for( var form_id in form_list )
		{
			if(
				typeof form_list[ form_id ][ 'property' ] == 'undefined' ||
				/^\s*$/.test( form_list[ form_id ][ 'property' ] )
			) continue;

			form_list[ form_id ][ 'tracker' ] = 'form_' + form_id;
			$( '[name="cp_calculatedfieldsf_id"][value="'+form_id+'"]').each(
				function()
				{
					var form_id = $( this ).val(),
						form_obj = $( this ).closest( 'form' ),
						form_settings = form_list[ form_id ];

					// Page BK
					form_settings[ 'page_bk' ]  = 0;

					// Create tracker
					if('ga' in window) ga( 'create', $.trim( form_settings[ 'property' ] ), 'auto', form_settings[ 'tracker' ] );

					// Load form event
					if( checkEvent( 'load', form_settings ) )
					{
						if('ga' in window)
							ga( form_settings[ 'tracker' ]+'.send', 'event', 'form', 'load', 'Loaded Form '+form_id );
						if('gtag' in window)
							gtag( 'event', 'load', { 'event_category' : 'form', 'event_label' : 'Loaded Form '+form_id } );
					}

					// Next page event
                    form_obj.find( '.pbNext' ).click(
						function()
						{
                            setTimeout(function(){
                                var current_page = form_obj.find( '.pbreak:visible' ).attr( 'page' );
                                if( checkEvent( 'next_page', form_settings ) )
                                {
                                    if( form_settings[ 'page_bk' ] < current_page )
                                    {
										if('ga' in window)
											ga( form_settings[ 'tracker' ]+'.send', 'event', 'form', 'next page', 'Form '+form_id+' - Page '+current_page );
										if('gtag' in window)
											gtag( 'event', 'next page', { 'event_category' : 'form', 'event_label' : 'Form '+form_id+' - Page '+current_page } );
                                    }
                                }
                                form_settings[ 'page_bk' ] = current_page;
                            }, 400);
						}
					);

					// Previous page event
					form_obj.find( '.pbPrevious' ).click(
						function()
						{
                            setTimeout(function(){
                                var current_page = form_obj.find( '.pbreak:visible' ).attr( 'page' );
                                if( checkEvent( 'previous_page', form_settings ) )
                                {
                                    if( current_page < form_settings[ 'page_bk' ] )
                                    {
										if('ga' in window)
											ga( form_settings[ 'tracker' ]+'.send', 'event', 'form', 'previous page', 'Form '+form_id+' - Page '+current_page );
										if('gtag' in window)
											gtag( 'event', 'previous page', { 'event_category' : 'form', 'event_label' : 'Form '+form_id+' - Page '+current_page } );
                                    }
                                }
                                form_settings[ 'page_bk' ] = current_page;
                            }, 400);
						}
					);

					// Submit form event --> Its call is included as an action from the server side script

					// Focus fields events
					if(
						typeof form_settings[ 'fields' ] != 'undefined' &&
						!$.isEmptyObject( form_settings[ 'fields' ] )
					)
					{
						var	fields	 = form_settings[ 'fields' ],
							form_seq = form_obj.find( '[name="cp_calculatedfieldsf_pform_psequence"]' ).val();

						for( var field_id in fields )
						{
							var field_settings = fields[ field_id ],
								howManyTimes = ( typeof field_settings[ 'only_one' ] != 'undefined' && field_settings[ 'only_one' ] ) ? 'one' : 'on';

							$( document )[ howManyTimes ](
								'focus',
								'[name*="'+field_id+form_seq+'"]',
								(function( field_id, field_settings )
								{
									return function()
									{
										var field_obj = $( this ),
											label 	  = 'Focused Field ' + field_id;

										if(
											typeof field_settings[ 'label' ] != 'undefined' &&
											field_settings[ 'label' ]
										)
										{
											if( field_obj.closest( '.fields' ).children( 'label:first' ).length )
											{
												label += ' - '+field_obj.closest( '.fields' ).children( 'label:first' ).text();
											}
										}

										if('ga' in window)
											ga( form_settings[ 'tracker' ]+'.send', 'event', 'form', 'focus', 'Form '+form_id+' - '+label );
										if('gtag' in window)
											gtag( 'event', 'focus', { 'event_category' : 'form', 'event_label' : 'Form '+form_id+' - '+label } );
									};
								})( field_id, field_settings )
							);
						}
					} // End focus fields events
				}
			);
		}	// End forms settings iteration
	}
);