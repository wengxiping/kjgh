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
<?php 
require_once(JPATH_ROOT . '/plugins/payplans/acymailing6/app/lib.php');

if (!is_array($value)) {
	$value = array($value);
}

JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);
$lib = new PPAcym();
?>
<?php if ($lib->exists()) { ?>
	<?php $lists = $lib->getLists(); ?>
	<select name="<?php echo $name;?>[]" class="pp-autocomplete o-form-control" multiple="multiple" <?php echo $attributes;?>>
		<?php foreach ($lists as $list) { ?>
		<option value="<?php echo $list->id;?>" <?php echo $value && in_array($list->id, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($list->name);?></option>
		<?php } ?>
	</select>
<?php } else { ?>
	<?php echo JText::_('COM_PAYPLANS_PLEASE_INSTALL_ACYM_BEFORE_USING_THIS_APPLICATION'); ?>
<?php } ?>

