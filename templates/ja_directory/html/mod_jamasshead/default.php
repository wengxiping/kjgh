<?php
/**
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

$background = "";
if(isset($masshead['params']['background'])) {
  $background = $masshead['params']['background'];
} else {
  $background = "images/joomlart/bg-masthead.jpg"; 
}
?>
<div class="jamasshead<?php echo $params->get('moduleclass_sfx','')?>" style="background-image: url(<?php echo $background ?>)" >
	<h3 class="jamasshead-title"><?php echo $masshead['title']; ?></h3>
	<div class="jamasshead-description"><?php echo $masshead['description']; ?></div>
</div>	