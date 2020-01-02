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
?>
<script src="https://checkout.stripe.com/checkout.js"></script>

<form method="post" autocomplete="off" action="<?php echo JRoute::_('index.php?option=com_payplans&view=payment&task=complete&action=process&payment_key=' . $payment->getKey());?>" data-pp-stripe-form>

	<div class="o-card o-card--borderless t-lg-mb--lg">
		<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_PAYMENT_DETAILS');?></div>

		<div class="o-card__body">
			<p><?php echo JText::_('COM_PP_PAYMENT_VIA_STRIPE'); ?></p>

			<div class="t-lg-mt--xl o-loader o-loader--inline is-centered is-active"></div>
		</div>
	</div>

	<?php echo $this->html('form.hidden', 'stripeToken', '', 'data-stripe-token=""'); ?>
</form>