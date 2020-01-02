<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
$micro = array('hour', 'minute', 'second');
?>

<div style="width: 100%" data-timer-wrapper data-timer="<?php echo $name; ?>">
		
	<div class="o-input-group">
		<input class="o-form-control" placeholder="" value="<?php echo $displayTitle; ?>" disabled type="text" data-timer-label <?php echo $attributes;?> >
		<span class="o-input-group__append">
			<button class="btn btn-pp-default-o t-hidden" type="button" data-timer-update-button><?php echo JText::_('COM_PP_UPDATE_BUTTON'); ?></button>
			<button class="btn btn-pp-default-o" type="button" data-timer-edit-button><?php echo JText::_('COM_PP_EDIT_BUTTON'); ?></button>
		</span>
	</div>

	<div class="editable t-hidden" data-timer-edit-wrapper>
		<div class="o-timers t-lg-mt--lg">
		<?php foreach ($segments as $key => $options) { ?>
			<?php if (in_array($key, $micro)) { continue; } ?>

			<div class="o-timers__item t-lg-mb--sm">
				<div class="o-input-group">
					<span class="o-input-group__prepend">
						<span class="o-input-group-text"><?php echo JText::_('COM_PAYPLANS_TIMER_' . $key . 'S'); ?></span>
					</span>
					<select class="o-form-control"
							data-timer-select data-key="<?php echo $key; ?>">
						<?php foreach ($options as $option) { ?>
							<option value="<?php echo $option->value; ?>"<?php echo ($option->selected) ? ' selected="selected"' : ''; ?>><?php echo $option->title; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>
		</div>


		<div class="o-timers t-lg-mt--lg<?php echo (!PP::config()->get('microsubscription')) ? ' t-hidden': ''; ?>">
		<?php foreach ($segments as $key => $options) { ?>
			<?php if (!in_array($key, $micro)) { continue; } ?>

			<div class="o-timers__item t-lg-mb--sm <?php echo $key == 'second' ? 't-hidden' : '';?>">
				<div class="o-input-group">
					<span class="o-input-group__prepend">
						<span class="o-input-group-text"><?php echo JText::_('COM_PAYPLANS_TIMER_' . $key . 'S'); ?></span>
					</span>
					<select class="o-form-control"
							data-timer-select data-key="<?php echo $key; ?>">
						<?php foreach ($options as $option) { ?>
							<option value="<?php echo $option->value; ?>"<?php echo ($option->selected) ? ' selected="selected"' : ''; ?>><?php echo $option->title; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
		<?php } ?>
		</div>



		<span class="muted">&emsp;<?php echo JText::_("COM_PAYPLANS_PLAN_LIFE_TIME_EXPIRATION_MSG"); ?></span>
	</div>

	<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" data-timer-hidden/>
</div>
