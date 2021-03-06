<?php
	if( $this->config->get('use_map') == 1 && !empty($this->link->lat) && !empty($this->link->lng) && !empty($this->link->zoom) ) {
		$width = '100%';
		$height = '300px';

?><div class="map">
	<div class="title"><?php echo JText::_( 'COM_MTREE_MAP' ); ?></div>
	<script src="//maps.googleapis.com/maps/api/js?v=3.<?php echo $this->google_maps_api_key_url_param; ?>" type="text/javascript"></script>
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
			var marker = new google.maps.Marker({
				position: mapLatlng,
				map: map,
			 	title:"<?php echo addslashes($this->link->link_name); ?>"
			 });
		}
		google.maps.event.addDomListener(window, 'load', initialize);
	</script>
	<div id="map" style="max-width: none;width:<?php echo $width; ?>;height:<?php echo $height; ?>"></div>
</div><?php
}
?>