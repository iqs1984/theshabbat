window.addEventListener('load', function(){
    var $ = fbuilderjQuery || jQuery || false, e;

    if($ && typeof cff_verification_code_settings != 'undefined')
    {
        for(var i in cff_verification_code_settings)
        {
            e = $('[id="'+cff_verification_code_settings[i]['email_field']+'_'+i+'"]');
            if(
                e.length &&
                (
                    !('email_field_required' in cff_verification_code_settings[i]) ||
                    cff_verification_code_settings[i]['email_field_required']*1
                )
            ) e.addClass('required');
        }
    }

    $(document).on('click', '.cff-verification-code-dlg-close', function(){
        $(this).closest('.cff-verification-code-dlg').remove();
    });
});

(function(){

    // Module var
    var url;

    // Private functions
    function get_jquery()
    {
        return fbuilderjQuery || jQuery || false;
    }; // End get_jquery

    function get_email(_c)
    {
        var $ = get_jquery(), f;
        if($)
        {
            try
            {
                f = $('[id="'+cff_verification_code_settings[_c]['email_field']+'_'+_c+'"]:not(.ignore)');
                if(f.length)
				{
					if(
						typeof cff_current_user_email != 'undefined' &&
						cff_current_user_email == f.val().toLowerCase()
					) return false;

					return f.val();
				}
            }
            catch(err){}
        }
        return false;
    }; // End get_email

    function get_form(_c)
    {
        try{
            return cff_verification_code_settings[_c]['formid'];
        }catch(err){}
        return false;
    } // End get_form

    window['cff_verification_code_verify'] = function(_c)
    {
        var $ = get_jquery(),
            e = get_email(_c),
            f = get_form(_c),
            o, v;

        if($ && e && f)
        {
            o = $('#cff_verification_code_dlg_'+_c+' [name="cff-verification-code-input"]');
            if(o.length && o.valid())
            {
                v = $.trim(o.val());
                $.ajax({
                    type: "post",
                    url:  url,
                    data: {
                        'cff-verification-code-action' : 'cff-verification-code-verify',
                        'cff-verification-code-email'  : e,
                        'cff-verification-code-form'   : f,
                        'cff-verification-code-code'   : v
                    },
                    success: function(data)
                    {
                        if(data != 'ok') alert(data);
                        else
                        {
                            cff_verification_code_settings[_c]['callback'](true);
                            o.closest('form').submit();
                        }
                    }
                });
            }
        }
    }; // End cff_verification_code_verify

    window['cff_verification_code_resend'] = function(_c, _r)
    {
        var v = get_email(_c), f = get_form(_c);
        if(v && f)
        {
            get_jquery().ajax({
                type: "post",
                url:  url,
                data: {
                    'cff-verification-code-action' : 'cff-verification-code-send',
                    'cff-verification-code-email'  : v,
                    'cff-verification-code-form'   : f
                },
                success: function(data){
                    if(_r && data) alert(data);
                }
            });
        }
    }; // End cff_verification_code_resend

    window['cff_open_verification_code_dialog'] = function(_c, _url, _callback)
    {
        url = _url;
        var $ = get_jquery();

        if(
            $ &&
            typeof cff_verification_code_settings != "undefined" &&
            _c in cff_verification_code_settings
        )
        {
            if(_callback) cff_verification_code_settings[_c]['callback'] = _callback;

            var s = cff_verification_code_settings[_c],
                l = s['dialog_label'],
                v = get_email(_c),
                dlg = '';

            var _match, _regexp = new RegExp('<%(fieldname\\d+)%>'), _field;
            while (_match = _regexp.exec(l))
            {
                _field = $('#'+_match[1]+'_'+_c);
                l = l.replace(_match[0], _field.length ? _field.val() : '' );
            }

            if(v)
            {
				if($('[id="cff_verification_code_dlg_'+_c+'"]').length == 0)
                {
                    dlg = '<div id="cff_verification_code_dlg_'+_c+'" class="cff-verification-code-dlg">'+
                        '<div class="cff-verification-code-dlg-close">x</div>'+
                        '<label class="cff-verification-code-dlg-label">'+l+'</label>'+
                        '<div class="cff-verification-code-dlg-controls-container">'+
                            '<input type="text" name="cff-verification-code-input" class="required" />'+
                            '<input type="button" value="'+s['verify_button'].replace(/'/g, "\'").replace(/"/g, '\"')+'" onclick="cff_verification_code_verify('+_c+');" class="cff-verification-code-verify-button">'+
                            '<input type="button" value="'+s['resend_button'].replace(/'/g, "\'").replace(/"/g, '\"')+'" onclick="cff_verification_code_resend('+_c+', 1);" class="cff-verification-code-resend-button">'+
                        '</div>'+
                        '<div class="cff-verification-code-dlg-instructions">'+
                        s['instructions_text']+
                        '</div>'+
                    '</div>';

                    $('[id="cp_calculatedfieldsf_pform_'+_c+'"] #fbuilder').append(dlg);
                    cff_verification_code_resend(_c);
                }
            }
            else
            {
                if(_callback) _callback(true);
            }
        }
    }; // End cff_open_verification_code_dialog

})()