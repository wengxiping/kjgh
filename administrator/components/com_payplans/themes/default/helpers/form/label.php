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
?>
<label class="o-control-label">
	<?php echo $label; ?>
	<?php if ($required) { ?>
		&#42;
	<?php } ?>

	<?php if ($help) { ?>
	<span class="o-control-label-tooltips">
		<i class="fa fa-question-circle t-lg-pull-right" <?php echo $this->html('bootstrap.popover', $label, $desc, 'bottom' , '' , true);?>></i>
	</span>
	<?php } ?>
</label>