	$.fbuilder.typeList.push(
		{
			id:"fhtmlds",
			name:"HTML Cont. DS",
			control_category:20
		}
	);
	$.fbuilder.controls['fhtmlds'] = function(){ this.init(); };
	$.extend(
		$.fbuilder.controls['fhtmlds'].prototype,
		$.fbuilder.controls['fhtml'].prototype,
		{
			ftype:"fhtmlds",
            init:function()
				{
					$.extend(true, this, new $.fbuilder.controls['datasource']() );
				},
			display:function()
				{
                    return $.fbuilder.controls[ 'fhtml' ].prototype.display.call(this);
				},
			editItemEvents:function(evt)
				{
                    $.fbuilder.controls['ffields'].prototype.editItemEvents.call(this,evt);
					this.editItemEventsDS();
				},
			showAllSettings:function()
				{
                    return this.showFieldType()+this.showName()+this.showCsslayout()+this.showDataSource( [ 'database', 'recordset', 'posttype', 'taxonomy', 'user' ], 'single' );
				}
	});