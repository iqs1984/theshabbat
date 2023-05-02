/*
* chart.js v0.1
* By: CALCULATED FIELD PROGRAMMERS
* Allows to use the chart.js library (https://www.chartjs.org/) with the CFF
* Copyright 2019 CODEPEOPLE
*/

;(function(root){
	var version = '3.7.1', lib = {}, _$, _loading = false, _loading_plugins = false, _plugins = {}, _loaded_plugins = 0, _queue = [], colors_json={"red":["#ffa8a8","#ff8787","#ff6b6b","#fa5252"],"blue":["#74c0fc","#4dabf7","#339af0","#228be6"],"yellow":["#ffe066","#ffd43b","#fcc419","#fab005"],"green":["#8ce99a","#69db7c","#51cf66","#40c057"],"pink":["#faa2c1","#f783ac","#f06595","#e64980"],"cyan":["#66d9e8","#3bc9db","#22b8cf","#15aabf"],"orange":["#ffc078","#ffa94d","#ff922b","#fd7e14"],"lime":["#c0eb75","#a9e34b","#94d82d","#82c91e"],"grape":["#e599f7","#da77f2","#cc5de8","#be4bdb"],"indigo":["#91a7ff","#748ffc","#5c7cfa","#4c6ef5"],"teal":["#63e6be","#38d9a9","#20c997","#12b886"],"violet":["#b197fc","#9775fa","#845ef7","#7950f2"]}, _initialized = false;

	/*** PRIVATE FUNCTIONS ***/
	function pickColor(i)
	{
		var idx = Object.keys(colors_json),
			plt = colors_json[idx[i % idx.length]];
		return plt[Math.floor(i % plt.length)];
	};

	function init_and_services()
	{
		if(_initialized) return;
		_initialized = true;

        var beforeUpdate = function(chart){
            try
            {
                var i = 0, m = 0;
                if('data' in chart && 'datasets' in chart.data)
                {
                    var dss = chart.data.datasets,
                        dss_length = dss.length;

                    for(var ds in dss)
                    {
                        ds = dss[ds];
                        if(!ds.borderColor)
                        {
                            var bc = [];
                            for(var _d in ds.data)
                            {
                                _d = ds.data[_d];
                                bc.push(pickColor((dss_length < 2) ? i++ : i));
                            }
                            if(2 <= dss_length) i++;
                            ds.borderColor = bc;
                            if(!ds.backgroundColor)
                            {
                                ds.backgroundColor = ds.borderColor.map(
                                    function(hex)
                                    {
                                        return hex+'80';
                                    }
                                );
                            }
                        }
                    }
                }
            }catch(err){if('console' in window) console.error(err.message);}
        };

		Chart.defaults.scale.ticks.beginAtZero = true;

        if('pluginService' in Chart)
        {
            Chart.pluginService.register(
                {
                    beforeUpdate: beforeUpdate
                }
            );
        }
        else
        {
            Chart.register(
                {
                    id:'cffBeforeUpdate',
                    beforeUpdate: beforeUpdate
                }
            );
        }
	};

	function load_and_clear_queue(version)
	{
		if(!('Chart' in window))
		{
			if (!_loading)
			{
				_loading = true;
                var url = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/'+version;
                try
                {
                    if(version.split('.')[0]*1<3) url += '/Chart.min.js';
                    else url += '/chart.min.js';
                }
                catch(err){ url += '/Chart.min.js'; }
				_$.getScript(
					url,
					function ()
					{
						if ( Object.keys(_plugins).length == _loaded_plugins ) {
							init_and_services();
							clear_queue();
						} else {
							load_plugins();
						}
					}
				);
			}
			return;
		} else if ( Object.keys(_plugins).length == _loaded_plugins ) {
			init_and_services();
			clear_queue();
		} else {
			load_plugins();
		}

	};

	function load_plugins() {
		if (_loading_plugins) return;
		_loading_plugins = true;

		for( var i in _plugins ) {
			_$.getScript( i ).done( (function(i){
				return function(){
					if(typeof _plugins[i] == 'function') {
						_plugins[i]();
					}
					_loaded_plugins += 1;
					if (Object.keys(_plugins).length == _loaded_plugins ) {
						load_and_clear_queue();
					}
				};
			})(i) );
		}
	};

	function add_queue(args)
	{
		// Add the to queue
		_queue.push(args);
		load_and_clear_queue(args['args']['version'] || version);
	};

	function clear_queue()
	{
		var item;
		while(_queue.length)
		{
			// Generate or update the chart
			item = _queue.shift();
			if('id' in item && 'args' in item) generator(item.id, item.args);
		}
	};

	/*** CHART FUNCTIONS ***/

	function generator(id, obj)
	{
		var _canvas = _$('[id="'+id.replace(/['"]/g, '\$1')+'"]'), _chart;
		if(_canvas.length)
		{
			_chart = _canvas.data('chart-obj');
			if(_chart)
			{
				_chart.clear();
				_chart.destroy();
			}
			_chart = new Chart(
				_canvas,
				obj
			);
			_canvas.data('chart-obj', _chart);
		}
	};

	/*** PUBLIC FUNCTIONS ***/

	lib.cff_chart_version = '0.1';

	lib.cffchart_addplugin = lib.CFFCHART_ADDPLUGIN = function(url, callback)
	{
		if ( !( url in _plugins ) ) {
			_plugins[url] = callback;
		}
	};

	lib.cffchart = lib.CFFCHART = function(canvasId, args, field)
	{
		if('undefined' == typeof fbuilderjQuery) return;
		_$ = fbuilderjQuery;
		if ( 'register_plugins' in args ){
			try {
				if( ! Array.isArray( args['register_plugins'] ) ) args['register_plugins'] = [args['register_plugins']];
				for( var i in args['register_plugins'] ) {
					if( typeof args['register_plugins'][i] == 'string' ) {
						 args['register_plugins'][i] = {'url': args['register_plugins'][i], 'callback': function(){}};
					}

					lib.CFFCHART_ADDPLUGIN(
						args['register_plugins'][i]['url'],
						(
							'callback' in args['register_plugins'][i] &&
							typeof args['register_plugins'][i]['callback'] == 'function'
						) ? args['register_plugins'][i]['callback'] : function(){}
					);
				}
			} catch(err){
				if( 'console' in window ) console.log(err);
			}
		}
		if(field && typeof field == 'object' && ('ftype' in field))
		{
			if(!('options' in args)) args['options'] = {};
			if(!('animation' in args['options'])) args['options']['animation'] = {};
			if(!('onComplete' in args['options']['animation']))
			{
				args['options']['animation']['onComplete'] = function(){
					var img = document.getElementById(canvasId).toDataURL();
					field.jQueryRef().find('input').val(img);
				};
			}
		}
		add_queue(
			{
				'id' 	: canvasId,
				'args' 	: args
			}
		);
	};
	root.CF_CHART = lib;

})(this);