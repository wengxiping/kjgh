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
<form method="post" action="<?php echo $postUrl;?>" data-pp-remita-form>

	<div class="o-card o-card--borderless t-lg-mb--lg">
		<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

		<div class="o-card__body">
			<?php echo $this->html('floatlabel.text', 'COM_PP_NAME', 'payerName',  $sandbox ? 'Oshadami Mike' : ''); ?>

			<?php echo $this->html('floatlabel.text', 'COM_PP_EMAIL_ADDRESS', 'payerEmail',  $sandbox ? 'oshadami@example.com' : ''); ?>

			<?php echo $this->html('floatlabel.text', 'COM_PP_TELEPHONE_NUMBER', 'payerPhone',  $sandbox ? '08012345678' : ''); ?>
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

	<?php echo $this->html('form.hidden', 'merchantId', $merchantId); ?>
	<?php echo $this->html('form.hidden', 'serviceTypeId', $serviceTypeId); ?>
	<?php echo $this->html('form.hidden', 'amt', $total); ?>
	<?php echo $this->html('form.hidden', 'responseurl', $responseUrl); ?>
	<?php echo $this->html('form.hidden', 'hash', $hash); ?>
	<?php echo $this->html('form.hidden', 'paymenttype', 'parent'); ?>
	<?php echo $this->html('form.hidden', 'orderId', $paymentKey); ?>

</form>
