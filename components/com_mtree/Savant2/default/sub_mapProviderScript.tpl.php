<?php

$map_provider_template_suffix = ucfirst(strtolower($this->config->get('map_provider')));

include $this->loadTemplate( 'sub_mapProviderScript'.$map_provider_template_suffix.'.tpl.php' );

?>