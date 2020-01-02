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

require_once(JPATH_ROOT . '/plugins/payplans/mtree/app/lib.php');

if (!is_array($value)) {
	$value = array($value);
}

JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);

$mosets = new PPMosets();

// If mostes tree not installed
if (!$mosets->exists()) {
	echo JText::_('COM_PAYPLANS_PLEASE_INSTALL_MTREE_BEFORE_USING_THIS_APPLICATION');
	return true;
}

$categories = $mosets->getCategories();
?>
<select name="<?php echo $name;?>[]" class="pp-autocomplete o-form-control" multiple="multiple" <?php echo $attributes;?>>
	<?php foreach ($categories as $category) { ?>
	<option value="<?php echo $category->category_id;?>" <?php echo in_array($category->category_id, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($category->title);?></option>
	<?php } ?>
</select>