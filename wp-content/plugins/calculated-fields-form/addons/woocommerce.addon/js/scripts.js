/**/
function cff_woocommerce_integration()
{
	if('cff_woocommerce_integration' in fbuilderjQuery) return;
	fbuilderjQuery['cff_woocommerce_integration'] = 1;

	var $ = fbuilderjQuery;

    window[ 'cpcff_woocommerce_validate'] = function( fId, e )
	{
		var r = true;

		$(e).find('[max=""]').removeAttr('max');
		$(e).find('[min=""]').removeAttr('min');

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

	$('[name="cp_calculatedfieldsf_pform_psequence"]').each(
		function()
		{
			var e   = $( this ),
				fId = e.val(),
				w   = e.closest( '.cpcff-woocommerce-wrapper' );

			if(
				w.length &&
				typeof window[ 'doValidate' + fId ] != 'undefined'
			)
			{
				w.closest( 'form' )
				 .attr( 'id', 'cp_calculatedfieldsf_pform' + fId )
				 .attr( 'name', 'cp_calculatedfieldsf_pform' + fId )
				 .attr( 'onsubmit', 'return cpcff_woocommerce_validate( "' + fId + '", this )' )
				 .css('display', 'block')
                 .parent().on('submit', function(evt){ /* Fix conflict with Side Cart Woocommerce (Ajax) - XootiX */
                    evt.preventDefault();
                    evt.stopPropagation();
                    return false;
                 });
				w.find( '.pbSubmit' ).remove();
			}
		}
	);

    // Fix a conflict with Divi and other third-party themes
    $('form[name*="cp_calculatedfieldsf_pform_"] [name="add-to-cart"]').on('mousedown', function(evt){
		cff_woocommerce_stop_submission = false;
        if(!$(this).closest('form').valid()){
			cff_woocommerce_stop_submission = true;
            var me = this;
            setTimeout(function(){$(me).removeClass('loading');}, 1000);
            evt.stopPropagation();
            evt.preventDefault();
            return false;
        }
    })
	.on('mouseup click', function(evt){
		if(cff_woocommerce_stop_submission) {
			evt.stopPropagation();
            evt.preventDefault();
            return false;
		}
	});

	// Integration with "opc_add_remove_product"
	$(document).on( 'opc_add_remove_product', function(evt, data, e, selectors){
		if(
			'action' in data &&
			data['action'] == 'pp_add_to_cart' &&
			'input_data' in data &&
			data['input_data'].indexOf('woocommerce_cpcff_form') != -1
		)
		{
			data['invalid'] = true;
			jQuery(selectors).closest('form').submit();
		}
	});

	// Quantity
	$('[name="woocommerce_cpcff_quantity_field"]').each( function(){
		var f = $(this.form),
		s = $.trim(this.value),
		q = f.find('[name="quantity"]'),
		i = $('[name="cp_calculatedfieldsf_pform_psequence"]', f).val(),
		changeFlag = false;

		if(q.length)
		{
			$(document).on('change', '[id*="'+s+i+'"]', function(){q.val(this.value); if(!changeFlag){changeFlag = true; q.change();} else changeFlag = false;});
			q.on('change', function(){var e = $('[id*="'+s+i+'"]'); if(e.length){e.val(this.value); if(!changeFlag){changeFlag = true; e.change();} else changeFlag = false;}});
		}
	});

	// Refresh the product price.
	$('[name="woocommerce_cpcff_field"]').each(
		function()
		{
			var e = $( this ),
				f = e.siblings( '[name="woocommerce_cpcff_form"]' ),
				a = f.siblings( '[name="woocommerce_cpcff_product"]' ),
				s = $( '[name="cp_calculatedfieldsf_id"][value="' + f.val() + '"]' ).siblings( '[name="cp_calculatedfieldsf_pform_psequence"]' ).val(),
				tmpjQuery = ( typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : $;

			function replacePrice(price, product)
			{
				if(typeof woocommerce_price_selector != 'undefined')
				{
					$(woocommerce_price_selector).html(price);
				}
				else
				{
					$( '[id="product-'+product+'"]' )
					 .find( '.summary .woocommerce-Price-amount.amount' )
					 .each(
						function()
						{
							if(
								$( this ).closest( 'del' ).length == 0 &&
								$( this ).closest( '.widget_shopping_cart' ).length == 0
							)
							$( this ).html( price );
						}
					);
				}
			}

			tmpjQuery( document ).on('change', '#'+e.val()+s, (function(a){
				return function(){ replacePrice($(this).val(), a); };
			})(a.val()));

			tmpjQuery( document ).on(
				'cpcff_default_calc',
				'#cp_calculatedfieldsf_pform'+s,
				(function(a, p){
					return function( evt ){
						replacePrice($( '#'+p ).val(), a);
					}
				})(a.val(), e.val()+s)
			);
		}
	);
} // End cff_woocommerce_integration

fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery(cff_woocommerce_integration);
fbuilderjQuery(window).on('load',cff_woocommerce_integration);

// To get variation prices and evaluate the equations
(function(){
	if(typeof fbuilderjQuery != 'undefined')
	{
		var $ 		= fbuilderjQuery,
			prices 	= {};

		function check_forms_and_evaluate_equations(to_search)
		{
			var form, equation;
			for(var i in document.forms)
			{
				form = document.forms[i];
				if(typeof form == 'object' && 'equations' in form)
				{
					for(var j in form.equations)
					{
						equation = form.equations[j];
						if(equation.equation.search(to_search) != -1)
						{
							EVALEQUATION(form, equation.result.replace(/_\d+$/, ''));
						}
					}
				}
			}
		};

		$(document).on(
			'found_variation',
			function( evt, variation )
			{
				woocommerce_cpcff_product_price = variation.display_price;
				check_forms_and_evaluate_equations(/woocommerce_cpcff_product_price/);
            }
		);
	}
})();