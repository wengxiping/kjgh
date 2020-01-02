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
<div class="o-form-group" <?php echo $wrapperAttributes;?>>
	<?php echo $this->html('form.label', $title); ?>

	<div class="col-md-7 o-control-input">
		<?php echo $this->html('form.toggler', $name, $this->config->get($name), $name, $attributes);?>

		<?php if ($note) { ?>
		<div class="small">
			<?php echo $note;?>
		</div>
		<?php } ?>
	</div>
</div>