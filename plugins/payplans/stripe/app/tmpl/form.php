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
<script src="https://js.stripe.com/v2/"></script>

<form method="post" autocomplete="off" action="<?php echo JRoute::_('index.php?option=com_payplans&view=payment&task=complete&action=process&payment_key=' . $payment->getKey());?>" data-pp-stripe-form>

	<div class="o-card o-card--borderless t-lg-mb--lg">
		<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

		<div class="o-card__body">
			<div data-pp-stripe-result>
			</div>

			<?php echo $this->html('form.card', array('card' => 'stripe_card_num', 'expire_month' => 'stripe_exp_month', 'expire_year' => 'stripe_exp_year', 'code' => 'stripe_card_code'),
				array('stripe_card_num' => $sandbox ? '4012888888881881' : '', 'exp_month' => $sandbox ? '12' : '', 'exp_year' => $sandbox ? '2024' : '', 'stripe_card_code' => $sandbox ? '123' : '')
			); ?>
		</div>
	</div>

	<div class="o-grid-sm">
		<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

		<div class="o-grid-sm__cell o-grid-sm__cell--right">
			<button type="button" class="btn btn-pp-primary btn--lg" data-pp-stripe-submit data-key="<?php echo $publicKey;?>">
				<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?>
			</button>
		</div>
	</div>

	<?php echo $this->html('form.hidden', 'stripeToken', '', 'data-stripe-token=""'); ?>
</form>