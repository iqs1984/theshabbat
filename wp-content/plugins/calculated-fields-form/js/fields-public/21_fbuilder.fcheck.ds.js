	$.fbuilder.controls['fcheckds'] = function(){};
	$.extend(
		$.fbuilder.controls['fcheckds'].prototype,
		$.fbuilder.controls['fcheck'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fcheckds",
			defaultSelection:"",
			first_time:true,
			show:function()
				{
					return '<div class="fields '+cff_esc_attr(this.csslayout)+(this.onoff ? ' cff-switch-container' : '')+' '+this.name+' cff-checkbox-field" id="field'+this.form_identifier+'-'+this.index+'"><label>'+this.title+''+((this.required)?"<span class='r'>*</span>":"")+'</label><div class="dfield"><input type="hidden" name="'+this.name+'[]" id="'+this.name+'_cb0" value="" /><span class="uh">'+this.userhelp+'</span></div><div class="clearer"></div></div>';
				},
			after_show : function()
				{
					var me = this,
						ignorepb = ($('[id="field'+me.form_identifier+'-'+me.index+'"]').closest('.pbreak').is(':visible')) ? '' : ' ignorepb ';

					$.fbuilder.controls['fcheck'].prototype.after_show.call(this);
					$.fbuilder.controls['datasource'].prototype.getData.call(this, function(data)
						{
							var str = '';
							if(typeof data['error'] != 'undefined')
							{
								alert(data.error);
							}
							else
							{
								var used = [], i = 0;
								while(data.data.length)
								{
									var e = data.data.shift(), s = JSON.stringify(e);
									if($.inArray(s,used) == -1)
									{
										str += '<div class="'+me.layout+'"><label for="'+me.name+'_cb'+i+'"><input aria-label="'+cff_esc_attr(e.text)+'" name="'+me.name+'[]" id="'+me.name+'_cb'+i+'" class="field group '+((me.required) ? ' required ' : '')+ignorepb+'" value="'+cff_esc_attr(e.value)+'" vt="'+cff_esc_attr((me.toSubmit == 'text') ? e.text : e.value)+'" type="checkbox" /> '+
                                        (me.onoff ? '<span class="cff-switch"></span>': '') +
                                        '<span>'+e.text+'</span></label></div>';
										used.push(s);
										i++;
									}
								}
							}
							$('#field'+me.form_identifier+'-'+me.index+' .dfield').html(str+(me.userhelpTooltip ? '' : '<span class="uh">'+me.userhelp+'</span>')+'<div class="clearer"></div>');
							if(str.length && me.first_time)
							{
								me.first_time = false;
								$.fbuilder.controls['datasource'].prototype.setDefault.call(me);
							}
							$('[id*="'+me.name+'"]').trigger('change').trigger('cff-data-filled');
						}
					);
				},
			setVal : function(v, nochange, _default)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['fcheck'].prototype.setVal.call(this, v, nochange, _default);
				}
	});