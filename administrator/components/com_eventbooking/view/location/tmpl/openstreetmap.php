<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

$rootUri = JUri::root(true);
JFactory::getDocument()
	->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
	->addScript($rootUri . '/media/com_eventbooking/assets/js/autocomplete/jquery.autocomplete.min.js')
	->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css');

$config    = EventbookingHelper::getConfig();
$zoomLevel = $config->zoom_level ? (int) $config->zoom_level : 14;

if ($this->item->id)
{
	$coordinates = $this->item->lat . ',' . $this->item->long;
}
elseif (trim($config->center_coordinates))
{
	$coordinates = trim($config->center_coordinates);
}
else
{
	$coordinates = '37.09024,-95.712891';
}

$coordinates  = explode(',', $coordinates);
$editor       = JEditor::getInstance(JFactory::getConfig()->get('editor', 'none'));
$translatable = JLanguageMultilang::isEnabled() && count($this->languages);

if ($translatable)
{
	JHtml::_('behavior.tabstate');
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
    {
		var form = document.adminForm;
        if (pressbutton == 'cancel')
        {
            Joomla.submitform(pressbutton);
        }
        else
        {
            //Should validate the information here
            if (form.name.value == "")
            {
                alert("<?php echo JText::_('EN_ENTER_LOCATION'); ?>");
                form.name.focus();
                return;
            }

            Joomla.submitform(pressbutton);
        }
	};

    jQuery(document).ready(function($){
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

                marker.setLatLng(latlng);
                mymap.panTo(latlng);
            }
        });

    });
</script>
<form action="index.php?option=com_eventbooking&view=location" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<div class="row-fluid">
	<?php
	if ($translatable)
	{
		echo JHtml::_('bootstrap.startTabSet', 'field', array('active' => 'general-page'));
		echo JHtml::_('bootstrap.addTab', 'field', 'general-page', JText::_('EB_GENERAL', true));
	}
	?>
	<div class="span6">
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_NAME'); ?>
			</div>
			<div class="controls">
				<input class="text_area" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->item->name;?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_ALIAS'); ?>
			</div>
			<div class="controls">
				<input class="text_area" type="text" name="alias" id="alias" size="50" maxlength="250" value="<?php echo $this->item->alias;?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_ADDRESS'); ?>
			</div>
			<div class="controls">
				<input class="input-xxlarge" type="text" name="address" id="address" size="70" autocomplete="off" maxlength="250" value="<?php echo $this->item->address;?>" />
			</div>
		</div>

		<?php
			if (EventbookingHelper::isModuleEnabled('mod_eb_cities'))
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_CITY'); ?>
					</div>
					<div class="controls">
						<input class="text_area" type="text" name="city" id="city" size="30" maxlength="250" value="<?php echo $this->item->city;?>" />
					</div>
				</div>
			<?php
			}

			if (EventbookingHelper::isModuleEnabled('mod_eb_states'))
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_STATE'); ?>
					</div>
					<div class="controls">
						<input class="text_area" type="text" name="state" id="state" size="30" maxlength="250" value="<?php echo $this->item->state;?>" />
					</div>
				</div>
			<?php
			}
		?>

		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_COORDINATES'); ?>
			</div>
			<div class="controls">
				<input class="text_area" type="text" name="coordinates" id="coordinates" size="30" maxlength="250" value="<?php echo $this->item->lat.','.$this->item->long;?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_LAYOUT'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['layout']; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_CREATED_BY'); ?>
			</div>
			<div class="controls">
				<?php echo EventbookingHelper::getUserInput($this->item->user_id, 'user_id', 100) ; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo JText::_('EB_IMAGE'); ?></div>
			<div class="controls">
				<?php echo EventbookingHelperHtml::getMediaInput($this->item->image, 'image'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  JText::_('EB_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '90', '10' ) ; ?>
			</div>
		</div>
		<?php
			if (JLanguageMultilang::isEnabled())
			{
			?>
				<div class="control-group">
					<div class="control-label">
						<?php echo JText::_('EB_LANGUAGE'); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['language'] ; ?>
					</div>
				</div>
			<?php
			}
		?>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('EB_PUBLISHED') ; ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['published']; ?>
			</div>
		</div>
	</div>
	<div class="span6">
		<div id="map_canvas" style="width: 95%; height: 400px"></div>
	</div>

	<?php
	if ($translatable)
	{
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'field', 'translation-page', JText::_('EB_TRANSLATION', true));
		echo JHtml::_('bootstrap.startTabSet', 'field-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

		foreach ($this->languages as $language)
		{
			$sef = $language->sef;
			echo JHtml::_('bootstrap.addTab', 'field-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . JUri::root() . 'media/com_eventbooking/flags/' . $sef . '.png" />');
			?>
			<div class="control-group">
				<div class="control-label">
					<?php echo  JText::_('EB_NAME'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge" type="text" name="name_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'name_'.$sef}; ?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  JText::_('EB_ALIAS'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_'.$sef}; ?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo JText::_('EB_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
				</div>
			</div>
			<?php
			echo JHtml::_('bootstrap.endTab');
		}
		echo JHtml::_('bootstrap.endTabSet');
		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
	}
	?>
</div>
<div class="clearfix"></div>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<style>
	#map-canvas img{
		max-width:none !important;
	}
</style>