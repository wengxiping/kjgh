<?php
$tile_server = json_decode($this->config->get('custom_tile_server'));
if(is_null($tile_server)) {
    $custom_tile_url = $this->config->get('custom_tile_url');
    $custom_tile_attribution = $this->config->get('custom_tile_attribution');
} else {
    $custom_tile_url = $tile_server->url;
    $custom_tile_attribution = $tile_server->attribution;
}

?><script type="text/javascript">
window.onload = function() {
    var point = [<?php echo $this->link->lat . ', ' . $this->link->lng; ?>];
    var mtmap = L.map('map')
        .setView(point, <?php echo ($this->link->zoom ? $this->link->zoom : 13); ?>);
    mtmap.attributionControl.setPrefix('');
    L.tileLayer(
        '<?php echo $custom_tile_url ?>',{attribution: '<?php echo $custom_tile_attribution ?>'}
        ).addTo(mtmap);
    var marker = L.marker(point).addTo(mtmap);
    marker.bindPopup("<?php echo addslashes($this->link->link_name); ?>").openPopup();
}
</script>
