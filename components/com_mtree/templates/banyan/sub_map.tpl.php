<?php
if ($this->config->get('use_map') == 1 && !empty($this->link->lat) && !empty($this->link->lng) && !empty($this->link->zoom)) {
    $width = '100%';
    $height = '300px';
    ?><div class="map">

	<div class="title"><?php echo JText::_('COM_MTREE_MAP'); ?></div>

	<?php include $this->loadTemplate('sub_mapProviderScript.tpl.php');?>

	<div id="map" style="max-width: none;width:<?php echo $width; ?>;height:<?php echo $height; ?>"></div>

	</div><?php
}