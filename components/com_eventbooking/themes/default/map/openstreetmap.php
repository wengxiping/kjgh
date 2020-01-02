<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2019 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;

$rootUri = JUri::root(true);
$document = JFactory::getDocument()
	->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
	->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css');

$height      = (int) $this->config->map_height ?: 600;
$height      += 20;
$zoomLevel   = (int) $this->config->zoom_level ?: 14;
$coordinates = $this->location->lat . ',' . $this->location->long;

if ($this->location->image || EventbookingHelper::isValidMessage($this->location->description))
{
	$onPopup = false;
}
else
{
	$onPopup = true;
}
?>
<div id="eb-event-map-page" class="eb-container row-fluid">
	<?php
	if (!$onPopup)
	{
	?>
		<h1 class="eb-page-heading"><?php echo $this->escape($this->location->name); ?></h1>
	<?php
	}

	if ($this->location->image && file_exists(JPATH_ROOT . '/' . $this->location->image))
	{
	?>
		<img src="<?php echo JUri::root(true) . '/' . $this->location->image; ?>" class="eb-venue-image img-polaroid" />
	<?php
	}

	if (EventbookingHelper::isValidMessage($this->location->description))
	{
	?>
		<div class="eb-location-description"><?php echo $this->location->description; ?></div>
	<?php
	}
	?>
	<div id="eb_location_map" style="height:<?php echo $height; ?>px; width:100%;"></div>
</div>
<script type="text/javascript">
    Eb.jQuery(document).ready(function($){
        var mymap = L.map('eb_location_map').setView([<?php echo $this->location->lat ?>, <?php echo $this->location->long; ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
            zoom: <?php echo $zoomLevel;?>,
        }).addTo(mymap);

        var popupContent = '<h4><?php echo addslashes($this->location->name); ?></h4>';
        popupContent = popupContent + '<p><?php echo addslashes($this->location->address); ?></p>';
        var marker = L.marker([<?php echo $this->location->lat ?>, <?php echo $this->location->long;?>], {draggable: true}).addTo(mymap);
        marker.bindPopup(popupContent);
    });
</script>
