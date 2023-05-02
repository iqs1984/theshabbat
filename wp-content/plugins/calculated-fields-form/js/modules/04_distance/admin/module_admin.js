fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery[ 'fbuilder' ] = fbuilderjQuery[ 'fbuilder' ] || {};
fbuilderjQuery[ 'fbuilder' ][ 'modules' ] = fbuilderjQuery[ 'fbuilder' ][ 'modules' ] || {};

fbuilderjQuery[ 'fbuilder' ][ 'modules' ][ 'distance' ] = {
	'tutorial' : 'https://cff.dwbooster.com/documentation#distance-module',
	'toolbars'		: {
		'distance' : {
			'label' : 'Distance functions',
			'buttons' : [
							{
								"value" : "DISTANCE",
								"code" : "DISTANCE(",
								"tip" : "<p>Get the distance between two address. <strong>DISTANCE( Address A, Address B, Unit System, Travel Mode )</strong></p><p>The allowed values for Unit System are: km for kilometters, or mi for miles, km is the value by default.</p><p>The allowed values for Travel Mode are: DRIVING, BICYCLING, TRANSIT, WALKING, or STRAIGHT, DRIVING is the value by default, and STRAIGHT determines the distance in straight line between the addresses. Returns the <b>FAIL</b> text if at least one of addresses is invalid, or it is not possible access to Google.</p>"
							},
							{
								"value" : "TRAVELTIME",
								"code" : "TRAVELTIME(",
								"tip" : "<p>Get the time for traveling between two places. <strong>TRAVELTIME( Address A, Address B, Return as Text, Travel Mode, Avoid Highways, Avoid Tolls )</strong></p><p>The allowed values for Return as Text are: 1 to get values in text format as 11 min, or 0 to get the value in seconds, zero is the default value.</p><p>The allowed values for Travel Mode are: DRIVING, BICYCLING, TRANSIT, or WALKING, DRIVING is the value by default.</p><p>The allowed values for Avoid Highways, and Avoid Tolls are:1 or 0, zero as the default value. Returns the <b>FAIL</b> text if at least one of addresses is invalid, or it is not possible access to Google.</p>"
							},
							{
								"value" : "LATLNG",
								"code" : "LATLNG(",
								"tip" : "<p>Returns an array with the latitude and longitude of the address passed as parameter, or the FAIL text:  <strong>LATLNG(address)</strong></p>"
							},
							{
								"value" : "CURRENTLATLNG",
								"code" : "CURRENTLATLNG(",
								"tip" : "<p>Returns an array with the latitude and longitude of the visitor location or the FAIL text. The web page must be SSL protected, and the user must share its location:  <strong>LATLNG()</strong></p>"
							},
							{
								"value" : "DMSTODD",
								"code" : "DMSTODD(",
								"tip" : "<p>Converts degrees, minutes and seconds to decimal degrees:  <strong>DMSTODD(value)</strong></p>"
							}
						]
		}
	}
};