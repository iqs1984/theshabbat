	$.fbuilder.controls['fdropdownds'] = function(){};
	$.extend(
		$.fbuilder.controls['fdropdownds'].prototype,
		$.fbuilder.controls['fdropdown'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fdropdownds",
			defaultSelection:"",
			first_choice:false,
			first_choice_text:"",
			first_time:true,
			show:function()
				{
					this.choices = [];
					this.choicesVal = [];
					return $.fbuilder.controls['fdropdown'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;
					$.fbuilder.controls['datasource'].prototype.getData.call(this, function(data)
						{
							var str = '',
								e 	= $('#'+me.name);
							if(typeof data['error'] != 'undefined')
							{
								alert(data.error);
							}
							else
							{
								var t, v, used = [];
								while(data.data.length)
								{
									var o = data.data.shift(), s = JSON.stringify(o);
									if($.inArray(s,used) == -1)
									{
										v = ((typeof o['value'] != 'undefined') ? o['value'] : '');
										t = ((typeof o['text'] != 'undefined')  ? o['text']  :  v);
										str += '<option value="'+cff_esc_attr(v)+'" vt="'+cff_esc_attr((me.toSubmit == 'text') ? t : v) +'">'+cff_esc_attr(t)+'</option>';
										used.push(s);
									}
								}
							}

							e.html((me.first_choice ? '<option value="">'+cff_esc_attr(me.first_choice_text)+'</option>' : '')+str);

							if(str.length && me.first_time)
							{
								me.first_time = false;
								$.fbuilder.controls['datasource'].prototype.setDefault.call(me);
							}
							e.trigger('change').trigger('cff-data-filled');

                            if(me.select2)
                            {
                                $('#'+me.name).next('.cff-select2-container').remove();
                                $('#'+me.name).after('<span class="cff-select2-container"></span>');
                                $('#'+me.name).on('change', function(){$(this).valid();});
                                if('select2' in $.fn)
                                    $('#'+me.name).select2({'dropdownParent':$('#'+me.name).next('.cff-select2-container')});
                                else
                                    $(document).ready(function(){if('select2' in $.fn) $('#'+me.name).select2({'dropdownParent':$('#'+me.name).next('.cff-select2-container')});});
                            }
						}
					);
				},
			setVal : function(v, nochange, _default)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['fdropdown'].prototype.setVal.call(this, v, nochange, _default);
				}
	});