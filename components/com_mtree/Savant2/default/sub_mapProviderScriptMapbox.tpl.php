<script type="text/javascript">
window.onload = function() {
    var point = [<?php echo $this->link->lat . ', ' . $this->link->lng; ?>];
    var mtmap = L.map('map')
        .setView(point, <?php echo ($this->link->zoom ? $this->link->zoom : 13); ?>);
    mtmap.attributionControl.setPrefix('');
    L.tileLayer(
        'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=<?php echo $this->config->get('mapbox_access_token') ?>', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
        }
    ).addTo(mtmap);
    var marker = L.marker(point).addTo(mtmap);
    marker.bindPopup("<?php echo addslashes($this->link->link_name); ?>").openPopup();
}
</script>