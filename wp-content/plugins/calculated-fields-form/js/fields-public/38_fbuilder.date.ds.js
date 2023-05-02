	$.fbuilder.controls['fdateds']=function(){};
	$.extend(
		$.fbuilder.controls['fdateds'].prototype,
		$.fbuilder.controls['fdate'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fdateds",
			first_time:true,
            excludeColumn: "",
            includeColumn: "",
            precedenceColumn: "exclude",
            invalidDatesDS:[],
            validDatesDS:[],
			show:function()
				{
					return $.fbuilder.controls['fdate'].prototype.show.call(this);
				},
			after_show : function()
				{
					var me = this;

                    function _compare_dates(d1, d2)
                    {
                        try
                        {
                            if(typeof d2 == 'string'){
                                var format = me.dformat.replace( /\//g, me.dseparator).replace(/y{4}/, 'yy');
                                d2 = $.datepicker.parseDate(format, d2);
                            }
                            return (
                                        d1.getDate() == d2.getDate() &&
                                        d1.getMonth() == d2.getMonth() &&
                                        d1.getFullYear() == d2.getFullYear()
                                  );
                        } catch (err){}
                        return false;
                    }

                    function _get_date(d, l, all)
                    {
                        var result = [];
                        all = all || false;
                        for(var i in l)
                        {
                            if(_compare_dates(d, l[i]))
                            {
                                result.push(l[i]);
                                if(!all) return result;
                            }
                        }
                        return result;
                    }


                    if(me.first_time)
                    {
                        me.first_time = false;
                        me._oldValidateDate = me._validateDate;
                        me._oldValidateTime = me._validateDate;

                        me._validateDate = function(d){
                            var me = this, tmp;

                            d = d || $('#'+me.name+'_date').datepicker('getDate');

                            if(!me._oldValidateDate(d)) return false;

                            if(me.validDatesDS.length)
                            {
                                tmp = _get_date(d, me.validDatesDS);
                                if(!tmp.length) return false;
                                if(me.precedenceColumn == 'include') return true;
                            }

                            tmp = _get_date(d, me.invalidDatesDS);
                            if(tmp.length && (!me.showTimepicker || !(tmp[0].getHours() && tmp[0].getMinutes()))) return false;

                            return true;
                        };

                        me._validateTime = function(){
                            var me = this, tmp, d = DATEOBJ(me.val());
                            if(!me._oldValidateTime()) return false;

                            tmp = _get_date(d, me.invalidDatesDS, true);
                            for(var i in tmp)
                            {
                                if(d.getHours() == tmp[i].getHours() && d.getMinutes() == tmp[i].getMinutes()) return false;
                            }
                            return true;
                        };
                    } // End first_time custom validations

					$.fbuilder.controls['fdate'].prototype.after_show.call(this);
					$.fbuilder.controls['datasource'].prototype.getData.call(this, function(data)
						{
							if(typeof data['error'] != 'undefined')
							{
								alert(data.error);
							}
							else
							{
                                me.invalidDatesDS = [];
                                me.validDatesDS = [];
								if(data.data.length)
								{
									for(var i in data.data)
                                    {
                                        if(me.excludeColumn in data.data[i])
                                        {
                                            me.invalidDatesDS.push(DATEOBJ(data.data[i][me.excludeColumn], me.dformat+(me.showTimepicker ? 'hh:ii' : '')));
                                        }
                                        if(me.includeColumn in data.data[i])
                                        {
                                            me.validDatesDS.push(data.data[i][me.includeColumn], me.dformat+(me.showTimepicker ? 'hh:ii' : ''));
                                        }
                                    }
								}
							}
							$('[id="'+me.name+'"]').trigger('cff-data-filled');
						}
					);
				},
			setVal : function(v, nochange)
				{
					this.defaultSelection = v;
					$.fbuilder.controls['fdate'].prototype.setVal.call(this, v, nochange);
				}
	});