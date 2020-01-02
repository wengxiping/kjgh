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
<select class="o-form-control" name="app_params[profile_type]">
	<option value=""><?php echo JText::_('Select Profile Type'); ?></option>

	<?php foreach ($profileTypes as $profile) { ?>
		<option value="<?php echo $profile->id;?>" <?php echo in_array($profile->id, $selectedProfile) ? 'selected="selected"' : ''; ?>> 
			<?php echo $profile->title;?>
		</option>
	<?php } ?>
</select>