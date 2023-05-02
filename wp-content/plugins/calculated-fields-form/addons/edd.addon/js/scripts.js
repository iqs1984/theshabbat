/**/
function cff_edd_integration()
{
	if('cff_edd_integration' in fbuilderjQuery) return;
	fbuilderjQuery['cff_edd_integration'] = 1;

	var $ = fbuilderjQuery;

	window[ 'cpcff_edd_validate'] = function( fId, e )
	{
		var r = true;

		if( typeof $.fn.valid !== 'undefined' )	r = $( e ).valid();

		if( !r )
		{
			setTimeout(
				(function( $, e )
				{
					return  function()
							{
								$( e ).find( ':submit' ).removeAttr( 'disabled' );
							};
				})( $, e ),
				500
			);
		}
		else
		{
			if( typeof fbuilderjQuery !== 'undefined' && fbuilderjQuery.fn.valid !== 'undefined' )	r = fbuilderjQuery( e ).valid();
			if( typeof window[ 'doValidate'+fId ] != 'undefined' ) r = window[ 'doValidate'+fId ]( e );
		}
		return r;
	};

	$( '[name="cp_calculatedfieldsf_pform_psequence"]' ).each(
		function()
		{
			var e   = $( this ),
				fId = e.val(),
				w   = e.closest( '.cpcff-edd-wrapper' );

			if(
				w.length &&
				typeof window[ 'doValidate' + fId ] != 'undefined'
			)
			{
				w.closest( 'form' )
				 .attr( 'id', 'cp_calculatedfieldsf_pform' + fId )
				 .attr( 'name', 'cp_calculatedfieldsf_pform' + fId )
				 .attr( 'enctype', 'multipart/form-data' )
				 .attr( 'onsubmit', 'return cpcff_edd_validate( "' + fId + '", this )' )
				 .addClass('cpcff-edd');

				w.find( '.pbSubmit' ).remove();
			}
		}
	);
} // End cff_edd_integration

fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery(cff_edd_integration);
fbuilderjQuery(window).on('load',cff_edd_integration);