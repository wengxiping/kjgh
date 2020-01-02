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
	<width>900</width>
	<height>600</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{upgradeButton}": "[data-upgrade-button]",

		"{form}": "[data-upgrade-form]",
		"{planSelection}": "[data-upgrade-new-plan]",
		"{regularPrice}": "[data-upgrade-regular-price]",
		"{unutilizedAmount}": "[data-upgrade-unutilized]",
		"{unutilizedTax}": "[data-upgrade-unutilized-tax]",
		"{payableAmount}": "[data-upgrade-payable-amount]",
		"{paymentDetails}": "[data-upgrade-payment-details]",
		"{paymentOptions}": "[data-upgrade-payment-options]",
		"{alertWrapper}": "[data-alert-wrapper]"
	}
	</selectors>
	<bindings type="javascript">
	{
		init: function() {
		},

		showDetails: function() {
			this.paymentDetails().removeClass('t-hidden');
			this.paymentOptions().removeClass('t-hidden');
			this.upgradeButton().removeAttr('disabled');
		},

		hideDetails: function() {
			this.paymentDetails().addClass('t-hidden');
			this.paymentOptions().addClass('t-hidden');
			this.upgradeButton().attr('disabled', 'disabled');
		},

		validate: function() {

			this.alertWrapper().addClass('t-hidden');

			// make sure payment mode selected
			if (PayPlans.$('input[name="type"]:checked').length == 0) {
				this.alertWrapper().html('<?php echo JText::_('COM_PP_UPGRADE_SUBSCRIPTION_SELECT_PAYMENT_MODE', true); ?>');
				this.alertWrapper().removeClass('t-hidden');
				return false;
			}

			return true;
		},

		"{closeButton} click": function() {
			this.parent.close();
		},

		"{upgradeButton} click": function() {

			if (this.validate()) {
				this.form().submit();
			}

			return false;
		},

		"{planSelection} change": function(element, event) {
			var newPlan = element.val();
			var self = this;

			if (!newPlan) {
				self.hideDetails();
				return;
			}

			PayPlans.ajax('admin/views/upgrades/getUpgradeDetails', {
				"upgrade_to": newPlan,
				"id": "<?php echo $subscription->getId();?>"
			}).done(function(data) {

				self.regularPrice().html(data.price);
				self.unutilizedAmount().html(data.unutilized);
				self.unutilizedTax().html(data.unutilizedTax);
				self.payableAmount().html(data.payableAmount);

				self.showDetails();
			});
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_PP_UPGRADE_SELECTED_PLAN'); ?></title>
	<content>
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="o-form-horizontal" data-upgrade-form>
			<div class="o-form-group">
				<?php echo $this->html('form.label', 'Current Plan'); ?>

				<div class="o-control-input">
					<?php echo $subscription->getTitle();?> (<b><?php echo $this->html('html.amount', $subscription->getTotal(), $order->getCurrency());?></b>)
				</div>
			</div>

			<div class="o-form-group">
				<?php echo $this->html('form.label', 'Upgrade to Plan'); ?>

				<div class="o-control-input">
					<select name="upgrade_to" class="o-form-control" data-upgrade-new-plan>
						<option value="" selected="selected" disabled="disabled"><?php echo JText::_('COM_PP_UPGRADE_SELECT_NEW_PLAN_OPTION'); ?></option>
						<?php foreach ($plans as $plan) { ?>
						<option value="<?php echo $plan->getId();?>"><?php echo $plan->getTitle();?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="o-form-group t-hidden" data-upgrade-payment-details>
				<table class="app app-table">
					<thead>
						<tr>
							<th class="text-left">
								<?php echo JText::_('Payment Details'); ?>
							</th>
							<th class="center" width="30%">
								&nbsp;
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NEW_PRICE');?></td>
							<td class="center">
								<span data-upgrade-regular-price></span>
							</td>
						</tr>

						<tr>
							<td><?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NOT_UTILIZED_PAYMENT');?></td>
							<td class="center">
								<span data-upgrade-unutilized></span>
							</td>
						</tr>

						<tr>
							<td><?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NOT_UTILIZED_TAX');?></td>
							<td class="center">
								<span data-upgrade-unutilized-tax></span>
							</td>
						</tr>

						<tr>
							<td>
								<b><u><?php echo JText::_('COM_PAYPLANS_UPGRADES_DETAILS_NEW_CURRENT_PAYABLE_AMOUNT');?></b></u>
							</td>
							<td class="center">
								<u><b><span data-upgrade-payable-amount></span></b></u>
							</td>
						</tr>
					</tbody>
				</table>
			</div>


			<div class="t-hidden" data-upgrade-payment-options>
				<h3><?php echo JText::_('COM_PP_PAYMENT_MODE');?></h3>

				<div class="o-form-group">
					<div>
						<?php echo $this->html('form.radio', 'type', 'free', false, 'COM_PP_UPGRADE_TYPE_FREE', 'free-upgrade'); ?>
						<?php echo $this->html('form.radio', 'type', 'offline', false, 'COM_PP_UPGRADE_TYPE_OFFLINE', 'offline-upgrade'); ?>
						<?php echo $this->html('form.radio', 'type', 'user', false, 'COM_PP_UPGRADE_TYPE_USER', 'partial-upgrade'); ?>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'subscription', 'upgrade'); ?>
			<?php echo $this->html('form.hidden', 'id', $subscription->getId()); ?>
		</form>
		<div class="alert t-hidden" data-alert-wrapper></div>
	</content>
	<buttons>
		<button type="button" class="btn btn-pp-default btn-sm" data-close-button><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
		<button type="button" class="btn btn-pp-default btn-sm" disabled="disabled" data-upgrade-button><?php echo JText::_('COM_PP_UPGRADE_BUTTON'); ?></button>
	</buttons>
</dialog>
