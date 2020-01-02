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
<div style="<?php echo $minWidth ? 'min-width:' . $minWidth . 'px;' : '';?>">
	<select name="<?php echo $name;?>" id="<?php echo $id;?>" class="o-form-control" <?php echo $readOnly; ?> style="<?php echo $minHeight ? 'min-height:' . $minHeight . 'px;' : '';?>" multiple>
		<?php foreach ($groups as $group) { ?>
		<option value="<?php echo $group->id;?>" <?php echo in_array($group->id, $selected) ? 'selected="selected"' : '';?>>
			<?php echo str_repeat('- ', $group->level);?> <b><?php echo $group->title;?></b>
		</option>
		<?php } ?>
	</select>
</div>