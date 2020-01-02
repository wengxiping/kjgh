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

<div class="o-form-group" data-plan-advancedpricing data-price-perday="<?php echo $plan->getPricePerDay(); ?>">
	<div class="o-radio">
		<?php if ($plan->modifiers) { ?>
			<input id="advancedpricing<?php echo $plan->getId(); ?>" type="radio" value="advancedpricing" name="plan-extra<?php echo $plan->getId(); ?>" data-advancedpricing-radio>
			<label for="advancedpricing<?php echo $plan->getId(); ?>">
				<?php echo JText::_('COM_PP_PLAN_SUBSCRIBE_FOR'); ?>
			</label>
		<?php } ?>
	</div>
</div>

<div class="o-form-group">
	<div class="o-select-group">
		<select name="total" id="total" class="o-form-control" data-number-of-purchase>
			<?php for ($i = $advancedpricing->units_min; $i <= $advancedpricing->units_max; $i++) { ?>
				<option value="<?php echo $i; ?>">
					<?php echo $i; ?> <?php echo $advancedpricing->units_title; ?>
				</option>
			<?php } ?>
		</select>
		<label for="" class="o-select-group__drop"></label>
	</div>
</div>
<div class="">
	<table class="table table--bordered-horizontal t-bg--default">
		<thead>
			<tr class="t-bg--shade">
				<th>
				</th>
				<th>
					<?php echo JText::_('COM_PP_PLAN_TIME'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_PP_PLAN_PRICE_UNIT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_PP_PLAN_ACTUAL_PRICE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_PP_PLAN_PRICE_TO_PAY'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_PP_PLAN_SAVINGS'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($advancedpricing->priceset as $set) { ?>
				<?php $actualPrice = $plan->getPricePerDay() * PPHelperPlan::convertTimeArrayToDays($set['duration']); ?>
				<?php $priceToPay = $set['price'] * 1; ?>
				<?php $savings = $actualPrice - $priceToPay; ?>

				<tr data-price-set data-price="<?php echo $set['price']; ?>" data-days="<?php echo PPHelperPlan::convertTimeArrayToDays($set['duration']); ?>">
					<td>
						<input id="priceset-select" type="radio" name="priceset" value="<?php echo $priceToPay ?>" data-duration="<?php echo $set['duration']; ?>" data-priceset-selection />
					</td>
					<td>
						<?php echo PP::string()->formatTimer($set['duration']); ?>
					</td>
					<td>
						<?php echo $this->html('html.amount', $set['price'], $this->config->get('currency')); ?>
					</td>
					<td data-actual-price>
						<?php echo $this->html('html.amount', $actualPrice, $this->config->get('currency')); ?>
					</td>
					<td data-price-topay>
						<?php echo $this->html('html.amount', $priceToPay, $this->config->get('currency')); ?>
					</td>
					<td data-savings>
						<?php echo $this->html('html.amount', $savings, $this->config->get('currency')); ?>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>



