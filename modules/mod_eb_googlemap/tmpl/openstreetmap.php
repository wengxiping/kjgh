<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JFactory::getDocument()
	->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
	->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css');

$coordinates = explode(',', $homeCoordinates);

?>
<div id="map<?php echo $module->id;?>" style="position:relative; width: <?php echo $width; ?>%; height: <?php echo $height?>px"></div>
<script type="text/javascript">
    Eb.jQuery(document).ready(function ($) {
        var mymap = L.map('map<?php echo $module->id; ?>', {
            center: [<?php echo $coordinates[0]; ?>, <?php echo $coordinates[1]; ?>],
            zoom: <?php echo $zoomLevel; ?>,
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            id: 'mapbox.streets'
        }).addTo(mymap);

		<?php
		foreach ($locations as $location)
		{
		?>
            var marker = L.marker([<?php echo $location->lat ?>, <?php echo $location->long;?>], {draggable: false, autoPan: true, title: "<?php echo addslashes($location->name);?>"}).addTo(mymap);
            marker.bindPopup('<?php echo $location->popupContent; ?>');
		<?php
		}
		?>
    });
</script>

