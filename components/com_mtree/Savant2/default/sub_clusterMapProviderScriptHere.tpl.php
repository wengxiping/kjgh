<link rel="stylesheet" href="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/leaflet.css" />
<script src="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/leaflet.js"></script>

<link rel="stylesheet" href="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/MarkerCluster.css" />
<link rel="stylesheet" href="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/MarkerCluster.Default.css" />
<script src="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/leaflet.markercluster.js"></script>
<script src="<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_js']; ?>leaflet/leaflet-tilelayer-here.js" type="text/javascript"></script>

<script type="text/javascript">
window.onload = function() {
    var locations = [<?php echo implode(',', $this->arrLocations); ?>];
    var mtmap = L.map('map').setView([locations[0][1], locations[0][2]], 13);
    mtmap.attributionControl.setPrefix('');
    L.tileLayer.here({appId: '<?php echo $this->config->get('here_app_id') ?>', appCode: '<?php echo $this->config->get('here_app_code') ?>'}).addTo(mtmap);

    var markers = L.markerClusterGroup();

    var imgPath = '<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_listing_small_image']; ?>';
    var imgThumbs = [];

    for (i = 0; i < locations.length; i++) {
        var title = locations[i][0];
        var marker = L.marker(
            new L.LatLng(locations[i][1], locations[i][2]), {
                title: title
            }
        );

        if (locations[i][3] == '') {
            imgThumbs.push(
                '<?php echo $this->jconf['live_site'] . $this->mtconf['relative_path_to_images']; ?>noimage_thb.png'
                );
        } else {
            imgThumbs.push(imgPath + locations[i][3]);
        }

        var popupContent = '<div class="mt-map-cluster-popup">' +
            '<h4><a target=_blank href="' + locations[i][4] + '">' + locations[i][0] + '</a></h4>'

            +
            '<div style="display: flex; flex-direction: row">' +
            '<div style="margin-right: 1rem">' +
            '<img src="' + imgThumbs[i] + '" />' +
            '</div>'

            +
            '<div class="mt-map-cluster-popup-info">'
            //                            + locations[i][4]
            +
            '</div>'

            +
            '<div class="map-popup-action">' +
            '<a target=_blank class="btn btn-info" href="' + locations[i][4] +
            '"><?php echo JText::_('COM_MTREE_CLUSTER_MAP_LISTING_READ_MORE'); ?></a>' +
            '</div>' +
            '</div>' +
            '</div>';
        marker.bindPopup(popupContent);
        markers.addLayer(marker);
    }

    mtmap.addLayer(markers);
    mtmap.fitBounds(markers.getBounds());

    var mapShownYet = false;

    if(jQuery("#map").css('display') != 'none') {
        mapShownYet = true;
    }

    jQuery(".mt-map-toggle-button").on("click", function() {
        jQuery("#map").slideToggle("slow", function(){
            if(!mapShownYet) {
                mtmap.invalidateSize(false);
                mtmap.fitBounds(markers.getBounds());
                mapShownYet = true;
            }
        });
        jQuery("span", this).text(jQuery("span", this).text() ==
            "<?php echo JText::_('COM_MTREE_HIDE_PAGES_MAP', true); ?>" ?
            "<?php echo JText::_('COM_MTREE_SHOW_PAGES_MAP', true); ?>" :
            "<?php echo JText::_('COM_MTREE_HIDE_PAGES_MAP', true); ?>");

        return false;
    });
}
</script>