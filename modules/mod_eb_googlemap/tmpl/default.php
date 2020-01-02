<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addScript('https://maps.googleapis.com/maps/api/js?key=' . $config->get('map_api_key', ''));
?>
<div align="left" id="map<?php echo $module->id;?>" style="position:relative; width: <?php echo $width; ?>%; height: <?php echo $height?>px"></div>
<script type="text/javascript">
    Eb.jQuery(document).ready(function ($) {
        var markerArray = [];
        var myHome = new google.maps.LatLng(<?php echo $homeCoordinates; ?>);
		<?php
		for($i = 0, $n = count($locations); $i < $n; $i++)
		{
		    $location = $locations[$i];
		?>
            var eventListing<?php echo $location->id?> = new google.maps.LatLng(<?php echo $location->lat; ?>, <?php echo $location->long; ?>);
		<?php
		}
		?>
        var mapOptions = {
            zoom: <?php echo $zoomLevel; ?>,
            streetViewControl: true,
            scrollwheel: false,
            mapTypeControl: true,
            panControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: myHome,
        };
        var map = new google.maps.Map(document.getElementById("map<?php echo $module->id; ?>"), mapOptions);
        var infoWindow = new google.maps.InfoWindow();

        function makeMarker(options) {
            var pushPin = new google.maps.Marker({map: map});
            pushPin.setOptions(options);
            google.maps.event.addListener(pushPin, 'click', function () {
                infoWindow.setOptions(options);
                infoWindow.open(map, pushPin);
            });
            markerArray.push(pushPin);
            return pushPin;
        }

        google.maps.event.addListener(map, 'click', function () {
            infoWindow.close();
        });
		<?php
		foreach($locations as $location)
		{

		?>
            makeMarker({
                position: eventListing<?php echo $location->id?>,
                title: "<?php echo addslashes($location->name);?>",
                content: '<?php echo $location->popupContent; ?>',
                icon: new google.maps.MarkerImage('<?php echo $markerUri;?>')
            });
		<?php
		}
		?>
    });
</script>

