<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-form-group o-form-group--float is-filled" data-pp-form-group>	
	<div class="o-select-group">
		<?php echo $this->html('form.country', $name, $value, '', $attributes); ?>
		<span class="o-select-group__drop"></span>
	</div>

	<label class="o-control-label" for="country"><?php echo isset($attributes['required']) ? '*' : ''; ?><?php echo $label;?></label>

	<div class="t-hidden" data-error-message>
		<div class="help-block t-text--danger"><?php echo JText::_('COM_PP_FIELD_REQUIRED_MESSAGE'); ?></div>
	</div>
</div>
