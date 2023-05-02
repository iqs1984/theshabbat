	$.fbuilder.controls['fhtmlds']=function(){};
	$.extend(
		$.fbuilder.controls['fhtmlds'].prototype,
		$.fbuilder.controls['fhtml'].prototype,
		$.fbuilder.controls['datasource'].prototype,
		{
			ftype:"fhtmlds",
			first_time: true,

			sanitize:function(v)
				{
					try{
						var HtmlSanitizer=new function(){var k={A:!0,ABBR:!0,B:!0,BLOCKQUOTE:!0,BODY:!0,BR:!0,CENTER:!0,CODE:!0,DD:!0,DIV:!0,DL:!0,DT:!0,EM:!0,FONT:!0,H1:!0,H2:!0,H3:!0,H4:!0,H5:!0,H6:!0,HR:!0,I:!0,IMG:!0,LABEL:!0,LI:!0,OL:!0,P:!0,PRE:!0,SMALL:!0,SOURCE:!0,SPAN:!0,STRONG:!0,SUB:!0,SUP:!0,TABLE:!0,TBODY:!0,TR:!0,TD:!0,TH:!0,THEAD:!0,UL:!0,U:!0,VIDEO:!0},l={FORM:!0,"GOOGLE-SHEETS-HTML-ORIGIN":!0},m={align:!0,color:!0,controls:!0,height:!0,href:!0,id:!0,src:!0,style:!0,target:!0,title:!0,type:!0,width:!0},n=
						{"background-color":!0,color:!0,"font-size":!0,"font-weight":!0,"text-align":!0,"text-decoration":!0,width:!0},g="http: https: data: m-files: file: ftp: mailto: pw:".split(" "),r={href:!0,action:!0},t=new DOMParser;this.SanitizeHtml=function(a,p){function q(b){if(b.nodeType==Node.TEXT_NODE)var d=b.cloneNode(!0);else if(b.nodeType==Node.ELEMENT_NODE&&(k[b.tagName]||l[b.tagName]||p&&b.matches(p))){d=l[b.tagName]?f.createElement("DIV"):f.createElement(b.tagName);for(var a=0;a<b.attributes.length;a++){var c=
						b.attributes[a];if(m[c.name])if("style"==c.name)for(c=0;c<b.style.length;c++){var e=b.style[c];n[e]&&d.style.setProperty(e,b.style.getPropertyValue(e))}else{if(r[c.name]){if(e=-1<c.value.indexOf(":")){a:{e=c.value;for(var h=0;h<g.length;h++)if(0==e.indexOf(g[h])){e=!0;break a}e=!1}e=!e}if(e)continue}d.setAttribute(c.name,c.value)}}for(a=0;a<b.childNodes.length;a++)c=q(b.childNodes[a]),d.appendChild(c,!1);if(("SPAN"==d.tagName||"B"==d.tagName||"I"==d.tagName||"U"==d.tagName)&&""==d.innerHTML.trim())return f.createDocumentFragment()}else d=
						f.createDocumentFragment();return d}a=a.trim();if(""==a||"<br>"==a)return"";-1==a.indexOf("<body")&&(a="<body>"+a+"</body>");var f=t.parseFromString(a,"text/html");"BODY"!==f.body.tagName&&f.body.remove();"function"!==typeof f.createElement&&f.createElement.remove();return q(f.body).innerHTML.replace(/<br[^>]*>(\S)/g,"<br>\n$1").replace(/div><div/g,"div>\n<div")};this.AllowedTags=k;this.AllowedAttributes=m;this.AllowedCssStyles=n;this.AllowedSchemas=g};

						return HtmlSanitizer.SanitizeHtml(v);
					}catch(err){}
					return v;
				},
			show:function()
				{
					return $.fbuilder.controls['fhtml'].prototype.show.call(this);
				},
			after_show : function()
				{

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
								if(data.data.length)
								{
									v = data.data[0]['value'];
								}
							}
							$('#'+me.name).html(me.sanitize(v)).trigger('cff-data-filled');
						}
					);
				}
	});