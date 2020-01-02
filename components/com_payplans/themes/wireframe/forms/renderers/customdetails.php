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
<div class="o-card o-card--borderless t-lg-mb--lg">
	<?php if ($title) { ?>
	<div class="o-card__header o-card__header--nobg t-lg-pl--no">
		<?php echo $title;?>
	</div>
	<?php } ?>

	<?php foreach ($sections as $section) { ?>
	<div class="o-card__body">
		<?php foreach ($section->items as $field) { ?> 
			<?php echo $this->html(
				'floatLabel.' . $field->type,
				$field->title, $field->type === 'checkbox' ? $type . '[' . $field->name . '][]' : $type . '[' . $field->name . ']', $field->type === 'checkbox' ? $field->value : $this->html('string.escape', $field->value), '', $field->attributes, $field->options); ?>
		<?php } ?>
	</div>
	<?php } ?>
</div>