	$.fbuilder.controls['fhiddends']=function(){};
	$.extend(
		$.fbuilder.controls['fhiddends'].prototype,
		$.fbuilder.controls['fhidden'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fhiddends",
			first_time:true,
			show:function()
				{
					return $.fbuilder.controls['fhidden'].prototype.show.call(this);
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
					$.fbuilder.controls['fhidden'].prototype.setVal.call(this, v, nochange);
				}
		}
	);