<?php 
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();

//render modules that are using this position 
?>
<div class="pp-invoice-download clearfix <?php if(count($plugin_result) != 1){echo "well";} ?>" >
<?php 
		$position = 'pp-invoice-thanks-action';
		echo $this->loadTemplate('partial_position',compact('plugin_result','position'));
		?>
</div> 
<?php