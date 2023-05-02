	$.fbuilder.controls[ 'datasource' ] = function(){
		this.list = {
			'json' : {
				title: 'JSON',
				jsonData: {
					source : ''
				},
				show : function()
					{
						var o = this.jsonData;

						return '<label>JSON URL or Variable Name:</label><input type="text" name="sJSONSource" id="sJSONSource" value="'+cff_esc_attr(o.source)+'" class="large" />';
					},
				events : function()
					{
						$( '#sJSONSource' ).bind( 'change keyup', { obj: this }, function( e )
							{
								e.data.obj.jsonData.source = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
					}
			},
			'recordset' : {
				title: 'Recordset',
				recordsetData: {
					recordset 	: '',
					value 		: '',
					text  		: '',
					where 		: ''
				},
				show : function( type, ancestor )
                /* Type can be 'pair', 'single', 'custom', 'pair' allows define the property for text and value, the 'single' only the value, 'custom' does not include the attributes for text or value*/
					{
						var items = ancestor.fBuild.getItems(),
							itemName = '',
							rs = this.recordsetData,
							str = '<label>Recordset:</label><select id="sRecordset" name="sRecordset" class="large">'+
							'<option value="">Select a recordset field</option>';

						items = items.filter(function(item){ return item.ftype == "frecordsetds"; });
						for( var i = 0, h = items.length; i<h; i++ )
						{
							itemName = items[i].name;
							str += '<option value="'+itemName+'" '+(( itemName == rs.recordset ) ? 'SELECTED' : '')+'>'+itemName+'</option>';
						}
						str +='</select>';
                        if(type != 'custom')
                        {
                            str +='<label>Property for values:</label><input type="text" class="large" name="sRecordValue" id="sRecordValue" value="'+cff_esc_attr(rs.value)+'" />';

                            if( type == 'pair' )
                            {
                                str += '<label>Property for texts:</label><input type="text" class="large" name="sRecordText" id="sRecordText" value="'+cff_esc_attr(rs.text)+'" />';
                            }
                        }
						str += '<label>Condition:</label><input type="text" class="large" name="sRecordWhere" id="sRecordWhere" value="'+cff_esc_attr(rs.where)+'" />';

						return str;
					},
				events : function()
					{
						$( '#sRecordset' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.recordsetData.recordset = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sRecordValue' ).bind( 'change keyup', { obj: this }, function( e )
							{
								e.data.obj.recordsetData.value = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sRecordText' ).bind( 'change keyup', { obj: this }, function( e )
							{
								e.data.obj.recordsetData.text = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sRecordWhere' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.recordsetData.where = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
					}
			},
			'database' : {
				title : 'Database',
				databaseData: {
					connection: 'structure',
					dns: '',
					host: '',
					engine: 'mysql',
					user: '',
					pass: '',
					database: ''
				},
				queryData : {
					active: 'structure',
					query: '',
					value: '',
					text: '',
					table: '',
					where: '',
					orderby: '',
					limit: ''
				},
				show : function( type ) // Type can be 'pair', 'single', or 'recordset', for 'pair' are shown options for text and value, for 'single' is shown only the option for value, the recordset only display the custom query options
					{
						var str = '<label>Database Connection</label>'+
						'<label class="column"><input type="radio" name="sConnectionType" id="sConnectionType" value="structure" '+( ( this.databaseData.connection == 'structure' ) ? 'CHECKED' : '' )+' /> Connection Components&nbsp;&nbsp; </label>'+
						'<label class="column"><input type="radio" name="sConnectionType" id="sConnectionType" value="dns" '+( ( this.databaseData.connection == 'dns' ) ? 'CHECKED' : '' )+' /> DNS</label>'+
						'<div class="clearer" />'+
						'<div id="databaseConnection_dns" class="connectionType" style="display:'+((this.databaseData.connection == 'dns') ? 'block' : 'none')+';">'+
						'<label>Dns:</label><input type="text" class="large" name="sDns" id="sDns" value="'+cff_esc_attr(this.databaseData.dns)+'" />'+
						'</div>'+
						'<div id="databaseConnection_structure" class="connectionType" style="display:'+((this.databaseData.connection == 'structure') ? 'block' : 'none')+';">'+
						'<label>Host:</label><input type="text" class="large" name="sHost" id="sHost" value="'+cff_esc_attr(this.databaseData.host)+'" />'+
						'<label>Engine:</label><input type="text" class="large" name="sEngine" id="sEngine" value="'+cff_esc_attr(this.databaseData.engine)+'" />'+
						'<label>Database:</label><input type="text" class="large" name="sDatabase" id="sDatabase" value="'+cff_esc_attr(this.databaseData.database)+'" />'+
						'</div>'+
						'<label>Username:</label><input type="text" class="large" name="sUser" id="sUser" value="'+cff_esc_attr(this.databaseData.user)+'" />'+
						'<label>Password:</label><input type="text" class="large" name="sPass" id="sPass" value="'+cff_esc_attr(this.databaseData.pass)+'" />'+
						'<div><input type="button" class="button" name="sTestConnection" id="sTestConnection" value="Test Connection" style="float:right;margin:5px 0;" /></div><div class="clearer" />';

						if( type != 'recordset' )
						{
							str += '<label class="column"><input type="radio" name="sQueryType" id="sQueryType" value="structure" '+( ( this.queryData.active == 'structure' ) ? 'CHECKED' : '' )+' /> Query Structure&nbsp;&nbsp;</label><label class="column"><input type="radio" name="sQueryType" id="sQueryType" value="query" '+( ( this.queryData.active == 'query' ) ? 'CHECKED' : '' )+' /> Custom Query</label><div class="clearer" />';
						}
						else
						{
							this.queryData.active = 'query';
						}
						str += '<div id="databaseQueryData_structure" class="queryType" style="display:'+( ( this.queryData.active == 'structure' ) ? 'block' : 'none' )+';" >'+
						'<label>Column for values:</label><input type="text" class="large" name="sQueryValue" id="sQueryValue" value="'+cff_esc_attr(this.queryData.value)+'" />';
						if( type == 'pair' )
						{
							str += '<label>Column for texts:</label><input type="text" class="large" name="sQueryText" id="sQueryText" value="'+cff_esc_attr(this.queryData.text)+'" />';
						}
						str += '<label>Table name:</label><input type="text" class="large" name="sQueryTable" id="sQueryTable" value="'+cff_esc_attr(this.queryData.table)+'" />'+
						'<label>Condition:</label><input type="text" class="large" name="sQueryWhere" id="sQueryWhere" value="'+cff_esc_attr(this.queryData.where)+'" />'+
						'<label>Order by:</label><input type="text" class="large" name="sQueryOrderBy" id="sQueryOrderBy" value="'+cff_esc_attr(this.queryData.orderby)+'" />'+
						'<label>Limit:</label><input type="text" class="large" name="sQueryLimit" id="sQueryLimit" value="'+cff_esc_attr(this.queryData.limit)+'" />'+
						'</div>'+
						'<div id="databaseQueryData_query" class="queryType" style="display:'+( ( this.queryData.active == 'query' ) ? 'block' : 'none' )+';" >'+
						'<label>Type the query:</label><input type="text" class="large" name="sCustomQuery" id="sCustomQuery" value="'+cff_esc_attr(this.queryData.query)+'" />'+
						'</div>'+
						'<div><input type="button" class="button" name="sTestQuery" id="sTestQuery" value="Test Query" style="float:right;margin:5px 0;" /></div><div class="clearer" />';
						return str;
					},
				events : function()
					{
						$( '[name="sConnectionType"]' ).bind( "click", { obj: this }, function( e )
							{
								$( '.connectionType' ).hide();
								$( '#databaseConnection_'+e.target.value ).show();
								e.data.obj.databaseData.connection = $.trim( $(this).val() );
							});

						$( '#sHost' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.host = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sEngine' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.engine = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sUser' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.user = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sPass' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.pass = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sDatabase' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.database = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sDns' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.databaseData.dns = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '[name="sQueryType"]' ).bind( "click", { obj: this }, function( e )
							{
								$( '.queryType' ).hide();
								$( '#databaseQueryData_'+e.target.value ).show();
								e.data.obj.queryData.active = $.trim( $(this).val() );
							});
						$( '#sQueryValue' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.value = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sQueryText' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.text = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sQueryTable' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.table = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sQueryWhere' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.where = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sQueryOrderBy' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.orderby = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sQueryLimit' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.limit = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCustomQuery' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.queryData.query = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sTestConnection' ).bind( 'click', { obj: this }, function( e )
							{
								var form_url = $( this ).parents( 'form' ).attr( 'action' );
								$.ajax(
									{
										url : form_url,
										cache : false,
										data : $.extend( { cffaction: 'test_db_connection' }, e.data.obj.databaseData ),
										success : function( data ){
											alert( data );
										}
									}
								);
							});
						$( '#sTestQuery' ).bind( 'click', { obj: this }, function( e )
							{
								var form_url = $( this ).parents( 'form' ).attr( 'action' );
								$.ajax(
									{
										url : form_url,
										cache : false,
										data : $.extend( { cffaction: 'test_db_query' }, e.data.obj.databaseData, e.data.obj.queryData ),
										success : function( data ){
											alert( data );
										}
									}
								);
							});
					}
			},
            'messages' : {
                title : 'Forms submissions',
                messagesData : {
                    forms  : '', // Forms ids separated by comma
                    submissions : '', // Submissions ids separated by comma
                    logged : 0,
                    paid   : 0,
                    unpaid : 0,
                    fields : '', // Fields names separated by comma
                    from   : '',
                    to     : '',
                    limit  : '',
                    conditions:''
                },
                show : function()
                {
                    var messagesData = this.messagesData,
                        str;

                    str = '<label>Forms Ids</label><div class="clearer"><input type="text" id="sForms" name="sForms" class="large" value="'+cff_esc_attr(messagesData.forms)+'"><i>Enter the form ids separated by commas</i></div>'+
                    '<label>Submissions Ids</label><div class="clearer"><input type="text" id="sSubmissions" name="sSubmissions" class="large" value="'+cff_esc_attr(messagesData.submissions)+'"><i>Enter the submissions ids separated by commas</i></div>'+
                    '<label><input type="checkbox" id="sLogged" name="sLogged" '+(messagesData.logged ? 'CHECKED' : '')+'> from logged user<br><i>Tick the box to load the submissions of logged user</i></label>'+
                    '<label><input type="checkbox" id="sPaid" name="sPaid" '+(messagesData.paid ? 'CHECKED' : '')+'> paid only<br><i>Tick the box to load the paid submissions</i></label>'+
                    '<label><input type="checkbox" id="sUnpaid" name="sUnpaid" '+(messagesData.unpaid ? 'CHECKED' : '')+'> unpaid only<br><i>Tick the box to load the unpaid submissions</i></label>'+
                    '<label>Fields Names</label><div class="clearer"><input type="text" id="sFields" name="sFields" class="large" value="'+cff_esc_attr(messagesData.fields)+'"><i>Enter the fields\' names to read separated by commas. Ex. fieldname1,fieldname2,fieldname3<br>If the attribute is left empty, it returns all fields</i></div>'+
                    '<label>From (yyyy-mm-dd or fieldname#)</label><div class="clearer"><input type="text" id="sFrom" name="sFrom" value="'+cff_esc_attr(messagesData.from)+'" pattern="^((\\d{4}-\\d{2}-\\d{2})|(fieldname\\d+))$" placeholder="yyyy-mm-dd or fieldname#" class="large"></div>'+
                    '<label>To (yyyy-mm-dd or fieldname#)</label><div class="clearer"><input type="text" id="sTo" name="sTo" value="'+cff_esc_attr(messagesData.to)+'" pattern="^((\\d{4}-\\d{2}-\\d{2})|(fieldname\\d+))$" placeholder="yyyy-mm-dd or fieldname#" class="large"></div>'+
                    '<label>Limit</label><div class="clearer"><input type="number" id="sLimit" name="sLimit" value="'+cff_esc_attr(messagesData.limit)+'"></div>'+
                    '<label>Filter by fields</label>fieldname#|value to compare<br>'+
                    '<textarea name="sConditions" id="sConditions" class="large" rows="6">'+cff_esc_attr(messagesData.conditions)+'</textarea>'+
                    '<i>One pair <b>fieldname#|value</b> per line.<br>Use <b>"|" symbol</b> as separartor between field name and value</i>'+
                    '<div class="choicesSet">Records are sorted by their ids in decreasing order. Each record is an object with the value of the fields entered through the fields attribute (the numeric components of the fields names used as their keys). They also include the "date" key, with the date of the submission, the "form" key with the form id, the "id" key with the submission id, and the "paid/unpaid" keys with value 0 or 1 (true or false) if the record corresponds to a paid or unpaid submission.</div>';

                    return str;
                },
                events : function()
                {
                    $('#sForms').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.forms = this.value.replace(/[^\d\,]/g, '').replace(/\,+/g, ',').replace(/^\,/, '').replace(/\,$/,'');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sSubmissions').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.submissions = this.value.replace(/[^\d\,]/g, '').replace(/\,+/g, ',').replace(/^\,/, '').replace(/\,$/,'');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sLogged').bind('click', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.logged = $(this).is(':checked');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sPaid').bind('click', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.paid = $(this).is(':checked');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sUnpaid').bind('click', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.unpaid = $(this).is(':checked');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sFields').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.fields = this.value.match(/fieldname\d+/ig).join(',').toLowerCase();
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sFrom').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.from = this.value;
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sTo').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.to  = this.value;
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sFrom').bind('change', {obj:this}, function(e)
                    {
                        this.reportValidity();
                    });
                    $('#sTo').bind('change', {obj:this}, function(e)
                    {
                        this.reportValidity();
                    });
                    $('#sLimit').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.messagesData.limit = this.value;
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sConditions').bind('keyup change', {obj:this}, function(e){
                        e.data.obj.messagesData.conditions = this.value;
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                }
            },
			'acf' : {
                title : 'Advanced Custom Fields',
                acfData : {
					field_name : '',
					read_from  : 'post',
					src_id	   : ''
                },
                show : function()
                {
                    var acfData = this.acfData,
                        str;

					str = '<div style="background-color: rgb(223, 239, 255); border: 1px solid rgb(194, 215, 239); padding: 5px; margin: 5px; text-align: left;">The Advanced Custom Fields data source, requires the Advanced Custom Fields plugin.</div>'+
					'<label>Field name</label><div class="clearer"><input type="text" id="sFieldName" name="sFieldName" class="large" value="'+cff_esc_attr(acfData.field_name)+'"><i>Enter ACF field name</i></div>'+

					'<label style="margin-top:10px;">Read field from</label><div class="clearer">'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="post" '+( acfData.read_from == 'post' ? 'CHECKED' : '')+'> Post/page</label>'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="taxonomy" '+( acfData.read_from == 'taxonomy' ? 'CHECKED' : '')+'> Taxonomy</label>'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="comment" '+( acfData.read_from == 'comment' ? 'CHECKED' : '')+'> Comment</label>'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="user" '+( acfData.read_from == 'user' ? 'CHECKED' : '')+'> User</label>'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="widget" '+( acfData.read_from == 'widget' ? 'CHECKED' : '')+'> Widget</label>'+
					'<label class="column width50"><input type="radio" name="sReadFrom" value="option" '+( acfData.read_from == 'option' ? 'CHECKED' : '')+'> Option</label><div class="clearer"></div></div>'+

					'<div class="clearer" style="margin-top:10px;display:'+(acfData.read_from == 'option' ? 'none' : 'block')+';"><label>Id of post/page, term, user, comment, or widget</label><div class="clearer"><input type="text" id="sSRCId" name="sSRCId" class="large" value="'+cff_esc_attr(acfData.src_id)+'"></div></div>';

                    return str;
                },
                events : function()
                {
                    $('#sFieldName').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.acfData.field_name = this.value.replace(/^\s+/, '').replace(/\s+$/, '');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('[name="sReadFrom"]').bind('click', {obj:this}, function(e)
                    {
                        e.data.obj.acfData.read_from = $('[name="sReadFrom"]:checked').val();
						$.fbuilder.reloadItems({'field':e.data.obj});
                    });
                    $('#sSRCId').bind('keyup change', {obj:this}, function(e)
                    {
                        e.data.obj.acfData.src_id = this.value.replace(/^\s+/, '').replace(/\s+$/, '');
                        $.fbuilder.reloadItems({'field':e.data.obj});
                    });
                }
            },
			'csv' : {
				title : 'CSV',
				csvData : {
					text : 0,
					value : 0,
					file : '',
					type : 'local',
					where: '',
					fields : [],
					headline : false,
					delimiter : 'character',
					character : ',',
					rows : []
				},
				show : function( type )
					{

						var str = '<label>CSV Import</label>',
							optionsTexts  = '',
							optionsValues = '',
							csvData = this.csvData,
							text =  csvData.text;

						if( typeof text == 'string') text = [ text ];

						for( var index in csvData.fields )
						{

							optionsTexts += '<option value="'+cff_esc_attr(index)+'" '+( ( $.inArray(index, text)  != -1 ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(csvData.fields[ index ])+'</option>';
							optionsValues += '<option value="'+cff_esc_attr(index)+'" '+( ( index == csvData.value ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(csvData.fields[ index ])+'</option>';
						}

						str += '<label>Select CSV file:</label>'+
						'<label class="column width50"><input type="radio" name="sCSVFrom" value="local" '+((csvData.type == 'local') ? 'checked' : '' )+' /> from local file</label>'+
						'<label class="column width50"><input type="radio" name="sCSVFrom" value="url" '+((csvData.type == 'url') ? 'checked' : '' )+' /> from URL</label><div class="clearer" style="margin-bottom:10px;"></div>'+
						'<label><input type="'+(( csvData.type == 'local' ) ? 'file' : 'text')+'" class="large" name="sCSVLocation" id="sCSVLocation" value="'+cff_esc_attr(csvData.file)+'" placeholder="'+(( csvData.type == 'local' ) ? 'file' : 'url' )+'" /></label>'+
						'<label>Use headline: <input type="checkbox" name="sCSVUseHeadline" id="sCSVUseHeadline" '+( ( csvData.headline ) ? 'CHECKED' : '' )+' /></label>'+
						'<label>Delimiter:</label>'+
						'<label class="column width30"><input type="radio" name="sCSVDelimiter" id="sCSVDelimiter" value="tabulator" '+( ( csvData.delimiter == 'tabulator' ) ? 'CHECKED' : '' )+' /> Tabulator </label><label class="column width30"><input type="radio" name="sCSVDelimiter" id="sCSVDelimiter" value="character" '+( ( csvData.delimiter == 'character' ) ? 'CHECKED' : '' )+' /> Character </label><label class="column width30"><input type="text" class="large" name="sCSVCharacter" id="sCSVCharacter" value="'+cff_esc_attr(csvData.character)+'" /></label>'+
						'<div class="clearer"><input type="button" class="button" name="sCSVImport" id="sCSVImport" value="Import CSV" style="margin:5px 0;" /></div><div style="clear:both;"></div>';
						if( type != 'recordset' )
						{
							str += '<label>Select column for texts:</label><select class="large" name="sCSVTexts" id="sCSVTexts">'+optionsTexts+'</select>'+
							'<label>Select column for values:</abel><select class="large" name="sCSVValues" id="sCSVValues">'+optionsValues+'</select>';
						}
						else
						{
							this.isRecordset = true;
							str += '<label>Select columns:</label><select class="large" name="sCSVTexts" id="sCSVTexts" multiple size="5">'+optionsTexts+'</select>'+
							'<label>Select column for filtering:</label><select class="large" name="sCSVValues" id="sCSVValues">'+optionsValues+'</select>';
						}
						str += '<label>Where the value is equal to:</label><input type="text" class="large" name="sCSVWhere" id="sCSVWhere" value="'+cff_esc_attr(csvData.where)+'" />';
						return str;
					},
				events : function()
					{
						$( '[name="sCSVFrom"]' ).bind( 'click', { obj: this }, function( e )
							{
								e.data.obj.csvData.type = $( this ).val();
								var attr = {'type' : 'file', 'placeholder' : 'file' };
								if( e.data.obj.csvData.type == 'url' ) attr = {'type' : 'text', 'placeholder' : 'url' };
								try{ $( '#sCSVLocation' ).attr( attr ).val( '' );}
								catch( $err ){}
							});
						$( '#sCSVLocation' ).bind( 'change blur', { obj: this}, function( e )
							{
								e.data.obj.csvData.file = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVWhere' ).bind( 'keyup change', { obj: this}, function( e )
							{
								e.data.obj.csvData.where = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVUseHeadline' ).bind( 'click', { obj: this}, function( e )
							{
								e.data.obj.csvData.headline = $( this ).is( ':checked' );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVTexts' ).bind( 'change', { obj: this}, function( e )
							{
								e.data.obj.csvData.text = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVValues' ).bind( 'change', { obj: this}, function( e )
							{
								e.data.obj.csvData.value = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '[name="sCSVDelimiter"]' ).bind( 'click', { obj: this}, function( e )
							{
								e.data.obj.csvData.delimiter = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVCharacter' ).bind( 'change keyup', { obj: this}, function( e )
							{
								e.data.obj.csvData.character = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sCSVImport' ).bind( 'click', { obj: this}, function( e )
							{
								e.data.obj.csvData.fields = [];
								e.data.obj.csvData.rows = [];
								e.data.obj.csvData.text = 0;
								e.data.obj.csvData.value = 0;

								var config = {
									'header' : e.data.obj.csvData.headline,
									'delimiter' : ( ( e.data.obj.csvData.delimiter != 'tabulator' ) ? e.data.obj.csvData.character : '' ),
									'character' : e.data.obj.csvData.character
								};

								if( e.data.obj.csvData.type == 'url' )
								{
									var file = e.data.obj.csvData.file;
									if( !/^\s*$/.test( file ) )
									{
										var form_url = $( this ).parents( 'form' ).attr( 'action' );

										config[ 'cffaction' ] = 'get_csv_headers';
										config[ 'file' ] = ('btoa' in window) ? btoa(file) : file;

										$.getJSON(
											form_url,
											config,
											function( data )
											{
												if(
													typeof data.data == 'object' &&
													typeof data.data.length != 'undefined'
												)
												{
													for( var i in data.data )
													{
														e.data.obj.csvData.fields[ i ] = data.data[ i ];
													}

													$.fbuilder.reloadItems({'field':e.data.obj});
													$( '#datasourceSettings' ).html( e.data.obj.show( ( typeof e.data.obj.isRecordset !== 'undefined' ) ? 'recordset' : ''  ) );
													e.data.obj.events();
												}
												else if( typeof data.error != 'undefined' )
												{
													alert( 'Error', data.error );
												}

											}
										);
									}
								}
								else
								{
									e.data.obj.csvData.file = '';
									config[ 'dynamicTyping' ] = false;
									config[ 'preview' ] = 0;
									var obj = {
										'config'   : config,
										'complete' : function( results, file, inputElem, event )
											{
												function setFields( c )
												{
													for ( var i = 0; i < c; i++ )
													{
														e.data.obj.csvData.fields.push( 'Field: '+i );
													}
												};

												if( results.errors.length == 0 )
												{
													if( typeof results.results.fields != 'undefined' )
													{
														e.data.obj.csvData.fields = results.results.fields;
														e.data.obj.csvData.text = e.data.obj.csvData.value = results.results.fields[ 0 ];
													}
													else if( typeof results.results.rows != 'undefined' )
													{
														if( results.results.rows.length )
														{
															setFields( results.results.rows[ 0 ].length );
														}
													}
													else if( typeof results.results != 'undefined' )
													{
														setFields( results.results[ 0 ].length );
													}

													e.data.obj.csvData.text = e.data.obj.csvData.value = 0;
													e.data.obj.csvData.rows = ( typeof results.results.rows != 'undefined' ) ? results.results.rows : results.results;
													e.data.obj.csvData.file = $( '#sCSVLocation' ).val();
													$.fbuilder.reloadItems({'field':e.data.obj});
													$( '#datasourceSettings' ).html( e.data.obj.show( ( typeof e.data.obj.isRecordset !== 'undefined' ) ? 'recordset' : ''  ) );
													e.data.obj.events();
												}
												else
												{
													alert( 'Error, checks the CSV file structure' );
												}
											}
									};
									$( '#sCSVLocation' ).parse( obj );
								}
							});
					}
			},
			'posttype' : {
				title : 'Post Type',
				posttypeData:{
					posttype : '',
					value 	 : 'ID',
					text 	 : 'post_title',
					last	 : '',
					id 		 : ''
				},
				loadPostTypes : function()
					{
						var me = this,
							e  = $( '#sPostType' ),
							form_url = e.parents( 'form' ).attr( 'action' );

						$.ajax(
							{
								dataType : 'json',
								url : form_url,
								cache : false,
								data : { cffaction: 'get_post_types' },
								success : function( data ){
									var opt = '',
										v,
										selected = ( me.posttypeData.posttype != '' ) ? me.posttypeData.posttype : Object.keys( data )[ 0 ];

									for( var index in data )
									{
										opt += '<option value="'+cff_esc_attr(index)+'" '+( ( index == selected ) ? 'SELECTED' : '')+' >'+cff_esc_attr(data[ index ])+'</option>';
									}

									e.html( opt ).change();
								}
							}
						);
					},
				show : function( type ) // Type can be 'pair' or 'single', for 'pair' are shown options for text and value, for 'single' is shown only the option for value
					{
						var str = '<label>Select Post Type</label>',
							columns = [ 'ID', 'post_title', 'post_excerpt', 'post_content' ],
							optionsValues = '',
							optionsTexts = '';

						for( var i in columns )
						{
							optionsValues += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.posttypeData.value == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
							optionsTexts += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.posttypeData.text == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
						}

						str += '<label>Post Type:</label><select class="large" name="sPostType" id="sPostType"></select>'+
						'<label>Attribute for values:</label><select class="large" name="sPostTypeValue" id="sPostTypeValue">'+optionsValues+'</select>';
						if( type == 'pair' )
						{
							this.posttypeData.id = '';
							str += '<label>Attribute for texts:</label><select class="large" name="sPostTypeText" id="sPostTypeText">'+optionsTexts+'</select>'+
							'<label>Display the last:</label><input type="text" class="large" name="sPostTypeLast" id="sPostTypeLast" value="'+cff_esc_attr(this.posttypeData.last)+'" />';
						}
						else
						{
							str += '<label>Type a post ID:</label><input class="large" name="sPostId" id="sPostId" type="text" value="'+cff_esc_attr(this.posttypeData.id)+'" /';
						}
						return str;
					},
				events : function()
					{
						$( '#sPostType' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.posttypeData.posttype = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sPostTypeText' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.posttypeData.text = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sPostTypeValue' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.posttypeData.value = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sPostTypeLast' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.posttypeData.last = $.trim( $( this ).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sPostId' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.posttypeData.id = $.trim( $( this ).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						this.loadPostTypes();
					}
			},
			'taxonomy' : {
				title : 'Taxonomy',
				taxonomyData:{
					taxonomy : '',
					value 	 : 'term_id',
					text 	 : 'name',
					id 		 : '',
					slug 	 : ''
				},
				loadTaxonomies : function()
					{
						var me = this,
							e  = $( '#sTaxonomy' ),
							form_url = e.parents( 'form' ).attr( 'action' );

						$.ajax(
							{
								dataType : 'json',
								url : form_url,
								cache : false,
								data : { cffaction: 'get_available_taxonomies' },
								success : function( data ){
									var opt = '',
										v,
										selected = ( me.taxonomyData.taxonomy != '' ) ? me.taxonomyData.taxonomy : Object.keys( data )[ 0 ];

									for( var index in data )
									{
										opt += '<option value="'+cff_esc_attr(index)+'" '+( ( index == selected ) ? 'SELECTED' : '')+' >'+cff_esc_attr(data[ index ].labels.name)+'</option>';
									}

									e.html( opt ).change();
								}
							}
						);
					},
				show : function( type ) // Type can be 'pair' or 'single', for 'pair' are shown options for text and value, for 'single' is shown only the option for value
					{
						var str = '<label>Select Taxonomy</label>',
							columns = [ 'term_id', 'name', 'slug' ],
							optionsValues = '',
							optionsTexts = '';

						str += '<label>Taxonomy:</label><select class="large" name="sTaxonomy" id="sTaxonomy"></select>';
						for( var i in columns )
						{
							optionsValues += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.taxonomyData.value == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
							optionsTexts += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.taxonomyData.text == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
						}

						str += '<label>Attribute for values:</label><select class="large" name="sTaxonomyValue" id="sTaxonomyValue">'+optionsValues+'</select>';
						if( type == 'pair' )
						{
							this.taxonomyData.id = '';
							this.taxonomyData.slug = '';
							str += '<label>Attribute for texts:</label><select class="large" name="sTaxonomyText" id="sTaxonomyText">'+optionsTexts+'</select>';
						}
						else
						{
							str += '<label>Type a term ID:</label><input class="large" name="sTermId" id="sTermId" type="text" value="'+cff_esc_attr(this.taxonomyData.id)+'" />'+
							'<label>or type a term slug:</label><input class="large" name="sTermSlug" id="sTermSlug" type="text" value="'+cff_esc_attr(this.taxonomyData.slug)+'" />';
						}
						return str;
					},
				events : function()
					{
						$( '#sTaxonomy' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.taxonomyData.taxonomy = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sTaxonomyText' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.taxonomyData.text = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sTaxonomyValue' ).bind( 'change', { obj: this }, function( e )
							{
								e.data.obj.taxonomyData.value = $( this ).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sTermId' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.taxonomyData.id = $.trim( $( this ).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sTermSlug' ).bind( 'keyup change', { obj: this }, function( e )
							{
								e.data.obj.taxonomyData.slug = $.trim( $( this ).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});

						this.loadTaxonomies();
					}
			},
			'user' : {
				title : 'User Data',
				userData : {
					logged  : false,
					text 	: 'user_nicename',
					value 	: 'ID',
					id 		: '',
					login 	: ''
				},
				show : function( type ) // Type can be 'pair' or 'single', for 'pair' are shown options for text and value, for 'single' is shown only the option for value
					{
						var str = '<label>Display for Users</label>',
							columns = [ 'ID', 'user_login', 'user_nicename', 'display_name', 'user_email' ],
							optionsValues = '',
							optionsTexts = '';

						for( var i in columns )
						{
							optionsValues += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.userData.value == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
							optionsTexts += '<option value="'+cff_esc_attr(columns[ i ])+'" '+( ( this.userData.text == columns[ i ] ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(columns[ i ])+'</option>';
						}

						str += '<label>Attribute for values:</label><select class="large" name="sUserValue" id="sUserValue">'+optionsValues+'</select>';
						if( type == 'pair' )
						{
							this.userData.logged = false;
							this.userData.id = '';
							this.userData.login = '';
							str += '<label>Attribute for texts:</label><select class="large" name="sUserText" id="sUserText">'+optionsTexts+'</select>';
						}
						else
						{
							this.userData.text = '';
							str += '<label>Display data of logged user: <input name="sUserLogged" id="sUserLogged" type="checkbox" '+( ( this.userData.logged ) ? 'CHECKED' : '' )+' /></label>'+
							'<label> or display data of user ID:</label><input class="large" name="sUserId" id="sUserId" type="text" value="'+cff_esc_attr(this.userData.id)+'" '+( ( this.userData.logged ) ? 'DISABLED' : '' )+' />'+
							'<label> or display data of user with user login:</label><input class="large" name="sUserLogin" id="sUserLogin" type="text" value="'+cff_esc_attr(this.userData.login)+'" '+( ( this.userData.logged ) ? 'DISABLED' : '' )+' />';
						}
						return str;
					},
				events : function()
					{
						$( '#sUserValue' ).bind( 'change', { obj : this }, function( e )
							{
								e.data.obj.userData.value = $(this).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sUserText' ).bind( 'change', { obj : this }, function( e )
							{
								e.data.obj.userData.text = $(this).val();
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sUserLogged' ).bind( 'click', { obj : this }, function( e )
							{
								var isChecked = $(this).is( ':checked' );
								e.data.obj.userData.logged = isChecked;
								$.fbuilder.reloadItems({'field':e.data.obj});
								$( '#sUserId' ).attr( 'disabled',  isChecked );
								$( '#sUserLogin' ).attr( 'disabled',  isChecked );
							});
						$( '#sUserId' ).bind( 'keyup change', { obj : this }, function( e )
							{
								e.data.obj.userData.id = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
						$( '#sUserLogin' ).bind( 'keyup change', { obj : this }, function( e )
							{
								e.data.obj.userData.login = $.trim( $(this).val() );
								$.fbuilder.reloadItems({'field':e.data.obj});
							});
					}
			}
		};
	};

	$.fbuilder.controls[ 'datasource' ].prototype = {
		isDataSource:true,
		active : '',
		editItemEventsDS : function()
			{
				for( var index in this.list )
				{
					this.list[ index ].events();
				}

				$( '#sDataSource' ).bind( 'change', { obj: this }, function( e )
					{
						e.data.obj.active = $(this).val();
						$.fbuilder.editItem( e.data.obj.index  );
						$.fbuilder.reloadItems({'field':e.data.obj});
					}
				);

				$.fbuilder.controls[ 'ffields' ].prototype.editItemEvents.call(
					this,
					[{s:"#sDefaultSelection",e:"change keyup", l:"defaultSelection"}]
				);
			},
		showDefaultSelection:function(){
			if(typeof this.defaultSelection != 'undefined' )
			{
				return '<div><label>Select by default</label><input type="text" id="sDefaultSelection" name="sDefaultSelection" value="'+cff_esc_attr(this.defaultSelection)+'" class="large" /><span style="font-style:italic;">Enter a fixed value, or the tag to another field: &lt;%fieldname#%&gt;, or the tag to a global javascript variable &lt;%varname%&gt;. For checkboxes, to display multiple choices selected, separate their values by the symbol: "|", for example: choice_a|choice_b</span></div>';
			}
			else return "";
		},
		showDataSource : function( list, type )
			{
				if( this.active == '' )
				{
					this.active = list[ 0 ];
				}

				var str = '<div class="datasourceSet"><label>Define Datasource</label><div><select class="large" name="sDataSource" id="sDataSource">';
				for( var i in list )
				{
					str += '<option value="'+cff_esc_attr(list[ i ])+'" '+( ( list[ i ] == this.active ) ? 'SELECTED' : '' )+' >'+cff_esc_attr(this.list[ list[ i ] ].title)+'</option>';
				}
				str += '</select></div><div id="datasourceSettings">'+this.list[ this.active ].show( type, this )+this.showDefaultSelection()+'</div>'+
				('firstChoice' in this ? this.firstChoice() : '')+
				('mergeValues' in this ? this.mergeValues() : '')+
				('attributeToSubmit' in this ? this.attributeToSubmit() : '')+
				('allowUntick' in this ? this.allowUntick() : '')+
				('multipleSelection' in this ? this.multipleSelection() : '')+
				('maxChoices' in this ? this.maxChoices() : '')+
                ('showExtraDataSourceFields' in this ? this.showExtraDataSourceFields() : '')+
				'</div>';
				return str;
			}
	};