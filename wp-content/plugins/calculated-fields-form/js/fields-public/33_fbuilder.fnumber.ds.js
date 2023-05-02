	$.fbuilder.controls['fnumberds']=function(){};
	$.extend(
		$.fbuilder.controls['fnumberds'].prototype,
		$.fbuilder.controls['fnumber'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fnumberds",
			first_time:true,
			show:function()
				{
					return $.fbuilder.controls['fnumber'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;
                    $.fbuilder.controls['fnumber'].prototype.after_show.call(this);
					$.fbuilder.controls['datasource'].prototype.getData.call(this, function(data)
						{
							var v = '';
							if(typeof data['error'] != 'undefined')
							{
								alert(data.error);
							}
							else
							{
								if(data.data.length)
								{
									v = data.data[0]['value'];
								}
							}
							if(me.first_time)
							{
								me.first_time = false;
								if(typeof me.defaultSelection != 'undefined') v = me.defaultSelection;
							}
							me.setVal(v);
                            $('[id="'+me.name+'"]').trigger('cff-data-filled');
						}
					);
				},
			setVal : function(v, nochange)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['fnumber'].prototype.setVal.call(this, v, nochange);
				}
	});