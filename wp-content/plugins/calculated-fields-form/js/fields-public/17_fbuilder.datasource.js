	$.fbuilder.controls['datasource'] = function(){};
	$.fbuilder.controls['datasource'].prototype = {
		isDataSource:true,
		active : '',
		list : {
			'database'	: { cffaction : 'get_data_from_database' },
			'posttype'  : { cffaction : 'get_posts' },
			'taxonomy'	: { cffaction : 'get_taxonomies' },
			'user' 		: { cffaction : 'get_users' },
            'messages'  : { cffaction : 'get_submissions' },
			'acf'  		: { cffaction : 'get_acf' },
			'json' 		: {
				jsonData: {
					source : ''
				},
				getData : function(callback, parentObj)
				{
					var obj = { data : [] },
						d   = this.jsonData,
						v   = $.trim(d.source),
						populate = function(v){
							if(typeof v == 'object')
							{
								if('length' in v) obj.data = v;
								else if(v != null) obj.data.push(v);
							}
						};
					if(v.length)
					{
						if(v in window)
						{
							populate(window[v]);
							callback(obj);
						}
						else if(/^http(s)?:\/\//i.test(v))
						{
							v += ((v.indexOf('?') == -1) ? '?' : '&')+'callback=?';
							$.ajax(v.replace(/^http(s)?\:/i, ''), {success:function(data){
								populate(data);
								callback(obj);
							}});
						}
					}
					else callback(obj);
				}
			},
			'recordset'	: {
				recordsetData: {
					recordset 	: '',
					value 		: '',
					text  		: '',
					where 		: ''
				},
				getData : function(callback, parentObj)
					{
						var obj = { data : [] },
							d  	= this.recordsetData,
							fi 	= parentObj['form_identifier'],
							rs 	= $.trim(d.recordset),
							r, // For records
							w 	= $.trim(d.where),
							t 	= $.trim(d.text),
							v 	= $.trim(d.value),
							tmp, tmp2;

						if(rs != '')
						{
							r = $.fbuilder['forms'][fi].getItem(rs+fi).val();
							if(w != '') w = parentObj.parseVars(w);
							parentObj.replaceVariables([rs], {})

							for(var i in r)
							{
								if(w == '' || (function(o,w){
									w = w.replace(/\brecord\s*\[/gi, 'o[');
									try{
										return eval(w);
									}catch(err){return false;}
								})(r[i],w))
								{
									tmp = {};
									if(t != ''){
										if(typeof r[i][t] != 'undefined') tmp['text'] = r[i][t];
										else if(/^record\[/.test(t)){
											tmp2 = t.replace(/^record\[/, 'r['+i+'][');
											try{
												tmp['text'] = eval(tmp2);
											}catch(err){ tmp['text'] = ''; }
										}
									}
									if(v != ''){
										if(typeof r[i][v] != 'undefined') tmp['value'] = r[i][v];
										else if(/^record\[/.test(v)){
											tmp2 = v.replace(/^record\[/, 'r['+i+'][');
											try{
												tmp['value'] = eval(tmp2);
											}catch(err){ tmp['value'] = ''; }
										}
									}
                                    if(t == '' && v == '') tmp = r[i];
									obj.data.push(tmp);
								}
							}
							callback(obj);
						}
					}
			},
			'csv' 			: {
				cffaction : 'get_csv_rows',
				csvData : {
					text   	: 0,
					value  	: 0,
					fields 	: [],
					rows 	: [],
					where	: ''
				},
				getData : function(callback, parentObj)
					{
						var isRS= parentObj.ftype == 'frecordsetds',// is recordset
							obj = { data : [] },
							d   = this.csvData,
							w   = $.trim(d.where),
							v, t, r;

						if(w != '') w = parentObj.parseVars(w);
						for(var i in d.rows)
						{
							v = d.value;
							t = (typeof d.text  == 'object') ? d.text : [d.text];
							if(!$.isArray(d.rows[i]))
							{
								for(var j = 0, h = t.length; j < h; j++)
									t[j] = d.fields[j];

								v = d.fields[v];
							}

							if(w == '' || w == d.rows[i][v])
							{
								r = {};
								if(!isRS) r['value'] = d.rows[i][v];
								for(var j = 0, h = t.length; j < h; j++)
									r[(isRS) ? t[j] : 'text'] = d.rows[i][t[j]];
								obj.data.push(r);
							}
						}
						callback(obj);
					}
			}
		},
		getData : function(callback)
			{
				var me 	= this,
					obj = me.list[me.active];

				if(me.active == 'csv' && typeof obj.csvData['rows'] != 'undefined' && obj.csvData['rows'].length)
				{
					if(typeof obj['getData'] != 'undefined') obj.getData(callback, me);
					if($('[id*="'+me.name+'"]').closest('.pbreak:hidden').length) $('[id*="'+me.name+'"]').addClass('ignorepb');
				}
				else if(
					me.active == 'json' && typeof obj.jsonData['source'] != 'undefined' && obj.jsonData['source'].length ||
					me.active == 'recordset'
				)
				{
					obj.getData(callback, me);
				}
				else
				{
					var url = document.location.href,
						data = {
							cffaction : obj.cffaction,
							form 	  : obj.form,
							field	  : me.name.replace(me.form_identifier, ''),
							vars	  : {}
						},
						_form = $('[id*="'+me.name+'"]').closest('form'),
						_page;

					if(_form.length)
					{
						_page = _form.find('[name="cp_ref_page"]');
						url = (_page.length) ? _page.val() : url;
					}

					if(typeof obj.vars != 'undefined')
					{
						if (!me.replaceVariables(obj.vars, data['vars'])) return;
					}

					if(typeof me.ajaxConnect != 'undefined') me.ajaxConnect.abort();
					me.ajaxConnect = $.ajax(
						{
							dataType : 'json',
							url : url.replace(/^http(s)?\:/i, ''),
							cache : false,
							data : data,
							success : (function(me){
								return function(data){
									if($('[id*="'+me.name+'"]').closest('.pbreak:hidden').length)
									{
										$('[id*="'+me.name+'"]').addClass('ignorepb');
									}

									callback(data);
								};
							})(me)
						}
					);
				}
			},
		parseVars : function(p)
			{
				var o = {}, v;
				p = p.replace(/^\s*/, '').replace(/\s*$/, '');
				if(p != '')
				{
					if((v = p.match(/<\s{0}%[^%]*%\s{0}>/g)) != null)
					{
						v = v.map(function(x){return x.replace(/(<\s{0}%|%\s{0}>)/g, '');});
						this.replaceVariables(v, o);
						for(var i in v)
						{
							var index = encodeURI(v[i]);
							if(typeof o[index] != 'undefined')
							{
								p = p.replace(new RegExp('<\s{0}%'+v[i].replace(/[\-\[\]\{\}\(\)\*\+\?\.\,\\\^\$\|\#\s]/g, "\\$&")+'%\s{0}>', 'g'),  o[index]);
							}
						}
					}
				}
				return p;
			},
		replaceVariables : function(vars, _rtn)
			{
				var	me = this,
					field,
					formId = form_identifier = me.form_identifier,
					id,
					raw,
					isValid = true,
					val = '';

				// Prevents duplicate handles
				if(!('ds_filtering_fields' in me)) me.ds_filtering_fields = {};

				for(var i = 0, h = vars.length; i < h; i++)
				{
					id 		= vars[i]+formId;
					raw 	= (id.indexOf('|r') != -1);
					id 		= id.replace('|r', '');
					field 	= $.fbuilder['forms'][formId].getItem(id);
					// It is a field in the form
					if(typeof field != 'undefined' && field != false)
					{
						val = field.val(raw);
						if($('[id*="'+id+'"]').val() == '') isValid = false;
						if(!(id in me.ds_filtering_fields))
						{
							me.ds_filtering_fields[id] = 1;
							$(document).on('change trigger_ds', '[id="'+id+'"],[id^="'+id+'_cb"],[id^="'+id+'_rb"]', function(){ me.after_show(); });
						}
					}
					else // It is a javascript variable
					{
						try{
							if(typeof window[vars[i]] != 'undefined') val = window[vars[i]];
							else val = eval(vars[i]);
						}catch(err){
							val = '';
						}
					}
					_rtn[encodeURI(vars[i])] = (val+'').replace(/^['"]+/, '').replace(/['"]+$/, '');
				}
				return isValid;
			},
		setDefault : function() // Used by the DS fields: DropDown, Checkbox, and Radio Buttons
			{
				var d = this.defaultSelection,
					l,e,t,n = this.name;

				if($.isArray(d))
				{
					for(var i in d) d[i] = this.parseVars($.trim(d[i]));
				}
			    else
				{
					d = this.parseVars($.trim(d));
				}

				if(!/^\s*$/.test(d.toString()))
				{
					l = ($.isArray(d)) ? d : $.fbuilder.htmlEncode(d).split('|');
					for(var i in l)
					{
						t = $.trim(l[i]);
						if(!/^\s*$/.test(t))
						{
							e = $('[name*="'+n+'"][value="'+t+'"],[name*="'+n+'"][vt="'+t+'"]');
							if(e.length) e.prop('checked', true);
							else
							{
								e = $('[name*="'+n+'"]').find('option[value="'+t+'"],option[vt="'+t+'"]');
								if(e.length) e.prop('selected', true);
							}
						}
					}
				}
			}
	};