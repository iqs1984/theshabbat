	$.fbuilder.controls['ftextareads']=function(){};
	$.extend(
		$.fbuilder.controls['ftextareads'].prototype,
		$.fbuilder.controls['ftextarea'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"ftextareads",
			first_time:true,
			show:function()
				{
					return $.fbuilder.controls['ftextarea'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;
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
					$.fbuilder.controls['ftextarea'].prototype.setVal.call(this, v, nochange);
				}
	});