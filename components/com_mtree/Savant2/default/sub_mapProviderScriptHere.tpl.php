<script src="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/leaflet-tilelayer-here.js" type="text/javascript"></script>
<script type="text/javascript">
window.onload = function() {
    var point = [<?php echo $this->link->lat . ', ' . $this->link->lng; ?>];
    var mtmap = L.map('map')
        .setView(point, <?php echo ($this->link->zoom ? $this->link->zoom : 13); ?>);
    mtmap.attributionControl.setPrefix('');
    L.tileLayer.here({
        appId: '<?php echo $this->config->get('here_app_id') ?>',
        appCode: '<?php echo $this->config->get('here_app_code') ?>'
    }).addTo(mtmap);
    var marker = L.marker(point).addTo(mtmap);
    marker.bindPopup("<?php echo addslashes($this->link->link_name); ?>").openPopup();
}
</script>