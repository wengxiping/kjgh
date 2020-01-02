<?php
$map_provider_template_suffix = ucfirst(strtolower($this->config->get('map_provider')));
include $this->loadTemplate( 'sub_clusterMapProviderScript'.$map_provider_template_suffix.'.tpl.php' );
?>