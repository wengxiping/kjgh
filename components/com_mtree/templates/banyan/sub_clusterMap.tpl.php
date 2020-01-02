<?php if (empty($this->arrLocations)) { ?>
<!-- No locations available -->
<?php return; }

// Map should be shown or closed by default?
$map_shown = true;
$map_button_label = JText::_('COM_MTREE_HIDE_PAGES_MAP');
if ($this->show_map == 2) {
    $map_shown = false;
    $map_button_label = JText::_('COM_MTREE_SHOW_PAGES_MAP');
}
?>
<?php include $this->loadTemplate('sub_clusterMapProviderScript.tpl.php');?>
<a href="javascript:void(0);" class="mt-map-toggle-button btn"><span><?php echo $map_button_label; ?></span></a>

<div class="mt-map-cluster">
	<div id="map" style="height: 400px; width: 100%; margin-bottom: 1.5em;<?php if (!$map_shown) {echo 'display:none;';}?>">
	</div>
</div>