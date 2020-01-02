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
<div class="o-control-input">

	<?php if ($editable) { ?>
	<select class="o-form-control" name="<?php echo $name;?>" <?php echo $multiple ? ' multiple="multiple" style="min-height: 100px;"' : '';?> <?php echo $attributes; ?>>
		
		<?php if (!$multiple) { ?>
			<option value=""><?php echo JText::_('COM_PP_SELECT_PLAN_GROUP'); ?></option>
		<?php } ?>

		<?php foreach ($groups as $group) { ?>
			<option value="<?php echo $group->getId();?>" <?php echo $group->isSelected ? 'selected="selected"' : ''; ?>> 
				<?php echo $group->getTitle();?>
			</option>
		<?php } ?>
	</select>
	<?php } else { ?>
		<?php echo $group->getTitle(); ?>
	<?php } ?>
</div>