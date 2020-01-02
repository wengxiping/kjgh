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
<form action="<?php echo $formUrl;?>" method="post" data-payumoney-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_PAYUMONEY', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('floatlabel.text', 'COM_PP_MOBILE_NUMBER', 'phone', ''); ?>
	</div>
</div>

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg">
			<?php echo JText::_('Complete Payment');?>
		</button>
	</div>
</div>

<?php echo $this->html('form.hidden', 'key', $params->get('merchant_key', '')); ?>
<?php echo $this->html('form.hidden', 'txnid', $transactionId); ?>
<?php echo $this->html('form.hidden', 'amount', $amount); ?>
<?php echo $this->html('form.hidden', 'productinfo', htmlspecialchars($productInfo)); ?>
<?php echo $this->html('form.hidden', 'surl', $surl); ?>
<?php echo $this->html('form.hidden', 'furl', $furl); ?>
<?php echo $this->html('form.hidden', 'curl', $curl); ?>
<?php echo $this->html('form.hidden', 'hash', $hash); ?>
<?php echo $this->html('form.hidden', 'udf1', $userId); ?>
<?php echo $this->html('form.hidden', 'email', $email); ?>
<?php echo $this->html('form.hidden', 'firstname', $userName); ?>
<?php echo $this->html('form.hidden', 'service_provider', 'payu_paisa'); ?>
</form>
