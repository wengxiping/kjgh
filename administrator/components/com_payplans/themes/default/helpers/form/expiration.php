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
<select name="<?php echo $name;?>" class="o-form-control" <?php echo $attributes;?> default="forever">
    <option value="forever" data-option-items="price" <?php echo $value == 'forever' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_PP_PLAN_TIME_EXPIRATION_FOREVER'); ?></option>
    <option value="fixed" data-option-items="expiration,price" <?php echo $value == 'fixed' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_PP_PLAN_TIME_EXPIRATION_FIXED'); ?></option>
    <option value="recurring" data-option-items="recurrence_count,recurrence_validation,expiration,price" <?php echo $value == 'recurring' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_PP_PLAN_TIME_EXPIRATION_RECURRING'); ?></option>
    <option value="recurring_trial_1" data-option-items="trial_price_1,trial_time_1,recurrence_count,recurrence_validation,expiration,price" <?php echo $value == 'recurring_trial_1' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_PP_PLAN_TIME_EXPIRATION_RECURRING_TRIAL_1'); ?></option>
    <option value="recurring_trial_2" data-option-items="trial_price_1,trial_time_1,trial_price_2,trial_time_2,recurrence_count,recurrence_validation,expiration,price" <?php echo $value == 'recurring_trial_2' ? 'selected="selected"' : '';?>><?php echo JText::_('COM_PP_PLAN_TIME_EXPIRATION_RECURRING_TRIAL_2'); ?></option>
</select>