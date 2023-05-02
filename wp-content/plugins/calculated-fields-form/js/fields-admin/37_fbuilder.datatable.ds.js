	$.fbuilder.typeList.push(
		{
			id:"fdatatableds",
			name:"DataTable DS",
			control_category:20
		}
	);
	$.fbuilder.controls['fdatatableds'] = function(){ this.init(); };
	$.extend(
		$.fbuilder.controls['fdatatableds'].prototype,
		$.fbuilder.controls['ffields'].prototype,
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
            init:function()
				{
					$.extend(true, this, new $.fbuilder.controls['datasource']() );
				},
			display:function()
				{
                    var table = '<table border="1" style="width:100%;min-height:30px;border:2px solid rgba(222,222,222,.75);"><thead><tr>', columns = this.columns;
                    columns = columns.split(/[\n\r]/);
                    for(var i in columns)
                    {
                        if($.trim(columns[i]).length) table+='<th>'+cff_esc_attr(columns[i])+'</th>';
                    }
                    table += '</tr></thead></table>';
					return '<div class="fields '+this.name+' '+this.ftype+' fhtml" id="field'+this.form_identifier+'-'+this.index+'" title="'+this.name+'"><div class="arrow ui-icon ui-icon-play "></div><div title="Delete" class="remove ui-icon ui-icon-trash "></div><div title="Duplicate" class="copy ui-icon ui-icon-copy "></div><label>'+this.title+'</label><div class="dfield">'+table+'</div><div class="clearer" /></div>';
				},
			editItemEvents:function()
				{
                    var f = function(el){return el.is(":checked");},
                        evt = [
							{s:"#sColumns",e:"change keyup", l:"columns"},
							{s:"#sUpload_size",e:"change keyup", l:"upload_size"},
							{s:"#sThumbWidth",e:"change keyup", l:"thumb_width"},
							{s:"#sHTML",e:"click", l:"html",f:f},
							{s:"#sAutowidth",e:"click", l:"autowidth",f:f},
							{s:"#sPaging",e:"click", l:"paging",f:f},
							{s:"#sLengthchange",e:"click", l:"lengthchange",f:f},
							{s:"#sOrdering",e:"click", l:"ordering",f:f},
							{s:"#sScrollx",e:"click", l:"scrollx",f:f},
							{s:"#sScrolly",e:"change keyup", l:"scrolly"},
							{s:"#sSearching",e:"click", l:"searching",f:f},
							{s:"#sLanguage",e:"change keyup", l:"language"}
						];
					$.fbuilder.controls['ffields'].prototype.editItemEvents.call(this,evt);
					this.editItemEventsDS();
				},
			showAllSettings:function()
				{
                    return this.showFieldType()+this.showTitle()+this.showName()+this.showDataSource( ['recordset'], 'custom' )+this.showDataTableAttributes()+this.showCsslayout();
				},
            showDataTableAttributes:function()
                {
                    var str = '', o = this;
                    str += '<div><label>column title|record attribute</label><textarea name="sColumns" id="sColumns" class="large" rows="6">'+cff_esc_attr(o.columns)+'</textarea><br><i>One pair <b>column title|record attribute</b> per line.<br>Use <b>| symbol</b> as separartor between column title and record attribute</i></div>'+

                    '<label><input type="checkbox" name="sHTML" id="sHTML" '+((o.html)?"checked":"")+'>Rendering HTML cells</label>'+

                    '<label><input type="checkbox" name="sAutowidth" id="sAutowidth" '+((o.autowidth)?"checked":"")+'>Auto width columns</label>'+

                    '<label><input type="checkbox" name="sPaging" id="sPaging" '+((o.paging)?"checked":"")+'>Multipage table</label>'+

                    '<label><input type="checkbox" name="sLengthchange" id="sLengthchange" '+((o.lengthchange)?"checked":"")+'>When pagination is enabled, display option to change the number of records per page</label>'+

                    '<label><input type="checkbox" name="sOrdering" id="sOrdering" '+((o.ordering)?"checked":"")+'>Enable ordering of columns</label>'+

                    '<label><input type="checkbox" name="sScrollx" id="sScrollx" '+((o.scrollx)?"checked":"")+'>Show horizontal scrolling in too wide tables</label>'+

                    '<div><label>Table height, numbers only (optional)</label><input type="text" name="sScrolly" id="sScrolly" class="large" value="'+cff_esc_attr(o.scrolly)+'"></div>'+

                    '<label><input type="checkbox" name="sSearching" id="sSearching" '+((o.searching)?"checked":"")+'>Include search box control in the table</label>'+

                    '<div><label>URL to a JSON language file (optional)</label><input type="text" name="sLanguage" id="sLanguage" class="large" value="'+cff_esc_attr(o.language)+'"><br><i><a href="https://datatables.net/plug-ins/i18n/" target="_blank">Language files</a></i></div>';

                    return str;
                }
	});