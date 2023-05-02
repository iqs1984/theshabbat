	$.fbuilder.controls['ftextds']=function(){};
	$.extend(
		$.fbuilder.controls['ftextds'].prototype,
		$.fbuilder.controls['ftext'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"ftextds",
			first_time: true,
			show:function()
				{
					return $.fbuilder.controls['ftext'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;
                    $.fbuilder.controls['ftext'].prototype.after_show.call(me);
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
							$('#'+me.name).val(v).trigger('change').trigger('cff-data-filled');
						}
					);
				},
			setVal : function(v, nochange)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['ftext'].prototype.setVal.call(this, v, nochange);
				}
	});