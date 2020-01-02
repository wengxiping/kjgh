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

$paymentMethods = $params->get('payment_method', array('Cash', 'Cheque', 'Wiretransfer'));
$paymentTypes = array();

foreach ($paymentMethods as $paymentMethod) {
	
	$obj = new stdClass();
	$obj->value = $paymentMethod;
	$obj->title = 'COM_PP_' . strtoupper($paymentMethod);

	$paymentTypes[] = $obj;
}

?>
<form action="<?php echo JRoute::_('index.php?tmpl=component');?>" method="post" data-offline-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_BANK_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_BANK', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<div class="o-from-group" style="background: #f4f4f4;padding: 20px 20px 10px; border-radius: 5px;">
			<div class="o-form-group">
				<span><?php echo JText::_('COM_PP_PAYMENT_BANK_NAME');?>:</span>&nbsp; <b><?php echo JText::_($params->get('bankname', '')); ?></b>
			</div>

			<div class="o-form-group">
				<span><?php echo JText::_('COM_PP_PAYMENT_BANK_ACCOUNT_NAME');?>:</span>&nbsp; <b><?php echo JText::_($params->get('account_name', '')); ?></b>
			</div>

			<div class="o-form-group">
				<span><?php echo JText::_('COM_PP_PAYMENT_BANK_ACCOUNT_NUMBER');?>:</span>&nbsp; <b><?php echo JText::_($params->get('account_number', '')); ?></b>
			</div>

			<div class="o-form-group">
				<span><?php echo JText::_('COM_PP_PAYMENT_BANK_INVOICE_REFERENCE_NUMBER');?>:</span>&nbsp; <b><?php echo $invoice->getKey(); ?></b>
			</div>
		</div>
	</div>
</div>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_PAYMENT_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::_('COM_PP_PAYMENT_VIA_BANK_COMPLETED'); ?>
		</div>

		<?php echo $this->html('floatlabel.lists', 'COM_PP_PAYMENT_METHOD', 'gateway_params[from]', '', '', array('data-offline-transaction-type' => ''), $paymentTypes); ?>

		<div class="t-hidden" data-offline-transaction-id>
			<?php echo $this->html('floatlabel.text', 'COM_PP_CHEQUE_OR_DEMAND_DRAFT_ID', 'gateway_params[id]', ''); ?>
		</div>
	</div>
</div>

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg"><?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?></button>
	</div>
</div>


<?php echo $this->html('form.hidden', 'view', 'payment'); ?>
<?php echo $this->html('form.hidden', 'layout', 'complete'); ?>
<?php echo $this->html('form.hidden', 'action', 'success', array('data-offline-action' => '')); ?>
<?php echo $this->html('form.hidden', 'gateway_params[amount]', $amount); ?>
<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>
</form>