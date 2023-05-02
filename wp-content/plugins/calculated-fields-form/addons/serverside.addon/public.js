window['SERVER_SIDE'] = function()
{
	if(typeof fbuilderjQuery == 'undefined' || !arguments.length) return;

	var $ 		= fbuilderjQuery,
		fb 		= $.fbuilder,
		fbc		= fb.calculator,
		index 	= JSON.stringify(arguments);

	if(typeof fb.server_side_equations_global == 'undefined')
		fb.server_side_equations_global = {};

	if(typeof fb.server_side_equations_global[index] != 'undefined')
	{
		if(fb.server_side_equations_global[index] == 'cff-result-pending') return;
		return fb.server_side_equations_global[index];
	}

	fb.server_side_equations_global[index] = 'cff-result-pending';
	var	aux	 	= (function(eq){
					return function(){
                        if(typeof eq == 'undefined') return;
						fbc.enqueueEquation(eq.identifier, [eq]);
						fbc.removePending(eq.identifier);

						if(
                            !(eq.identifier in fbc.processing_queue) ||
                            !fbc.processing_queue[eq.identifier]
                        )
						{
							fbc.processQueue(eq.identifier);
						}
					};
				})(fb['currentEq']),
		data 	= { 'cff_server_side_equation' : arguments[0] },
		url 	= document.location.href.split('?')[0];

	for(var i = 1, h = arguments.length; i < h; i++)
		data['param_'+i] = arguments[i];

    if('currentEq' in fb) fbc.addPending(fb['currentEq']['identifier']);

	$.ajax(
		{
			'url' : url.replace(/^http(s)?\:/i, ''),
			'method' : 'POST',
			'data' : data,
			'dataType' : 'json',
			'success' : (function(index){
					return function(data)
					{
						if(typeof data['error'] != 'undefined')
						{
							if(typeof console != 'undefined') console.log(data['error']);
						}
						else
						{
							fb.server_side_equations_global[index] = data['result'];
							aux();
						}
					};
				})(index)
		}
	);
}