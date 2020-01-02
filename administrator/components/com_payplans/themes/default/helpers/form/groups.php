<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<select name="<?php echo $name;?><?php echo $multiple ? '[]' : '';?>" class="o-form-control" <?php echo $attributes;?> data-group-<?php echo $name;?> <?php echo $multiple ? 'multiple="multiple"' : '';?> <?php echo (!$editable) ? ' disabled="disabled"': ''; ?>>
	<option value=""><?php echo JText::_('COM_PP_SELECT_A_GROUP');?></option>
	<?php foreach ($selections as $obj) { ?>
	<option value="<?php echo $obj->id;?>"<?php echo ($obj->selected) ? 'selected="selected"':''; ?>><?php echo JText::_($obj->title);?></option>
	<?php } ?>
</select>
