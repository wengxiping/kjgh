function mapinitialize() {
	if( linkValLng != 0 && linkValLat != 0 && linkValLng != '' && linkValLat != '') {
		var mapLatlng = [linkValLat, linkValLng];
		var zoom = linkValZoom;
	} else {
		var mapLatlng = [defaultLat, defaultLng];
		var zoom = defaultZoom;
	}

    map = L.map('map')
        .setView(mapLatlng, zoom);
    map.attributionControl.setPrefix('');
    switch (mapConfig.map_provider) {
        case 'here':
            L.tileLayer.here({appId: mapConfig.here_app_id, appCode: mapConfig.here_app_code}).addTo(map);
            break;
        case 'mapbox':
            L.tileLayer(
                'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=' + mapConfig.mapbox_access_token, {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox.streets',
                }
            ).addTo(map);
            break;
        default: 
            if (mapConfig.custom_tile_server == '' || mapConfig.custom_tile_server == false) {
                L.tileLayer(
                    mapConfig.custom_tile_url, {attribution: mapConfig.custom_tile_attribution}
                ).addTo(map);
            } else {
                L.tileLayer(
                    mapConfig.custom_tile_server.url, {attribution: mapConfig.custom_tile_server.attribution}
                ).addTo(map);
            }
	}
	
	marker = L.marker(mapLatlng, { draggable: true }).addTo(map);
	marker.on('dragend', function (e) {
		leafletUpdateField(e.target._latlng);
	});
	map.on('zoom', function (e) {
		jQuery('#zoom').val(e.target._zoom);
	});

    geocoder = new google.maps.Geocoder();
}
function leafletUpdateField(latlng) {
	jQuery('#lat').val(latlng.lat);
	jQuery('#lng').val(latlng.lng);
}

function showAddress(address) {
  geocoder.geocode(
    {'address':address},
    function(results,status) {
		jQuery('#locateButton').val(Joomla.JText._('COM_MTREE_LOCATE_IN_MAP'));
		jQuery('#locateButton').attr('disabled',false);

        if (status == google.maps.GeocoderStatus.OK) {
            console.log(map, results);
            var point = [results[0].geometry.location.lat(), results[0].geometry.location.lng()];
            map.setView(point, 14);
            marker.setLatLng(point);
            console.log({lat:point[0], lng:point[1] });
            leafletUpdateField({lat:point[0], lng:point[1] });
            
            marker.bindPopup(address).openPopup();
            
		} else {
			alert(Joomla.JText._('COM_MTREE_GEOCODER_NOT_OK') + address);
		}
    }
  );
}			
function locateInMap() {
	jQuery('#locateButton').val(Joomla.JText._('COM_MTREE_LOCATING'));
	jQuery('#locateButton').attr('disabled',true);
	jQuery('#locateButton').css('font-weight','normal');
	showAddress(getAddress());
}
function getAddress() {
	var city;
	var state;
	var country;
	var postcode;
	if(typeof(jQuery('#cf7').val()) != 'undefined' && jQuery('#cf7').val() != ''){country=jQuery('#cf7').val();}
	else {country = defaultCountry;}
	if(typeof(jQuery('#cf6').val()) != 'undefined' && jQuery('#cf6').val() != ''){state=jQuery('#cf6').val();}
	else {state = defaultState;}
	if(typeof(jQuery('#cf5').val()) != 'undefined' && jQuery('#cf5').val() != ''){city=jQuery('#cf5').val();}
	else {city = defaultCity;}

	if(typeof(jQuery('#cf8').val()) == 'undefined') {
		postcode = '';
	} else {
		postcode = jQuery('#cf8').val();
	}
	var address = new Array(jQuery('#cf4').val(),city,state,postcode,country);
	var val = null;
	for(var i=0;i<address.length;i++){
		if(address[i] != '') {
			if(val == null) {
				val = address[i];
			} else {
				val += ', ' + address[i];
			}
		}
	}
	return val;
}
function updateMapAddress() {
	jQuery('#map-msg').html(getAddress());
	if(jQuery('#cf4').val() == '' && jQuery('#cf5').val() == '' && jQuery('#cf6').val() == '' && jQuery('#cf7').val() == '' && jQuery('#cf8').val() == '') {
		jQuery('#locateButton').css('font-weight','normal');
		jQuery('#locateButton').attr('disabled',true);
	} else {
		jQuery('#locateButton').css('font-weight','bold');
		jQuery('#locateButton').attr('disabled',false);
	}
}

jQuery(document).ready(function () {
	var mapShownYet = false;

	updateMapAddress();
	jQuery('#locateButton').css('font-weight','normal');
	if(linkValLat == 0 || linkValLng == 0) {
		jQuery('#map-msg').html(Joomla.JText._('COM_MTREE_ENTER_AN_ADDRESS_AND_PRESS_LOCATE_IN_MAP_OR_MOVE_THE_RED_MARKER_TO_THE_LOCATION_IN_THE_MAP_BELOW'));
	}
	mapinitialize();
	jQuery('#cf4,#cf5,#cf6,#cf7,#cf8').change(function(){
		updateMapAddress();
	});
	jQuery('#cf4,#cf5,#cf6,#cf7,#cf8').keyup(function(){
		updateMapAddress();
	});

	jQuery('a[data-toggle="tab"]').on('shown', function (e) {
        if(e.target.hash=='#listing-map' && !mapShownYet)
        {
			map.invalidateSize(false);
			mapShownYet = true;
        }
    });

});