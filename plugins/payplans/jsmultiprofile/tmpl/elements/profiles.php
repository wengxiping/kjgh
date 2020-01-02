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

require_once(JPATH_ROOT . '/plugins/payplans/jsmultiprofile/app/lib.php');

if (!is_array($value)) {
	$value = array($value);
}

JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);
$profileLib = new PPMultiprofile();
?>
<?php if ($profileLib->exists()) { ?>
	<?php $profiles = $profileLib->getProfiles(); ?>
	<select name="<?php echo $name;?>[]" class="pp-autocomplete o-form-control" <?php echo $attributes;?>>
		<?php foreach ($profiles as $profile) { ?>
		<option value="<?php echo $profile->id;?>" <?php echo in_array($profile->id, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($profile->name);?></option>
		<?php } ?>
	</select>
<?php } else { ?>
	<?php echo JText::_('COM_PP_PLEASE_INSTALL_JOMSOCIAL'); ?>
<?php } ?>

