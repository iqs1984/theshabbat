	$.fbuilder.typeList.push(
		{
			id:"fdateds",
			name:"Date Time DS",
			control_category:20
		}
	);
	$.fbuilder.controls['fdateds'] = function(){ this.init(); };
	$.extend(
		$.fbuilder.controls['fdateds'].prototype,
		$.fbuilder.controls['fdate'].prototype,
		{
			ftype:"fdateds",
            excludeColumn: "",
            includeColumn: "",
            precedenceColumn: "exclude",

            init : function()
				{
					$.extend(true, this, new $.fbuilder.controls['datasource']());
				},
			display:function()
				{
					return $.fbuilder.controls['fdate'].prototype.display.call(this);
				},
			editItemEvents:function()
				{
                    var evt = [
							{s:"#sExcludeColumn",e:"change keyup", l:"excludeColumn"},
							{s:"#sIncludeColumn",e:"change keyup", l:"includeColumn"},
							{s:"[name='sPrecedenceColumn']",e:"change", l:"precedenceColumn",f:function(el){
                                return $("[name='sPrecedenceColumn']:checked").val();
                            }}
						];
					$.fbuilder.controls['fdate'].prototype.editItemEvents.call(this);
					this.editItemEventsDS();
                    $.fbuilder.controls['ffields'].prototype.editItemEvents.call(this, evt);
				},
			showAllSettings:function()
				{
					return $.fbuilder.controls['fdate'].prototype.showAllSettings.call(this)+this.showDataSource(['recordset'], 'custom');
				},
            showExtraDataSourceFields:function()
                {
                    var o = this;

                    return '<hr /><div><label>Column for Date/Time to exclude</label><input type="text" name="sExcludeColumn" id="sExcludeColumn" class="large" value="'+cff_esc_attr(o.excludeColumn)+'" /></div>'+

                    '<div><label>Column for Date/Time to include</label><input type="text" name="sIncludeColumn" id="sIncludeColumn" class="large" value="'+cff_esc_attr(o.includeColumn)+'" /></div>'+

                    '<div>'+
                    '<label>For date/time in both exclude/include columns</label>'+
                    '<label class="column width50"><input name="sPrecedenceColumn" type="radio" value="exclude" '+(o.precedenceColumn == 'exclude' ? 'CHECKED' : '')+' /> Exclude</label>'+
                    '<label class="column width50"><input name="sPrecedenceColumn" type="radio" value="include" '+(o.precedenceColumn == 'include' ? 'CHECKED' : '')+' /> Include</label>'+
                    '<div class="clearer"></div>'+
                    '</div>';
                },
			showPredefined : function()
				{
					return '';
				}
	});