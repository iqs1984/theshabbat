fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery[ 'fbuilder' ] = fbuilderjQuery[ 'fbuilder' ] || {};
fbuilderjQuery[ 'fbuilder' ][ 'modules' ] = fbuilderjQuery[ 'fbuilder' ][ 'modules' ] || {};

fbuilderjQuery[ 'fbuilder' ][ 'modules' ][ 'chart' ] = {
	'tutorial' : 'https://cff.dwbooster.com/documentation#chart-module',
	'toolbars'		: {
		'chart' : {
			'label' : 'Chart.js Integration',
			'buttons' : [
							{
								"value" : "cffchart",
								"code" : "cffchart(",
								"tip" : "<p>Allows to generate a Chart (with ChartJS library) into the form.</p><p>Insert a canvas tag into a HTML Content field &amp;lt;canvas id=&quot;my-canvas&quot;&amp;gt;&amp;lt;/canvas&amp;gt;, and then call the cffchart operation as part of the equation:<br><br>cffchart(&quot;my-canvas&quot;, {type:&quot;bar&quot;, data:{labels:[&quot;Label A&quot;, &quot;Label B&quot;], datasets:[{data:[fieldname1, fieldname2]}]}})<br><br><a href=https://www.chartjs.org target=&quot;_blank&quot;>More information in https://www.chartjs.org</a></p>"
							}
						]
		}
	}
};