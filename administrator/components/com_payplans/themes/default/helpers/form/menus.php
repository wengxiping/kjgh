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
<select name="<?php echo $name;?>[]" class="o-form-control pp-autocomplete" multiple="true" <?php echo $attributes;?>>
	<?php foreach ($menus as $menu) { ?>
		<?php if ($menu->links) { ?>
			<optgroup label="<?php echo $menu->title;?>">
			<?php foreach ($menu->links as $link) { ?>
				<option value="<?php echo $link->value;?>" <?php echo in_array($link->value, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($link->text);?></option>
			<?php } ?>
			</optgroup>
		<?php } ?>
	<?php } ?>
</select>