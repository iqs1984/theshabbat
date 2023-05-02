/*
* distance.js v0.2
* By: CALCULATED FIELD PROGRAMMERS
* The script allows operations with distance
* Copyright 2015 CODEPEOPLE
* You may use this project under MIT or GPL licenses.
*/

;(function(root){
	var lib = {},
		defaultUnitSystem = 'km',
		defaultTravelMode = 'DRIVING',
		defaultAvoidHighways = false,
		defaultAvoidTolls 	 = false,
        defaultAvoidFerries  = false,
		latlngArr	  = {},
		distanceArr   = [],
		geocodingObj  = {},
		travelTimeArr = [],
		callbacks     = [],
		currentLatLng,
        cff_timeout_id = {};


	/*** PRIVATE FUNCTIONS ***/

	/*
	* Runs all callbacks after loading the Google API
	*/
	function _runCallbacks()
	{
		var h = callbacks.length;
		if( h )
		{
			for( var i = 0; i < h; i++ )
			{
				callbacks[i]();
			}
		}
		callbacks = [];
	};

	/*
	* Inserts the SCRIPT tag for loading the Google API
	*/
	function _createScriptTags()
	{
		// If Google Maps has not been loaded, and has not been created the script tags for loading the API
		if(!('loadingGoogleMaps' in fbuilderjQuery))
		{
            fbuilderjQuery.loadingGoogleMaps = true;
			var script=document.createElement('script');
			script.type  = "text/javascript";
			script.src = '//maps.google.com/maps/api/js?'+( ( typeof google_api_key != 'undefined' ) ? 'key='+google_api_key+'&' : '' )+'callback=cff_google_maps_loaded';
			document.body.appendChild(script);
		}
	};

	/*
	* Check the default value and the attribute and returns the correct value.
	*/
	function _getValue( attr, val )
	{
		if(
			typeof google != 'undefined' &&
			typeof google[ 'maps' ] != 'undefined'
		)
		{
			val = String(val).toUpperCase();

			switch( attr )
			{
				case 'unitSystem':
					val = ( val == 'MI' ) ? google.maps.UnitSystem.IMPERIAL : google.maps.UnitSystem.METRIC;
				break;
				case 'travelMode':
					switch( val )
					{
						case 'BICYCLING': val = google.maps.TravelMode.BICYCLING; break;
						case 'TRANSIT'  : val = google.maps.TravelMode.TRANSIT; break;
						case 'WALKING'  : val = google.maps.TravelMode.WALKING; break;
						default  		: val = google.maps.TravelMode.DRIVING; break;
					}
				break;
			}
		}

		return val;
	};

	/*
	* Evaluate all equations in the form
	*/
	function _reCalculate( eq )
	{
		$.fbuilder.calculator.enqueueEquation(eq.identifier, [eq]);
		if(!(eq.identifier in $.fbuilder.calculator.processing_queue) || !$.fbuilder.calculator.processing_queue[eq.identifier])
		{
			$.fbuilder.calculator.processQueue(eq.identifier);
		}
	};

	/*** PUBLIC FUNCTIONS ***/
	lib.cf_distance_version = '0.2';

	/*
	* CURRENTLATLNG()
	*
	* Return the user latitude and longitude of address. The page must be protected with SSL
	*/
	lib.currentlatlng =lib.CURRENTLATLNG = function(eq)
	{
		eq = (typeof eq != 'undefined') ? eq : $.fbuilder['currentEq'];
		if( typeof currentLatLng != 'undefined' ) return currentLatLng;

		if ('geolocation' in navigator) {
			navigator.geolocation.getCurrentPosition( function(position) {
				currentLatLng = [position.coords.latitude, position.coords.longitude];
				_reCalculate( eq );
			});
		} else {
			return 'FAIL';
		}
	};

	/*
	* LATLNG( address )
	*
	* Return the latitude and longitude of address.
	*/
	lib.LATLNG = function(address, eq)
	{
		address = ''+address;
		address = address.trim();

		if(address.length == 0) return '';
		if(address in latlngArr) return latlngArr[address];
		eq = (typeof eq != 'undefined') ? eq : $.fbuilder['currentEq'];

		if( typeof google == 'undefined' || google['maps'] == null )
		{
			// List of functions to be called after complete the Google Maps loading
			callbacks.push(
				(
					function( address )
					{
						return function(){ LATLNG( address, eq ) };
					}
				)( address, eq )
			);
			_createScriptTags();
			return;
		}
		else
		{
			var g = new google.maps.Geocoder();
			g.geocode(
				{'address': address},
				(function(address){
					return function(result, status){
						try{
							if(status && status == "OK"){
								var lat  = result[0]['geometry']['location'].lat(),
									lng = result[0]['geometry']['location'].lng();

								latlngArr[address] = [lat,lng];
							}
							else
							{
								latlngArr[address] = 'FAIL';
								console.log('GeocoderStatus:'+status);
							}
						}catch(err){latlngArr[address] = 'FAIL';}
						_reCalculate( eq );
					};
				})(address)
			);
		}
	};

	/*
	* DISTANCE( address_a_string, address_b_string, unit_system, travel_mode, eq )
	*
	* unit_system:
	* km  - Kilometters
	* mi  - Miles
	*
	* travel_mode:
	* DRIVING - Indicates standard driving directions using the road network
	* BICYCLING - Requests bicycling directions via bicycle paths & preferred streets
	* TRANSIT - Requests directions via public transit routes
	* WALKING - Requests walking directions via pedestrian paths & sidewalks
	*
	* eq equation that calls this operation
	*
	* the function returns the distance between address_a and address_b, in the unit_system
	*/
	lib.DISTANCE = function( address_a, address_b, unit_system, travel_mode, eq ){
        function getIndex(a,b,m)
        {
            for( var i in distanceArr )
            {
                if(
                    distanceArr[ i ][ 'a' ] == a &&
                    distanceArr[ i ][ 'b' ] == b &&
                    distanceArr[ i ][ 'm' ] == m
                ) return i;
            }
            return -1;
        }

        function _haversine(eq, addresses)
        {
            var g = new google.maps.Geocoder();
            for(var i in addresses)
            {
                g.geocode(
                    {address: addresses[i]},
                    ( function( eq, addresses, i)
                        {
                            $.fbuilder.calculator.addPending(eq.identifier);
                            return  function(result, status){
                                        var dist;
                                        $.fbuilder.calculator.removePending(eq.identifier);
                                        geocodingObj[addresses[i]] = false;
                                        if(status && status == "OK")
                                        {
                                            geocodingObj[addresses[i]] = {
                                                lat : result[0]['geometry']['location'].lat(),
                                                lng : result[0]['geometry']['location'].lng()
                                            }
                                        }
                                        if(addresses[0] in geocodingObj && addresses[1] in geocodingObj)
                                        {
                                            if(geocodingObj[addresses[0]] == false || geocodingObj[addresses[1]] == false) dist = 'FAIL';
                                            else
                                            {
                                                var lat1  = geocodingObj[addresses[0]]['lat'],
                                                    lng1  = geocodingObj[addresses[0]]['lng'],
                                                    lat2  = geocodingObj[addresses[1]]['lat'],
                                                    lng2  = geocodingObj[addresses[1]]['lng'];

                                                if ((lat1 == lat2) && (lng1 == lng2)) dist  = 0;
                                                else
                                                {
                                                    var a = POW(SIN(RADIANS(lat2-lat1)/2),2) + POW(SIN(RADIANS(lng2-lng1)/2),2) * COS(RADIANS(lat1)) * COS(RADIANS(lat2)),
                                                        c = 2 * ATAN2(SQRT(a), SQRT(1-a));

                                                    dist = c * 6371000;
                                                }
                                            }

                                            var j = getIndex(addresses[0], addresses[1], 'STRAIGHT');
                                            distanceArr[ j ][ 'distance' ] = dist;
                                            _reCalculate( eq );
                                        }
                                    };
                        }
                    )(eq, addresses, i)
                );
            }
        };

		if( typeof address_a != 'undefined' && typeof address_b != 'undefined' )
		{
            address_a = (new String(address_a)).replace( /^\s+/, '' ).replace( /\s+$/, '' );
			address_b = (new String(address_b)).replace( /^\s+/, '' ).replace( /\s+$/, '' );
			if( address_a.length > 2 && address_b.length > 2 )
			{
				if( typeof unit_system == 'undefined' ) unit_system = defaultUnitSystem;
				if( typeof travel_mode == 'undefined' ) travel_mode = defaultTravelMode;

				eq = (typeof eq != 'undefined') ? eq : $.fbuilder['currentEq'];

				// The pair of address was processed previously
                var i = getIndex(address_a, address_b, travel_mode);
                if(i != -1)
                {
                    if(distanceArr[ i ][ 'distance' ] == 'pending') return;
                    return (isNaN(distanceArr[ i ][ 'distance' ])) ? distanceArr[ i ][ 'distance' ] : distanceArr[ i ][ 'distance' ]/((/mi/i.test(unit_system)) ? 1609.344 : 1000);
                }

                // Google Maps has not been included previously
				if( typeof google == 'undefined' || google['maps'] == null )
				{
					// List of functions to be called after complete the Google Maps loading
					callbacks.push(
						(
							function( address_a, address_b, unit_system, travel_mode, eq )
							{
								return function(){ DISTANCE( address_a, address_b, unit_system, travel_mode, eq ) };
							}
						)( address_a, address_b, unit_system, travel_mode, eq )
					);
					_createScriptTags();
					return;
				}

                distanceArr.push({'a':address_a, 'b':address_b, 'distance':'pending', 'm' : travel_mode});

				if(travel_mode == 'STRAIGHT')
				{
					_haversine(eq, [address_a, address_b]);
				}
				else
				{
					var service = new google.maps.DistanceMatrixService(),
						request = {
							origins		: [ address_a ],
							destinations: [ address_b ],
							travelMode	: _getValue( 'travelMode',  travel_mode ),
							unitSystem	: _getValue( 'unitSystem',  unit_system ),
							avoidHighways : (typeof avoid_highways == 'boolean') ? avoid_highways : defaultAvoidHighways,
							avoidTolls  : (typeof avoid_tolls == 'boolean') ? avoid_tolls : defaultAvoidTolls,
							avoidFerries: (typeof avoid_ferries == 'boolean') ? avoid_ferries : defaultAvoidFerries
						};

                    service.getDistanceMatrix(
						request,
						(
							function( eq, request, travel_mode )
							{
								$.fbuilder.calculator.addPending(eq.identifier);
								return function (response, status)
										{
											var r;
											$.fbuilder.calculator.removePending(eq.identifier);
											if (status == google.maps.DistanceMatrixStatus.OK)
											{
												try{
													if( response.rows[ 0 ].elements[ 0 ].status == google.maps.DistanceMatrixElementStatus.OK)
													{
														r = response.rows[ 0 ].elements[ 0 ].distance[ 'value' ];
													}
													else
													{
														if(typeof console != 'undefined')
															console.log('DistanceMatrixElementStatus:'+response.rows[ 0 ].elements[ 0 ].status);
														r = 'FAIL';
													}
												}catch( err ){ r = 'FAIL'; }
											}
                                            else if(status == google.maps.DistanceMatrixStatus.OVER_QUERY_LIMIT)
                                            {
                                                // Reached the API limits, stop the evaluation and set timeout
                                                if(eq.identifier in cff_timeout_id) clearTimeout(cff_timeout_id[eq.identifier]);

                                                cff_timeout_id[eq.identifier] = setTimeout(function(){
                                                    // Remove pendings before reevaluate the equation
                                                    distanceArr = distanceArr.filter(function(i){return i['distance'] != 'pending'});
                                                    _reCalculate(eq);
                                                }, 1000);
                                                return;
                                            }
											else
											{
												if(typeof console != 'undefined')
													console.log('DistanceMatrixStatus:'+status);
												r = 'FAIL';
											}

                                            var i = getIndex(request.origins[ 0 ], request.destinations[ 0 ], travel_mode);
                                            distanceArr[ i ][ 'distance' ] = r;
                                            _reCalculate( eq );
										};
							}
						)( eq, request, travel_mode )
					);
				}
			}
		}
		return 0;
	};

	/*
	* TRAVELTIME( address_a_string, address_b_string, as_text, travel_mode, avoid_highways, avoid_tolls )
	*
	* as_text:
	* true or 1  - Returns a textual representation of travel time
	* false or 0 - Returns the travel time in seconds
	*
	* travel_mode:
	* DRIVING - Indicates standard driving directions using the road network
	* BICYCLING - Requests bicycling directions via bicycle paths & preferred streets
	* TRANSIT - Requests directions via public transit routes
	* WALKING - Requests walking directions via pedestrian paths & sidewalks
	*
	* avoid_highways: true, false
	* avoid_tolls: true, false
	*
	* eq equation that calls this operation
	*
	* the function returns the time between address_a and address_b
	*/
	lib.TRAVELTIME = function( address_a, address_b, as_text, travel_mode, avoid_highways, avoid_tolls, eq ){
        function getIndex(a,b,m,x,h,t)
        {
            for( var i in travelTimeArr )
            {
                if(
                    travelTimeArr[ i ][ 'a' ] == a &&
                    travelTimeArr[ i ][ 'b' ] == b &&
                    travelTimeArr[ i ][ 'm' ] == m &&
                    travelTimeArr[ i ][ 'x' ] == x &&
                    travelTimeArr[ i ][ 'h' ] == h &&
                    travelTimeArr[ i ][ 't' ] == t
                ) return i;
            }
            return -1;
        }

		if( typeof address_a != 'undefined' && typeof address_b != 'undefined' )
		{
			address_a = (new String(address_a)).replace( /^\s+/, '' ).replace( /\s+$/, '' );
			address_b = (new String(address_b)).replace( /^\s+/, '' ).replace( /\s+$/, '' );
			if( address_a.length > 2 && address_b.length > 2 )
			{
				if( typeof as_text == 'undefined' ) 	 as_text = false;
				if( typeof travel_mode == 'undefined' )  travel_mode = defaultTravelMode;
				if( typeof avoid_highways != 'boolean' ) avoid_highways = defaultAvoidHighways;
				if( typeof avoid_tolls != 'boolean' ) 	 avoid_tolls = defaultAvoidTolls;

				eq = (typeof eq != 'undefined') ? eq : $.fbuilder['currentEq'];

				// The pair of address was processed previously
                var i = getIndex(address_a, address_b, travel_mode, as_text, avoid_highways, avoid_tolls);
                if(i != -1)
                {
                    if(travelTimeArr[ i ]['time']  == 'pending') return;
                    return travelTimeArr[ i ][ 'time' ];
                }

				// Google Maps has not been included previously
				if( typeof google == 'undefined' || google['maps'] == null )
				{
					// List of functions to be called after complete the Google Maps loading
					callbacks.push(
						(
							function( address_a, address_b, as_text, travel_mode, avoid_highways, avoid_tolls, eq )
							{
								return function(){ TRAVELTIME( address_a, address_b, as_text, travel_mode, avoid_highways, avoid_tolls, eq ) };
							}
						)( address_a, address_b, as_text, travel_mode, avoid_highways, avoid_tolls, eq )
					);
					_createScriptTags();
					return;
				}

                travelTimeArr.push(
                    {'a':address_a, 'b':address_b, 'm':travel_mode, 'x':as_text, 'h':avoid_highways, 't':avoid_tolls, 'time':'pending'}
                );

				var service = new google.maps.DistanceMatrixService(),
					request = {
						origins		: [ address_a ],
						destinations: [ address_b ],
						travelMode	: _getValue( 'travelMode',  travel_mode ),
						avoidHighways : avoid_highways,
						avoidTolls  : avoid_tolls,
                        avoidFerries: (typeof avoid_ferries == 'boolean') ? avoid_ferries : defaultAvoidFerries
					};

				service.getDistanceMatrix(
					request,
					(
						function( eq, as_text, request, travel_mode, as_text, avoid_highways, avoid_tolls )
						{
							$.fbuilder.calculator.addPending(eq.identifier);
							return function (response, status)
									{
										var r;
										$.fbuilder.calculator.removePending(eq.identifier);
										if (status == google.maps.DistanceMatrixStatus.OK)
										{
											try{
												r = response.rows[ 0 ].elements[ 0 ].duration[ ( as_text ) ? 'text' : 'value' ];
											}catch( err ){ r = 'FAIL'; }
										}
                                        else if(status == google.maps.DistanceMatrixStatus.OVER_QUERY_LIMIT)
                                        {
                                            // Reached the API limits, stop the evaluation and set timeout
                                            if(eq.identifier in cff_timeout_id) clearTimeout(cff_timeout_id[eq.identifier]);

                                            cff_timeout_id[eq.identifier] = setTimeout(function(){
                                                // Remove pendings before reevaluate the equation
                                                travelTimeArr = travelTimeArr.filter(function(i){return i['time'] != 'pending'});
                                                _reCalculate(eq);
                                            }, 1000);
                                            return;
                                        }
										else r = 'FAIL';

                                        var i = getIndex(request.origins[ 0 ], request.destinations[ 0 ], travel_mode, as_text, avoid_highways, avoid_tolls);
                                        travelTimeArr[i]['time'] = r;
										_reCalculate( eq );
									};
						}
					)( eq, as_text, request, travel_mode, as_text, avoid_highways, avoid_tolls )
				);
			}
		}
		return 0;
	};

	lib.DMSTODD = lib.dmstodd = lib.DMStoDD = function(v){
		v = v.replace(/^\s+/g, '').replace(/\s+$/g, '');
		var p = v.match(/^([\d\.]+)[^\d]+([\d\.]+)[^\d]+([\d\.]+)[^NSEW]+([NSEW])$/i);
		if(p) return ((p[4].match(/[sw]/i)) ? -1 : 1 )*(p[1]*1+p[2]/60+p[3]/(60*60));
		return v;
	};

	lib.cff_google_maps_loaded = function(){
        jQuery(document).trigger('cff-google-maps-loaded');
    };

    jQuery(document).on('cff-google-maps-loaded', _runCallbacks);

	root.CF_DISTANCE = lib;

})(this);