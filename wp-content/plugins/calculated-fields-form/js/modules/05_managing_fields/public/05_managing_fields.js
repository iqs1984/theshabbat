/*
* managing_fields.js v0.1
* By: CALCULATED FIELD PROGRAMMERS
* The script allows managing fields
* Copyright 2015 CODEPEOPLE
* You may use this project under MIT or GPL licenses.
*/

;(function(root){
	var lib = {
		formsDependency:{}
	};
	lib.cf_processing_version = '0.1';

	/*** PRIVATE FUNCTIONS ***/

	function _getForm(_form)
	{
		if(typeof _form == 'undefined' || _form == null){
			if('currentFormId' in fbuilderjQuery.fbuilder) _form = fbuilderjQuery.fbuilder.currentFormId;
			else return '_1';
		}
		if(/^_\d*$/.test(_form)) return _form;
		if(/^\d*$/.test(_form)) return '_'+_form;
		return $( $(_form).length ? _form : '#'+_form ).find('[name="cp_calculatedfieldsf_pform_psequence"]').val();
	}

	function _getField( _field, _form )
	{
        try
        {
            if(typeof _field == 'undefined') return false;
            if(typeof _field == 'object')
            {
                if('ftype' in _field) return _field;
                if('jquery' in _field)
                {
                    if(_field.length) _field = _field[0];
                    else return false;
                }

                if('getAttribute' in _field)
                {
					_form  = $(_field).closest('form');
                    var to_check = _field.getAttribute('class').match(/fieldname\d+/);
					if(to_check) _field = to_check[0];
					else {
						_field = _field.getAttribute('name').match(/fieldname\d+/)[0];
					}
                }
                else return false;
            }
			if(typeof _form == 'undefined' && typeof _field == 'string' && _field.match(/fieldname\d+(_\d+)/)) _form = _field.match(/fieldname\d+(_\d+)/)[1];
            return $.fbuilder['forms'][_getForm(_form)].getItem(_field);
        } catch (err) { return false; }
	}

	/*** PUBLIC FUNCTIONS ***/
    lib.getField = function(_field, _form)
    {
		// To eval equations with fields from other forms
		var _fi = _getField(_field, _form), _fo;
		if(_fi){
			try{
				_fo = _fi['form_identifier'];
				if(
					_fo &&
					_fi &&
					'currentFormId' in $.fbuilder &&
					'currentEq' in $.fbuilder &&
					$.fbuilder['currentEq']['identifier'] != _fo
				){
					var _eqResultField = $.fbuilder[ 'currentEq' ][ 'result' ];
					if ( !( _fi.name in lib.formsDependency ) ) {
						lib.formsDependency[ _fi.name ] = {};
						$( '[name *= "' + _fi.name + '"]' ).on( 'change keyup depEvent', function(){
							var _fname = $(this).attr('name').match( /fieldname\d+_\d+/ )[0];
							if( _fname in lib.formsDependency ) {
								for( var i in lib.formsDependency[ _fname ] ) {
									EVALEQUATION(i, lib.formsDependency[ _fname ][ i ])
								}
							}
						} );
					}
					lib.formsDependency[ _fi.name ][ _eqResultField ] = $.fbuilder['currentFormId'];
				}
			} catch(err){}
		}
		// End

        return _fi;
    };

	lib.validform = lib.VALIDFORM = lib.ValidForm = function( _form, _silent ){
		_silent = _silent || false;
		var o = _getForm(_form), f;
		if(o){
			f = $('[id="'+$.fbuilder.forms[o]['formId']+'"]');
			if(f.length) return _silent ? f.validate().checkForm()  : f.valid();
		}
		return false;
	};

	lib.validfield = lib.VALIDFIELD = lib.ValidField = function( _field, _form, _silent ){
		_silent = _silent || false;
		var o = _getForm(_form), f = _getField(_field, _form), j;
		if(f){
			j = f.jQueryRef().find(':input');
			if(j.length)
				return _silent ? j.closest('form').validate().check( j ) : j.valid();
		}
		return false;
	};

	lib.activatefield = lib.ACTIVATEFIELD = function( _field, _form )
	{
		var o = _getForm(_form), f = _getField(_field, _form), j;
		if(f)
		{
			j = f.jQueryRef();
            j.removeClass('ignorefield');
			if(j.find('[id*="'+f.name+'"]').hasClass('ignore'))
			{
                j.add(j.find('.fields')).show();
				if(f.name in $.fbuilder.forms[o].toHide) delete $.fbuilder.forms[o].toHide[f.name];
				if(!(f.name in $.fbuilder.forms[o].toShow)) $.fbuilder.forms[o].toShow[f.name] = {'ref': {}};
				j.find('[id*="'+f.name+'"]').removeClass('ignore').change();
				$.fbuilder.showHideDep({'formIdentifier':o,'fieldIdentifier':f.name});
			}
		}
	};

	lib.ignorefield = lib.IGNOREFIELD = function( _field, _form )
	{
		var o = _getForm(_form), f = _getField(_field, _form), j;
		if(f)
		{
			j = f.jQueryRef();
            j.addClass('ignorefield');
			if(!j.find('[id*="'+f.name+'"]').hasClass('ignore'))
			{
				j.add(j.find('.fields')).hide();
				if(!(f.name in $.fbuilder.forms[o].toHide)) $.fbuilder.forms[o].toHide[f.name] = {};
				if(f.name in $.fbuilder.forms[o].toShow) delete $.fbuilder.forms[o].toShow[f.name];
				j.find('[id*="'+f.name+'"]').addClass('ignore').change();
				$.fbuilder.showHideDep({'formIdentifier':o,'fieldIdentifier':f.name});
			}
		}
	};

    lib.isignored = lib.ISIGNORED = function( _field, _form )
	{
		var o = _getForm(_form), f = _getField(_field, _form), j;
		if(f) return 0 < f.jQueryRef().find('.ignore').length;
        return false;
	};

    lib.showfield = lib.SHOWFIELD = function( _field, _form )
    {
        var f = _getField(_field, _form), j;
		if(f)
		{
			j = f.jQueryRef();
            if(!j.find('[id*="'+f.name+'"]').hasClass('ignore'))
                j.removeClass('hide-strong').show();
		}
    };

	lib.hidefield = lib.HIDEFIELD = function( _field, _form )
    {
        var f = _getField(_field, _form);
		if(f)
		{
            j = f.jQueryRef();
            if(!j.find('[id*="'+f.name+'"]').hasClass('ignore'))
                f.jQueryRef().addClass('hide-strong');
		}
    };

	lib.ishidden = lib.ISHIDDEN = function( _field, _form )
	{
		var o = _getForm(_form), f = _getField(_field, _form), j;
		if(f) return f.jQueryRef().is(':hidden');
        return true;
	};

    lib.disableequations = lib.DISABLEEQUATIONS = function(f)
	{
		jQuery(f || '[id*="cp_calculatedfieldsf_pform_"]').attr('data-evalequations',0);
	};

	lib.enableequations = lib.ENABLEEQUATIONS = function(f)
	{
		jQuery(f || '[id*="cp_calculatedfieldsf_pform_"]').attr('data-evalequations',1);
	};

	lib.EVALEQUATIONS = lib.evalequations = function(f)
	{
		if( typeof f != 'undefined') {
			fbuilderjQuery.fbuilder.calculator.defaultCalc(f);
		} else {
			for( var i in fbuilderjQuery.fbuilder.forms ) {
				fbuilderjQuery.fbuilder.calculator.defaultCalc(jQuery('[id="'+fbuilderjQuery.fbuilder.forms[i].formId+'"]'));
			}
		}
	};

	lib.EVALEQUATION = lib.evalequation = function( _field, _form )
	{
        try
        {
            /* For compatibility with function( _form, _field ) */
            if(typeof _field == 'object' && 'tagName' in _field && _field.tagName == 'FORM')
                [_field, _form] = [_form, _field];

            var c = fbuilderjQuery.fbuilder.calculator;

            if(typeof _field == 'undefined') c.defaultCalc(_form);

            var f = _getField(_field, _form),
                o = f.jQueryRef().closest('form')[0];

            for(i in o.equations)
            {
                if(o.equations[i].result == f.name){
                    c.enqueueEquation(f.form_identifier, [o.equations[i]]);
                    c.processQueue(f.form_identifier);
                    return;
                }
            }
        }
        catch(err){if('console' in window) console.log(err);}
    };


    lib.COPYFIELDVALUE = lib.copyfieldvalue = function(_field, _form)
    {
        var f = _getField(_field, _form), j;
		if(f)
		{
			j = f.jQueryRef().find(':input:eq(0)');
			if(j.length)
			{
                try
                {
                    j.select();
                    document.execCommand('copy');
                } catch(err){}
			}
		}
    };

    lib.gotopage = lib.GOTOPAGE = lib.goToPage = function(p, f)
    {
        try
        {
            var o = $('#'+$.fbuilder['forms'][_getForm(f)].formId), c;
            if(o.length)
            {
                c = o.find('.pbreak:visible').attr('page');
                $.fbuilder.goToPage({'form':o,'from':c,'to':p, 'forcing' : true});
            }
        } catch(err) { if('console' in window) console.log(err); }
    };

    lib.gotofield = lib.GOTOFIELD = lib.goToField = function(e, f)
    {
        try
        {
            var o = $('#'+$.fbuilder['forms'][_getForm(f)].formId), p, c;
			if(o.length)
            {
				e = o.find('[id*="'+(Number.isInteger(e) ? 'fieldname'+e : e)+'_"]');
				if(e.length)
				{
					c = o.find('.pbreak:visible').attr('page');
					p = e.closest('.pbreak').attr('page');
					$(document).one('cff-gotopage', function(evt, arg){
						if(e.is(':visible'))
							$('html,body').animate({scrollTop:e.offset().top});
					});
					$.fbuilder.goToPage({'form':o,'from':c,'to':p, 'forcing' : true});
				}
            }
        } catch(err) { if('console' in window) console.log(err); }
    };

    if(window.PRINTFORM == undefined)
    {
        lib.printform = lib.PRINTFORM = function(show_pages, f)
        {
			f = _getForm(f);
			function addRemoveClasses(add){
				var o = $('#'+$.fbuilder['forms'][f].formId),
					m = add ? 'addClass' : 'removeClass';
				if(o.length)
				{
					o[m]('cff-print');
					if(!!show_pages) o.find('.pbreak')[m]('cff-print');
					while(o.length)
					{
						o.siblings()[m]('cff-no-print');
						o = o.parent();
					}
				}

			};
			addRemoveClasses(true);
            window.print();
            setTimeout(function(){
				addRemoveClasses(false);
            }, 5000);
        };

    }
	root.CF_FIELDS_MANAGEMENT = lib;

})(this);