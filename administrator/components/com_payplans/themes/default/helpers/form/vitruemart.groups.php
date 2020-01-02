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
<select name="<?php echo $name;?>" class="pp-autocomplete o-form-control" <?php echo $attributes;?>>
	<option value=""><?php echo JText::_('COM_PP_SELECT_A_GROUP');?></option>
	<?php foreach ($groups as $group) {
		// Seems like Virtuemart doesn't translate these shopper group options from the backend language
		$shopperGroupName = str_replace('COM_VIRTUEMART', 'COM_PP_VIRTUEMART', $group->shopper_group_name);
	 ?>
	<option value="<?php echo $group->virtuemart_shoppergroup_id;?>" <?php echo $value == $group->virtuemart_shoppergroup_id ? 'selected="selected"' : '';?>><?php echo JText::_($shopperGroupName);?></option>
	<?php } ?>
</select>
