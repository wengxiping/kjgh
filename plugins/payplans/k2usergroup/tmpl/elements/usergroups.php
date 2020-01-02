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

require_once(JPATH_ROOT . '/plugins/payplans/k2usergroup/app/lib.php');

if (!is_array($value)) {
	$value = array($value);
}

JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);
$k2usergroup = new PPK2usergroup();
?>
<?php if ($k2usergroup->exists()) { ?>
	<?php $usergroups = $k2usergroup->getK2UserGroups(); ?>
	<select name="<?php echo $name;?>" class="pp-autocomplete o-form-control" <?php echo $attributes;?>>
		<?php foreach ($usergroups as $usergroup) { ?>
		<option value="<?php echo $usergroup->groups_id;?>" <?php echo in_array($usergroup->groups_id, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($usergroup->name);?></option>
		<?php } ?>
	</select>
<?php } else { ?>
	<?php echo JText::_('COM_PAYPLANS_PLEASE_INSTALL_K2_BEFORE_USING_THIS_APPLICATION'); ?>
<?php } ?>

