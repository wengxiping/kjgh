<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$position = !isset($position) ? 'default' : $position;
$attribs = !isset($attribs) ? array() : $attribs;
?>
<?php if ($wrapper) { ?>
<div class="pp-position">
<?php } ?>

	<?php if (isset($result) && isset($result[$position])) { ?>
		<?php if ($wrapper) { ?>
		<div class="<?php echo $position;?>">
		<?php } ?>

		<?php echo $result[$position]; ?>
		<?php if ($wrapper) { ?>
		</div>
		<?php } ?>
	<?php } ?>

	<?php

	// @TODO:
	// // Rendering of modules
	// //$modules = PayplansHelperTemplate::_renderModules($position, $attribs);

	// foreach ($modules as $html) {
	// 	echo $html;
	// }
	?>
<?php if ($wrapper) { ?>
</div>
<?php } ?>