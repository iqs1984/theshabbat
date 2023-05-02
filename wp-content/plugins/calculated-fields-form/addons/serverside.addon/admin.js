fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery[ 'fbuilder' ] = fbuilderjQuery[ 'fbuilder' ] || {};
fbuilderjQuery[ 'fbuilder' ][ 'modules' ] = fbuilderjQuery[ 'fbuilder' ][ 'modules' ] || {};

fbuilderjQuery[ 'fbuilder' ][ 'modules' ][ 'server-side' ] = {
	'tutorial' : '',
	'toolbars'		: {
		'server-side' : {
			'label' : 'Call server side equations',
			'buttons' : [
							{
								"value" : "SERVER_SIDE",
								"code" : "SERVER_SIDE(",
								"tip" : "<p>Call an equation defined in the server side. <strong>SERVER_SIDE( equation name [, parameter, parameter, parameter])</strong></p><p>The first parameter is a text with the equation name, the other parameters would be the parameters used by the server side equation. Returns the result of the server side equation.</p>"
							}
						]
		}
	}
};