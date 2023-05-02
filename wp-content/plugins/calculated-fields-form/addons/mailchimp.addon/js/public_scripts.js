/**/
if( typeof fbuilderjQuery != 'undefined' )
{
	fbuilderjQuery( document ).one( 'showHideDepEvent', function( evt, f_id ){
		var $ = fbuilderjQuery;
		if( typeof f_id != 'undefined' )
		{
			var id = f_id.replace( 'cp_calculatedfieldsf_pform', '' );
			if( typeof window[ 'mailchimp_groups'+id ] != 'undefined' )
			{
				var mg = window[ 'mailchimp_groups'+id ],
					g  = '';
				for( var i in mg )
				{
					g += '<div class="fields mailchimp-group mailchimp-group-'+i+'">';
					g += '<label>'+( ( typeof mg[ i ][ 'title' ] != 'undefined' ) ? mg[ i ][ 'title' ] : '' )+'</label>';
					g += '<div class="dfield">';
					g += ( typeof mg[ i ][ 'structure' ] != 'undefined' ) ? mg[ i ][ 'structure' ] : '';
					g += '</div>';
					g += '</div>';
				}

				if( g != '' )
				{
					if( $( '#'+f_id+' .mailchimp-groups' ).length )
					{
						var into = $( '#'+f_id+' .mailchimp-groups' );
						if( into.find( '.dfield' ) ) into = into.find( '.dfield' );
						into.append( g );
					}
					else
					{
						var captcha = $( '#'+f_id+' .captcha' );
						if(captcha.length) captcha.before(g)
						else
						{
							var last = $( '#'+f_id+' .fields:last' );
							if( last.length ) last.after( g );
						}
					}
				}
			}
		}
	});
}