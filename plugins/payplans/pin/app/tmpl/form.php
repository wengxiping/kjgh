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
<form action="<?php echo $formUrl;?>" method="post" data-pp-sagepay-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_PIN', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('form.card', array('name' => 'card_name', 'card' => 'card_num', 'expire_month' => 'exp_month', 'expire_year' => 'exp_year', 'code' => 'card_code'),
			array('card_name' => $sandbox ? 'John Doe' : '', 'card_num' => $sandbox ? '4200000000000000' : '', 'exp_month' => $sandbox ? '12' : '', 'exp_year' => $sandbox ? '2024' : '', 'card_code' => '123')
		); ?>
	</div>
</div>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

	<div class="o-card__body">

		<?php echo $this->html('floatlabel.text', 'COM_PP_EMAIL_ADDRESS', 'email',  $sandbox ? 'john@doe.com' : ''); ?>

		<?php echo $this->html('floatlabel.text', 'COM_PP_ADDRESS', 'address', $sandbox ? 'Address line 1' : ''); ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_CITY', 'city', $sandbox ? 'Gotham City' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_STATE', 'state', $sandbox ? 'State of Gotham' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_ZIP', 'zip', $sandbox ? '1234' : ''); ?>
			</div>
		</div>
		
		<?php echo $this->html('floatlabel.text', 'COM_PP_COUNTRY', 'country', $sandbox ? 'United States' : ''); ?>
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

</form>