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
?>

<div class="o-form-group">
	<div class="o-radio">
		<?php if ($plan->advancedpricing) { ?>
			<input id="modifier-radio<?php echo $plan->getId(); ?>" type="radio" value="modifier" name="plan-extra<?php echo $plan->getId(); ?>" data-modifier-radio>
			<label for="modifier-radio<?php echo $plan->getId(); ?>">
				<?php echo JText::_('COM_PP_PLAN_SUBSCRIBE_FOR'); ?>
			</label>
		<?php } ?>
	</div>
</div>

<div class="o-form-group" data-plan-modifier>
	<div class="o-select-group">
		<select name="planmodifier" id="planmodifier" class="o-form-control" data-modifier-selection data-id="<?php echo $modifier->getId(); ?>" >
			<option value="default">
				<?php echo $plan->getTitle(); ?> <?php echo $plan->getCurrency(); ?><?php echo $plan->getPrice(); ?> <?php echo $plan->separator; ?> <?php echo $this->html('html.plantime', PPHelperPlan::convertIntoTimeArray($plan->getRawExpiration())); ?>
			</option>
			<?php foreach ($modifier->options as $option) { ?>
				<option value="<?php echo $option->title;?>_<?php echo $option->price;?>_<?php echo $option->time; ?>_<?php echo $modifier->getId();?>">
					<?php echo $option->title; ?> <?php echo $plan->getCurrency(); ?><?php echo $option->price; ?> <?php echo $plan->separator; ?> <?php echo $this->html('html.plantime', PPHelperPlan::convertIntoTimeArray($option->time)); ?>
				</option>
			<?php } ?>
		</select>
		<label for="" class="o-select-group__drop"></label>
	</div>
</div>

