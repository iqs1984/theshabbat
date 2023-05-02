	$.fbuilder.controls['fdatatableds']=function(){};
	$.extend(
		$.fbuilder.controls['fdatatableds'].prototype,
        $.fbuilder.controls['ffields'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fdatatableds",
            title:"Data table",
            columns: "",
            html:false,
            autowidth:false,
            paging: false,
            lengthchange:false,
            ordering:false,
            scrollx:false,
            scrolly:'',
            searching:false,
            language:'',
			show:function()
				{
                    var me = this;
                    $(document).on('click', '#'+this.name+' tbody tr', function(){
                        var i = $(this).data('record');
                        if(typeof i != 'undefined') $('#'+me.name).trigger('cff-datatable-click', i);
                    });
                    return '<div class="fields '+cff_esc_attr(this.csslayout)+' '+this.name+' cff-datatable-field" id="field'+this.form_identifier+'-'+this.index+'"><label>'+this.title+'</label><div class="dfield"><div id="'+this.name+'"></div></div><div class="clearer"></div></div>';
				},
			after_show : function()
				{
					var me = this;
					$.fbuilder.controls['datasource'].prototype.getData.call(this, function(data)
						{
							var str = '';
							if(typeof data['error'] != 'undefined')
							{
								alert(data.error);
							}
							else
							{
                                var settings = {
                                    'lengthChange'  : me.lengthchange,
                                    'ordering'      : me.ordering,
                                    'paging'        : me.paging,
                                    'scrollX'       : me.scrollx,
                                    'scrollY'       : me.scrolly,
                                    'searching'     : me.searching,
                                    'createdRow'    : function(row,data,dataIndex){
                                        $(row).attr('data-record', dataIndex);
										$(row).find('td').each(function() {
											if(!me.html)
												$(this).html( $('<div></div>').text( $( this ).html() ).html() );
										} );
                                    }
                                },
                                columns = me.columns.split(/[\n\r]/),
                                tmp;

                                if(me.autowidth) settings['autoWidth'] = me.autowidth;
                                settings['data'] = data.data;
								settings['columns'] = [];

                                for(var i in columns)
                                {
                                    tmp = $.trim(columns[i]);
                                    if(tmp.length)
                                    {
                                        tmp = tmp.split('|');
                                        if(1<tmp.length)
                                        {
											for(var j in tmp) tmp[j] = $.trim(tmp[j]);
                                            settings['columns'].push(
                                                {
                                                    'title' : tmp.shift(),
                                                    'data'  : tmp.join('|')
                                                }
											);
                                        }
                                    }
                                }

                                if(me.language.length) settings['language'] = {'url': me.language};
                                $('.'+me.name+' .dfield #'+me.name).html('<table style="width:100%" class="display"></table>');
                                try
                                {
                                    $.fn.dataTableExt.errMode = 'ignore';
                                    $('.'+me.name+' .dfield table').DataTable(settings);
                                } catch(err){ $('.'+me.name+' .dfield').html(''); console.log(err);}
                            }
						}
					);
				}
		}
	);