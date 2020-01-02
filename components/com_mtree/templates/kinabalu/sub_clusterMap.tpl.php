<?php
if( empty($this->arrLocations) ) {
	?>
	<!-- No locations available -->
	<?php
	return;
}

// Map should be shown or closed by default?
$map_shown = true;
$map_button_label = JText::_( 'COM_MTREE_HIDE_PAGES_MAP' );
if( $this->show_map == 2 ) {
	$map_shown = false;
	$map_button_label = JText::_( 'COM_MTREE_SHOW_PAGES_MAP' );
}

?>
<script src="//maps.googleapis.com/maps/api/js?v=3.<?php echo $this->google_maps_api_key_url_param; ?>" type="text/javascript"></script>
<script src="<?php echo $this->jconf['live_site'].$this->mtconf['relative_path_to_js']; ?>markerclusterer.js" type="text/javascript"></script>

<a href="javascript:void(0);" class="toggleMap"><span><?php echo $map_button_label; ?></span></a>
<div id="mapCon">
	<div id="map" style="height: 400px; width: 100%; margin-bottom: 1.5em;<?php if( !$map_shown ) { echo 'display:none;'; } ?>">
	</div>
</div>
<script type="text/javascript">
	<?php if($this->use_styled_map) { ?>
	var styles = <?php echo $this->google_maps_styled_map_style_array; ?>
	var styledMap = new google.maps.StyledMapType(styles,
			{name: "<?php echo JText::_( 'COM_MTREE_STYLED_MAP' ); ?>"});
	<?php } ?>

	var imgPath = '<?php echo $this->jconf['live_site'].$this->mtconf['relative_path_to_listing_small_image']; ?>';
	var locations = [
		<?php
		echo implode(',',$this->arrLocations);

		?>
	];
	var bounds = new google.maps.LatLngBounds();

	var map = new google.maps.Map(document.getElementById('map'), {
		mapTypeControlOptions: {
			mapTypeIds: [<?php echo implode(',',$this->map_types); ?>]
		},
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false,
		scrollwheel: false
		<?php if($this->use_styled_map) { ?>
		,style: styles
		<?php } ?>
	});

	<?php if($this->use_styled_map) { ?>
	// Styled Map
	map.mapTypes.set('map_style', styledMap);
	<?php } ?>

	map.setMapTypeId(<?php echo $this->default_map; ?>);

	var infowindow = new google.maps.InfoWindow();

	var marker, i;
	var imgThumbs = [];
	var markers = [];

	for (i = 0; i < locations.length; i++) {
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			map: map,
			icon: '<?php echo $this->jconf['live_site'].$this->mtconf['google_maps_marker_image']; ?>'
		});

		if( locations[i][3] == '' ) {
			imgThumbs.push('<?php echo $this->jconf['live_site'].$this->mtconf['relative_path_to_images']; ?>noimage_thb.png');
		} else {
			imgThumbs.push(imgPath + locations[i][3]);
		}

		google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
				infowindow.setContent(
					'<div class="map-popup">'
					+ '<h4><a target=_blank href="' + locations[i][4] + '">' + locations[i][0] + '</a></h4>'

					+ '<div class="map-popup-image">'
					+ '<img src="'+imgThumbs[i]+'" />'
					+ '</div>'

					+ '<div class="map-popup-info">'
//							+ locations[i][4]
					+ '</div>'

					+ '<div class="map-popup-action">'
					+ '<a target=_blank class="btn btn-info" href="' + locations[i][4] + '">Read more</a>'
					+ '</div>'

					+ '</div>'
				);
				infowindow.open(map, marker);
			}
		})(marker, i));

		markers.push(marker);

		//extend the bounds to include each marker's position
		bounds.extend(marker.position);
	}

	//now fit the map to the newly inclusive bounds
	map.fitBounds(bounds);

	google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
		limitMaxZoom();
	});

	// Clustering
	var options = {
		gridSize: 25,
		imagePath: '<?php echo $this->jconf['live_site']; ?>/media/com_mtree/images/m'
	};

	var markerCluster = new MarkerClusterer(map, markers, options);

	jQuery(".toggleMap").on("click", function () {
		jQuery("#map").slideToggle("slow", function() {
			google.maps.event.trigger(map, "resize");
			map.fitBounds(bounds);
			limitMaxZoom();
		});
		jQuery("span", this).text(jQuery("span",this).text() == "<?php echo JText::_( 'COM_MTREE_HIDE_PAGES_MAP', true ); ?>" ? "<?php echo JText::_( 'COM_MTREE_SHOW_PAGES_MAP', true ); ?>" : "<?php echo JText::_( 'COM_MTREE_HIDE_PAGES_MAP', true ); ?>");

		return false;
	});

	function limitMaxZoom(){
		var maxZoom = <?php echo $this->mtconf['cluster_map_max_zoom']; ?>;
		if (map.getZoom() > maxZoom) {
			map.setZoom(maxZoom);
		}
	}
</script>