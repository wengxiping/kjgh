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
<form method="post" autocomplete="off" action="<?php echo $postUrl;?>" data-pp-payuturkey-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_PAYUTURKEY', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('form.card', array('name' => 'card-owner', 'card' => 'credit-card', 'expire_month' => 'card-expiry-month', 'expire_year' => 'card-expiry-year', 'code' => 'cvc-length'),
			array('card-owner' => $sandbox ? 'John Doe' : '', 'credit-card' => $sandbox ? '5571135571135575' : '', 'card-expiry-month' => $sandbox ? '12' : '', 'card-expiry-year' => $sandbox ? '2024' : '', 'cvc-length' => '000')
		); ?>
	</div>
</div>	

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg">
			<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?>
		</button>
	</div>
</div>