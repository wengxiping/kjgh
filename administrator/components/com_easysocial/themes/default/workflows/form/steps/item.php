<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-wf-field<?php echo $field->element == 'header' ? ' es-wf-field--header' : ''; ?><?php echo $field->isConditional() ? ' has-condition' : ''; ?>"  data-field-item data-id="<?php echo $field->id; ?>" data-appid="<?php echo $field->app_id; ?>" data-ordering="<?php echo $field->ordering; ?>" data-isNew="<?php echo $workflow->id ? 'false' : 'true'; ?>" data-element="<?php echo $field->element; ?>" data-required="<?php echo $field->isRequired(); ?>">
	<div class="">
		<a href="javascript:void(0);" class="es-wf-field__drag-icon">
			<i class="fa fa-bars"></i>
		</a>
		<span class="<?php echo $field->isRequired() ? '' : 't-hidden'; ?>" data-field-item-required>*</span>
		<span data-field-item-title data-field-item-edit><?php echo JText::_($field->getTitle()); ?></span>
		<span class="es-wf-field__link-label<?php echo $field->isConditional() ? '' : ' t-hidden'; ?>" data-field-item-conditional><i class="fa fa-link"></i></span>
	</div>
	<div class="es-wf-field__action">
		<div class="es-wf-action">
			<span class="o-label o-label--primary t-lg-mr--md" data-field-item-element><?php echo strtoupper(str_ireplace('_', ' ', $field->element)); ?></span>
			<a href="javascript:void(0);" data-field-item-edit>
				<i class="far fa-edit"></i>
			</a>
			<a href="javascript:void(0);" data-field-item-move>
				<i class="fa fa-exchange-alt"></i>
			</a>
			<a href="javascript:void(0);" data-field-item-delete>
				<i class="fa fa-times"></i>
			</a>
		</div>
	</div>
</div>