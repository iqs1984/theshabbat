	$.fbuilder.typeList.push(
		{
			id:"fdropdownds",
			name:"Dropdown DS",
			control_category:20
		}
	);
	$.fbuilder.controls[ 'fdropdownds' ] = function(){ this.init(); };
	$.extend(
		$.fbuilder.controls[ 'fdropdownds' ].prototype,
		$.fbuilder.controls[ 'fdropdown' ].prototype,
		{
			ftype:"fdropdownds",
			defaultSelection:"",
			first_choice:false,
			first_choice_text:"",
			init : function()
				{
					this.choices = [];
					this.choicesVal = [];
					$.extend(true, this, new $.fbuilder.controls[ 'datasource' ]() );
				},
			display:function()
				{
					return $.fbuilder.controls[ 'fdropdown' ].prototype.display.call(this);
				},
			editItemEvents:function()
				{
					$.fbuilder.controls[ 'fdropdown' ].prototype.editItemEvents.call(this);
					this.editItemEventsDS();
					$.fbuilder.controls[ 'ffields' ].prototype.editItemEvents.call(this, [
						{s:"#sFirstChoiceText",e:"change keyup", l:"first_choice_text"},
						{s:'[name="sFirstChoice"]', e:"click", l:"first_choice", f: function(el){return el.is(':checked');}}
					]);
				},
			firstChoice:function()
				{
					return '<div class="choicesSet"><label><input type="checkbox" name="sFirstChoice" '+((this.first_choice) ? ' CHECKED ' : '')+'/> Includes an additional first choice.</label><br><label>First choice text:<input type="text" id="sFirstChoiceText" name="sFirstChoiceText" class="large" value="'+cff_esc_attr(this.first_choice_text)+'" /></label></div>';
				},
			showAllSettings:function()
				{
					return $.fbuilder.controls[ 'fdropdown' ].prototype.showAllSettings.call(this)+this.showDataSource( [ 'database', 'csv', 'recordset', 'posttype', 'taxonomy', 'user' ], 'pair' );
				},
			showChoiceIntance: function()
				{
					return '';
				}
		}
	);