<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

EventbookingHelperJquery::validateForm();

$rootUri = JUri::root(true);
JFactory::getDocument()
	->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
	->addScript($rootUri . '/media/com_eventbooking/assets/js/autocomplete/jquery.autocomplete.min.js')
	->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css');

/* @var EventbookingHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$span7Class        = $bootstrapHelper->getClassMapping('span7');
$span5Class        = $bootstrapHelper->getClassMapping('span5');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');

$config = EventbookingHelper::getConfig();

if (trim($config->center_coordinates))
{
	$coordinates = $config->center_coordinates;
}
else
{
	$coordinates = '37.09024,-95.712891';
}

$zoomLevel = $config->zoom_level ? (int) $config->zoom_level : 14;
$coordinates = explode(',', $coordinates);
?>
<form action="index.php?option=com_eventbooking&view=location" method="post" name="adminForm" id="adminForm" class="form-horizontal">
<h1 class="eb-page-heading"><?php echo $this->escape(JText::_('EB_ADD_EDIT_LOCATION')); ?></h1>
<div class="row-fluid">
    <div  class="<?php echo $span5Class ?>">
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo JText::_('EB_NAME'); ?>
    			<span class="required">*</span>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
    			<input class="input-large validate[required]" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->escape($this->item->name); ?>" />
    		</div>
    	</div>
    
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo JText::_('EB_ADDRESS'); ?>
    			<span class="required">*</span>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
      	         <input class="input-xlarge validate[required]" type="text" name="address" id="address" size="70" autocomplete="off" maxlength="250" value="<?php echo $this->escape($this->item->address);?>" />
    		</div>
    	</div>
	    <?php
	    if (JModuleHelper::isEnabled('mod_eb_cities'))
	    {
		?>
		    <div class="control-group">
			    <label class="control-label">
				    <?php echo JText::_('EB_CITY'); ?>
			    </label>
			    <div class="controls">
				    <input class="text_area" type="text" name="city" id="city" size="30" maxlength="250" value="<?php echo $this->escape($this->item->city);?>" />
			    </div>
		    </div>
		<?php
	    }

	    if (JModuleHelper::isEnabled('mod_eb_states'))
	    {
		?>
		    <div class="control-group">
			    <label class="control-label">
				    <?php echo JText::_('EB_STATE'); ?>
			    </label>
			    <div class="controls">
				    <input class="text_area" type="text" name="state" id="state" size="30" maxlength="250" value="<?php echo $this->escape($this->item->state);?>" />
			    </div>
		    </div>
		<?php
	    }
	    ?>
    	<div class="<?php echo $controlGroupClass;  ?>">
    		<label class="<?php echo $controlLabelClass; ?>">
    			<?php echo JText::_('EB_COORDINATES'); ?>
    		</label>
    		<div class="<?php echo $controlsClass; ?>">
    			<input class="input-large validate[required]" type="text" name="coordinates" id="coordinates" size="30" maxlength="250" value="" />
    		</div>
    	</div>
	    <div class="<?php echo $controlGroupClass;  ?>">
		    <label class="<?php echo $controlLabelClass; ?>">
			    <?php echo JText::_('EB_PUBLISHED') ; ?>
		    </label>
		    <?php echo $this->lists['published']; ?>
	    </div>
     </div>
     <div class="<?php echo $span7Class ?>">
         <div id="map_canvas" style="width: 95%; height: 350px"></div>
     </div>
     <div class="row-fluid">
     	<button id="save_location" class="<?php echo $btnPrimary; ?>" type="submit"><span class="icon-save"></span><?php echo JText::_('EB_SAVE'); ?></button>
   	</div>
</div>
	<div class="clearfix"></div>
	<input type="hidden" name="published" value="1" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $("#adminForm").validationEngine('attach', {
            onValidationComplete: function(form, status){
                if (status == true) {
                    $.ajax({
                        type:'POST',
                        data: $('#adminForm input[type=\'radio\']:checked, #adminForm input[type=\'checkbox\']:checked, #adminForm input[type=\'text\'], #adminForm input[type=\'hidden\'],  #adminForm select'),
                        dataType: 'json',
                        url: siteUrl + 'index.php?option=com_eventbooking&task=location.save_ajax',
                        beforeSend: function () {
                            $('#save_location').prop('disabled',true);
                        },
                        success : function(json){
                            $( "#adminForm" ).before( '<div class="alert alert-success"><h4 class="alert-heading">Message</h4><div class="alert-message">'+json['message']+'</div></div>' );
                            $('#save_location').prop('disabled',false);
                            var parentJQuery = parent.jQuery;
                            parentJQuery('#location_id').append(parentJQuery('<option>', {
                                value: json['id'],
                                text: json['name']
                            }));
                            parentJQuery('#location_id').val(json['id']);
                            parentJQuery.colorbox.close();
                        }
                    })
                    return false;
                }
                return false;
            }
        });

        var mymap = L.map('map_canvas', {
            center: [<?php echo $coordinates[0]; ?>, <?php echo $coordinates[1]; ?>],
            zoom: <?php echo $zoomLevel; ?>,
            zoomControl: true,
            attributionControl: false,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            id: 'mapbox.streets',
        }).addTo(mymap);

        var marker = L.marker([<?php echo $coordinates[0] ?>, <?php echo $coordinates[1];?>], {draggable: false}).addTo(mymap);

        $('#address').autocomplete({
            serviceUrl: '<?php echo JUri::base(true).'/index.php?option=com_eventbooking&task=location.search'; ?>',
            minChars: 3,
            onSelect: function (suggestion) {
                var form = document.adminForm;

                if (suggestion.name && form.name.value === '')
                {
                    form.name.value = suggestion.name;
                }

                if (suggestion.coordinates)
                {
                    form.coordinates.value = suggestion.coordinates;
                }

                if (suggestion.city)
                {
                    $('#city').val(suggestion.city);
                }

                if (suggestion.state)
                {
                    $('#state').val(suggestion.state);
                }

                var newPosition = L.latLng(suggestion.lat, suggestion.long);

                marker.setLatLng(newPosition);
                mymap.panTo(newPosition);
            }
        });
    });
</script>