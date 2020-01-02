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

require_once(JPATH_ROOT . '/plugins/payplans/phoca/app/lib.php');

$lib = new PPPhoca();
$exists = $lib->exists();

if (!$exists) {
	echo JText::_('Phoca Download is not installed on the site');
	return;
}

$categories = $lib->getCategories();

if (!is_array($value)) {
	$value = array($value);
}

JHtml::_('formbehavior.chosen', '.pp-autocomplete', null);
?>
<select name="<?php echo $name;?>[]" class="pp-autocomplete o-form-control" multiple="multiple" <?php echo $attributes;?>>
	<?php foreach ($categories as $category) { ?>
	<option value="<?php echo $category->id;?>" <?php echo in_array($category->id, $value) ? 'selected="selected"' : '';?>><?php echo JText::_($category->title);?></option>
	<?php } ?>
</select>