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
<div class="o-control-input">
	<select class="o-form-control" id="<?php echo $id;?>" name="<?php echo $name;?>[]" multiple="multiple" style="min-height: 100px;">
		<?php foreach ($modules as $module) { ?>
			<option value="<?php echo $module->id;?>" <?php echo ($selected && in_array($module->id, $selected)) ? 'selected="selected"' : ''; ?>> 
				<?php echo $module->title;?>
			</option>
		<?php } ?>
	</select>
</div>