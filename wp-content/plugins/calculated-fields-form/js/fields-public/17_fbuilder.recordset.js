	$.fbuilder.controls['frecordsetds']=function(){};
	$.extend(
		$.fbuilder.controls['frecordsetds'].prototype,
		$.fbuilder.controls['ffields'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"frecordsetds",
			records : [],
			show:function(){ return '<input id="'+this.name+'" name="'+this.name+'" class="cpcff-recordset '+this.name+'" type="hidden" />'; },
			after_show : function(){
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
							me.records = [];
							if(data.data.length)
							{
								me.records = data.data.slice(0);
							}
						}
						$('#'+me.name).trigger('change').trigger('cff-data-filled');
					}
				);
			},
			reload:function(){this.after_show();},
			val: function(){
				var e = $('[id="'+this.name+'"]');
				if(e.length)
				{
					return this.records;
				}
				return [];
			},
            setRecords: function(records){
				try
                {
					var record;
					this.records = [];
					if(Array.isArray(records)){
						for(var i in records){
							record = JSON.parse(JSON.stringify(records[i]));
							if(typeof record === 'object' && record !== null)
							{
								this.records.push(record);
							}
						}
					}
					$('#'+this.name).trigger('change');
                }catch(err){console.log(err);}
			},
			addRecord: function(record)
            {
                try
                {
                    record = JSON.parse(JSON.stringify(record));
                    if(typeof record === 'object' && record !== null)
                    {
                        this.records.push(record);
                        $('#'+this.name).trigger('change');
                    }
                }catch(err){console.log(err);}
            },
            updateRecord: function(record, condition)
            {
                try
                {
                    record = JSON.parse(JSON.stringify(record));
                    condition = JSON.parse(JSON.stringify(condition));

                    var flag, change = false;
                    if(
                        typeof record === 'object' && record !== null &&
                        typeof condition === 'object' && condition !== null
                    )
                    {
                        for(var i in this.records)
                        {
                            flag = true
                            for(var j in condition)
                            {
                                if(
                                    !(j in this.records[i]) ||
                                    this.records[i][j] !== condition[j]
)
                                {
                                    flag = false; break;
                                }
                            }
                            if(flag)
                            {
                                $.extend(true, this.records[i], record);
                                change = true;
                            }
                        }
                        if(change) $('#'+this.name).trigger('change');
                    }
                }catch(err){console.log(err);}
            },
            deleteRecord: function(condition)
            {
                try
                {
                    condition = JSON.parse(JSON.stringify(condition));
                    var l = this.records.length;
                    if(typeof condition === 'object' && condition !== null)
                    {
                        this.records = this.records.filter(function(item){
                            for(var j in condition)
                                if(!(j in item) || item[j] !== condition[j]) return true;
                            return false;
                        });
                        if(l != this.records.length) $('#'+this.name).trigger('change');
                    }
                }catch(err){console.log(err);}
            }
	});