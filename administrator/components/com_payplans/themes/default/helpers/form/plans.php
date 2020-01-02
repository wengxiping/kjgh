<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<select class="o-form-control" name="<?php echo $name;?>" <?php echo $attributes;?> <?php echo $multiple ? 'multiple="multiple"' : '';?> <?php echo (!$editable) ? ' disabled="disabled"': ''; ?> <?php echo $multiple ? 'style="min-height: 180px;"' : '';?>>

	<?php if ($allowEmpty) { ?>
		<option value=""><?php echo JText::_('COM_PP_SELECT_A_PLAN');?></option>
	<?php } ?>
	
	<?php foreach ($plans as $plan) { ?>
		<option value="<?php echo $plan->getId();?>" <?php echo $plan->isSelected ? 'selected="selected"' : ''; ?>> 
			<?php echo JText::_($plan->getTitle());?>
		</option>
	<?php } ?>
</select>
