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
defined('_JEXEC') or die('Unauthorized Access');
?>
<select name="<?php echo $name;?>" class="o-form-control" id="<?php echo $id;?>" <?php echo $attributes;?>>
	<?php foreach ($options as $option) { ?>
	<option value="<?php echo $option->value;?>" <?php echo $value == $option->value ? 'selected="selected"' : '';?>><?php echo JText::_($option->title);?></option>
	<?php } ?>
</select>