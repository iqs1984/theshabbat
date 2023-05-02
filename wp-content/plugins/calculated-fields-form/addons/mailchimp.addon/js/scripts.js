/**/
jQuery( function( $ )
    {
		function _get_data()
		{
			var obj = {
					'api_key' : $.trim( $( '[name="cpcff_mailchimp_api_key"]' ).val() ),
					'list_id' : $.trim( $( '[name="cpcff_mailchimp_list_id"]' ).val() ),
					'url' 	  : document.location.href
				};

			$( '[name="#cpcff_mailchimp_api_key"]' ).val( obj.api_key );
			$( '[name="#cpcff_mailchimp_list_id"]' ).val( obj.list_id );

			return obj;
		};

		function _replace_options(str, options, attr )
		{
			var v;
			str  = str.replace(new RegExp('\s*'+attr+'\s*', 'gi'), ' ');
			for(var i in options)
			{
				v = options[i]+'"';
				str = str.replace( new RegExp(v, 'gi'), v+' '+attr+' ');
			}
			return str;
		};


		function _escape_html(string)
		{
			var entityMap = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#39;',
				'/': '&#x2F;',
				'`': '&#x60;',
				'=': '&#x3D;'
			};
			return String(string).replace(/[&<>"'`=\/]/g, function (s) {
				return entityMap[s];
			});
		};

		window[ 'cpcff_mailchimp_getList' ] = function ()
		{
			var obj = _get_data();

			if( /.+\-us\d+$/i.test( obj.api_key ) )
			{
				$.getJSON(
					obj.url,
					{
						'cpcff_mailchimp_nonce'  : cpcff_mailchimp_nonce,
						'cpcff_mailchimp_action' : 'cpcff_mailchimp_get_lists',
						'api_key': obj.api_key
					},
					function( data )
					{
						if( typeof data[ 'error' ] != 'undefined' )
						{
							alert( data[ 'error' ] );
						}
						else if( typeof data[ 'lists' ] != 'undefined' )
						{
							var str = '<select class="cpcff-mailchimp-list" style="width:100%;"><option value="">'+cpcff_mailchimp_texts[ 'select_list' ]+'</option>',
								selected;
							for( var i in data[ 'lists' ] )
							{
								selected = ( data[ 'lists' ][ i ][ 'id' ] == obj.list_id ) ? 'SELECTED' : '' ;
								str += '<option value="'+data[ 'lists' ][ i ][ 'id' ]+'" '+selected+' >'+data[ 'lists' ][ i ][ 'name' ]+'</option>';
							}
							str += '</select>';
							$( '.cpcff-mailchimp-list-container' ).html( str );
						}
						else
						{
							alert( cpcff_mailchimp_texts[ 'no_list' ] );
						}
					}
				);
			}
			else
			{
				alert( cpcff_mailchimp_texts[ 'invalid_api_key' ] );
			}
		};

		window[ 'cpcff_mailchimp_getFields' ] = function ()
		{
			var obj 	= _get_data(),
				fields 	= {},
				groups  = {};

			$('[name *="cpcff_mailchimp_attr"]').each(function(){
				var e = $(this),
					p = e.attr( 'name' ).match(/cpcff_mailchimp_attr\[([^\]]+)\]/);
				fields[ p[ 1 ] ] = e.val();
			});

			$('[name *="cpcff_mailchimp_gpr["]').each(function(){
				if( this.checked )
				{
					var e = $(this),
						p = e.attr( 'name' ).match(/cpcff_mailchimp_gpr\[([^\]]+)\]/),
						i = p[1];

					groups[i] = {attr : '', options : []};

					// Determine the options selected or ticked
					$('[name ^="'+p[1]+'"]').each(
						function(){
							if(this.tagName == 'INPUT')
							{
								groups[i]['attr'] = 'CHECKED';
								if(this.checked) groups[i]['options'].push(this.value);
							}
							else
							{
								groups[i]['attr'] = 'SELECTED';
								$(this).find('option:selected').each(function(){
									groups[i]['options'].push(this.value);
								});
							}
						}
					);
				}
			});

			if( /.+\-us\d+$/i.test( obj.api_key ) )
			{
				if( !/^\s*$/.test( obj.list_id ) )
				{
					$.getJSON(
						obj.url,
						{
							'cpcff_mailchimp_nonce'  : cpcff_mailchimp_nonce,
							'cpcff_mailchimp_action' : 'cpcff_mailchimp_get_fields',
							'api_key': obj.api_key,
							'list_id': obj.list_id
						},
						function( data )
						{
							if( typeof data[ 'error' ] != 'undefined' )
							{
								alert( data[ 'error' ] );
							}
							else
							{
								// Fields
								var tag, f_str = '<tr><td>email_address</td><td><input type="text" placeholder="filedname#" name="cpcff_mailchimp_attr[email_address]" value="'+(( typeof fields[ 'email_address' ] != 'undefined' ) ? fields[ 'email_address' ] : '')+'" /></td></tr>';
								if( typeof data[ 'fields' ] != 'undefined' )
								{
									for( var i in data[ 'fields' ] )
									{
										tag = data[ 'fields' ][i];
										f_str += '<tr><td>'+tag+'</td>';
										f_str += '<td><input type="text" placeholder="filedname#" name="cpcff_mailchimp_attr['+tag+']"  value="'+(( typeof fields[ tag ] != 'undefined' ) ? fields[ tag ] : '')+'" /></td></tr>';
									}
								}
								$( '.cpcff-mailchimp-fields-container' ).html( f_str );

								// Groups
								var g_str = '', g_id;
								if( typeof data[ 'groups' ] !== 'undefined' )
								{
									for( var i in data[ 'groups' ] )
									{
										g_id =  data[ 'groups' ][i]['id'];
										g_str += '<tr><td valign="top"><input '+( ( typeof groups[ g_id ] !== 'undefined' ) ? 'CHECKED' : '' )+' type="checkbox" name="cpcff_mailchimp_gpr['+g_id+']" />';
										g_str += data[ 'groups' ][i]['title'];
										g_str += '<input type="hidden" name="cpcff_mailchimp_gpr_title['+g_id+']" value="'+data[ 'groups' ][i]['title']+'" /></td>';
										g_str += '<td class="cpcff-mailchimp-group-container">';
										if(typeof groups[ g_id ] !== 'undefined')
										{
											data[ 'groups' ][i]['interests'] = _replace_options(data[ 'groups' ][i]['interests'], groups[ g_id ]['options'],groups[ g_id ]['attr']);
										}
										g_str += '<input type="hidden" name="cpcff_mailchimp_gpr_structure['+g_id+']" value="'+_escape_html(data[ 'groups' ][i]['interests'])+'" />';
										g_str += data[ 'groups' ][i]['interests']+'</td></tr>';
									}
								}
								$( '.cpcff-mailchimp-groups-container' ).html( g_str );
								$( '[name^="cpcff_mailchimp_gpr["]' ).change();
							}
						}
					);
				}
				else
				{
					alert( cpcff_mailchimp_texts[ 'required_list_id' ] );
				}
			}
			else
			{
				alert( cpcff_mailchimp_texts[ 'invalid_api_key' ] );
			}
		};

		$(document).on(
			'change',
			'[name^="cpcff_mailchimp_gpr"]',
			function(){
				var e = $(this),
					p = e.attr( 'name' ).match(/cpcff_mailchimp_gpr\[([^\]]+)\]/),
					i = p[1];
				$('[name^="'+i+'"]').prop('disabled', !e.is(':checked'));
			}
		);

		$( document ).on(
			'change',
			'.cpcff-mailchimp-list',
			function(){
				$( '.cpcff-mailchimp-fields-container' ).html( '<tr><td colspan="2">'+cpcff_mailchimp_texts[ 'no_fields' ]+'</td></tr>' );
				$( '.cpcff-mailchimp-groups-container' ).html( '<tr><td colspan="2">'+cpcff_mailchimp_texts[ 'no_groups' ]+'</td></tr>' );
				$( '[name="cpcff_mailchimp_list_id"]' ).val( $(this).val() );
			}
		);

		$( document ).on(
			'change',
			'.cpcff-mailchimp-group-container input,.cpcff-mailchimp-group-container select',
			function () {
				var n = this.name.replace(/[^a-z0-9]/ig,''),
					h = $('[name="cpcff_mailchimp_gpr_structure['+n+']"]'),
					s = h.val(),
					v = [],
					a;

				if(this.tagName == 'INPUT')
				{
					a = 'CHECKED';
					$('[name ^="'+n+'"]:checked').each(function(){v.push(this.value);});
				}
				else
				{
					a = 'SELECTED';
					v.push(this.value);
				}
				s = _replace_options(s, v, a);
				h.val(s);
			}
		);
	}
);