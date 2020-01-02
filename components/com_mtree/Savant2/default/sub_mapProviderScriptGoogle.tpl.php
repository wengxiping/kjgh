<script type="text/javascript">
		function initialize() {
			var mapLatlng = new google.maps.LatLng(<?php echo $this->link->lat . ', ' . $this->link->lng; ?>);
			var mapOptions = {
				zoom: <?php echo ($this->link->zoom?$this->link->zoom:13); ?>,
				center: mapLatlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				scrollwheel: false
			};
			var map = new google.maps.Map(document.getElementById("map"), mapOptions);

			<?php if($this->use_styled_map) { ?>
			// Styled Map
			var styles = <?php echo $this->google_maps_styled_map_style_array; ?>;
			var styledMap = new google.maps.StyledMapType(styles,
				{name: "<?php echo JText::_( 'COM_MTREE_STYLED_MAP' ); ?>"});
			map.mapTypes.set('styled_map', styledMap);
			<?php } ?>

			map.setMapTypeId(<?php echo $this->default_map; ?>);

			var marker = new google.maps.Marker({
				position: mapLatlng,
				map: map,
				title:"<?php echo addslashes($this->link->link_name); ?>"
			});
		}
		google.maps.event.addDomListener(window, 'load', initialize);
	</script>