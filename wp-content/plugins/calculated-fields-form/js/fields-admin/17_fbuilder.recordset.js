	$.fbuilder.typeList.push(
		{
			id:"frecordsetds",
			name:"RecordSet DS",
			control_category:20
		}
	);
	$.fbuilder.controls[ 'frecordsetds' ] = function(){ this.init(); };
	$.extend(
		$.fbuilder.controls[ 'frecordsetds' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			ftype:"frecordsetds",
			init : function()
				{
					$.extend(true, this, new $.fbuilder.controls[ 'datasource' ]() );
				},
			display:function()
				{
					return '<div class="fields '+this.name+' '+this.ftype+' fhtml" id="field'+this.form_identifier+'-'+this.index+'" title="'+this.name+'"><div class="arrow ui-icon ui-icon-play "></div><div title="Delete" class="remove ui-icon ui-icon-trash "></div><div title="Duplicate" class="copy ui-icon ui-icon-copy "></div><label>RecordSet ['+this.name+']</label><div class="clearer" /></div>';
				},
			editItemEvents:function()
				{
					this.editItemEventsDS();
				},

			showAllSettings:function()
				{
					return this.showFieldType()+this.showName()+this.showDataSource( [ 'database', 'csv', 'json', 'messages', 'acf' ], 'recordset' );
				}
		}
	);