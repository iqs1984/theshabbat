	$.fbuilder.controls['fPhoneds']=function(){};
	$.extend(
		$.fbuilder.controls['fPhoneds'].prototype,
		$.fbuilder.controls['fPhone'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fPhoneds",
			first_time:true,
			show:function()
				{
					return $.fbuilder.controls['fPhone'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;
                    $.fbuilder.controls['fPhone'].prototype.after_show.call(me);
					$.fbuilder.controls['datasource'].prototype.getData.call(me, function(data)
						{
							var v = '';

							if(typeof data['error'] != 'undefined') alert(data.error);
							else if(data.data.length) v = data.data[0].value;

							if(me.first_time)
							{
								me.first_time = false;
								if(typeof me.defaultSelection != 'undefined') v = me.defaultSelection;
							}

                            me.setVal(v);
                            $('#'+me.name).trigger('cff-data-filled');
						}
					);
				},
			setVal : function(v, nochange)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['fPhone'].prototype.setVal.call(this, v, nochange);
				}
		}
	);