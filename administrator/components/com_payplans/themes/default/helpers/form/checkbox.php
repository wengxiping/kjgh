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
<div <?php echo $attributes;?>>
	<?php foreach ($options as $option) { ?>
		<div class="o-checkbox" >
			<input type="checkbox" 
			name="<?php echo $name;?>"
			id="item-checkbox-<?php echo $option->value;?>"
			value="<?php echo !empty($option->value) ? $option->value : $option->title; ?>"
			<?php echo (!empty($value) && in_array($option->value, $value)) ? 'checked="checked"' : ''; ?> 
			/>
			<label for="item-checkbox-<?php echo $option->value;?>" class="option"><?php echo $option->title; ?></label>
		</div>
	<?php } ?>
</div>