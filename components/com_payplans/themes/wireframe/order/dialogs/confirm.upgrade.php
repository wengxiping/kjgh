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
<dialog>
	<width>450</width>
	<height>560</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]",
		"{planSelection}" : "[data-upgrade-plans]",
		"{form}": "[data-submit-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		},

		"{submitButton} click": function(element) {
			this.form().submit();
		}
	}
	</bindings>
	<title><?php echo JText::_('Upgrade'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" data-submit-form>

		<div class="pp-plan-upgrade">
			<div class="pp-plan-upgrade-info t-lg-mb--lg">
				<div class="pp-plan-upgrade-info__title">
					<?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_PREVIOUS_PLAN'); ?>
				</div>
				<div class="pp-plan-upgrade-info__desc">
					<?php echo $plan->getTitle(); ?>
				</div>
				<div class="pp-plan-upgrade-info__price">
					<?php $this->html('html.amount', $plan->getPrice(), $plan->getCurrency()); ?>
				</div>
			</div>
			<div class="pp-plan-upgrade-info pp-plan-upgrade-info--action t-lg-mb--lg">
				<div class="pp-plan-upgrade-info__title">
					<?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NEW_PLAN'); ?>
				</div>
				<div class="pp-plan-upgrade-info__desc">
					<?php echo JText::_('COM_PP_UPGRADE_SELECT_NEW_PLAN'); ?>
				</div>
				<div class="pp-plan-upgrade-info__select">
					<div class="o-select-group">
						<select name="upgrade_to" class="o-form-control" data-upgrade-plans>
							<option value="" selected="selected" disabled="disabled"><?php echo JText::_('COM_PP_UPGRADE_SELECT_NEW_PLAN_OPTION'); ?></option>
							<?php foreach ($upgrade_to as $uPlan) { ?>
								<option value="<?php echo $uPlan->getId(); ?>"><?php echo $uPlan->getTitle(); ?></option>
							<?php } ?>
						</select>
						<span class="o-select-group__drop"></span>
					</div>
				</div>

			</div>

			<table class="table table--borderless">
				<tbody>
					<tr>

						<td>
							<?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NEW_PRICE'); ?>
						</td>

						<td class="t-text--right" data-upgrade-amount>
							<?php echo $this->html('html.amount', '0', $plan->getCurrency()); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NOT_UTILIZED_PAYMENT'); ?>
						</td>

						<td class="t-text--right" data-ununtilized-amount>
							<?php echo $this->html('html.amount', '0', $plan->getCurrency()); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_PAYPLANS_UPGRADE_TAX_MESSAGE'); ?>
						</td>

						<td class="t-text--right" data-ununtilized-tax>
							<?php echo $this->html('html.amount', '0', $plan->getCurrency()); ?>
						</td>
					</tr>

				</tbody>
			</table>
			<hr class="pp-hr">
			<table class="table table--borderless">

				<tbody>
					<tr>
						<td>
							<b><?php echo JText::_('COM_PP_UPGRADE_TOTAL_PAYABLE_AMOUNT'); ?></b>
						</td>

						<td class="t-text--right" data-payable-amount>
							<b><?php echo $this->html('html.amount', '0', $plan->getCurrency()); ?></b>
						</td>
					</tr>
				</tbody>
			</table>

		</div>

		<?php echo $this->html('form.action', 'order', 'processUpgrade'); ?>
		<?php echo $this->html('form.hidden', 'key', $key); ?>

		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-danger-o btn-sm"><?php echo JText::_('COM_PP_YES_BUTTON'); ?></button>
	</buttons>
</dialog>
