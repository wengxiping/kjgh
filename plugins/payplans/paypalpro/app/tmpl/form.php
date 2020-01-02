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
<form action="<?php echo $postUrl;?>" method="post" data-paypalpro-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_PAYPAL', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('form.card', array('card' => 'card_num', 'expire_month' => 'exp_month', 'expire_year' => 'exp_year', 'code' => 'card_code'),
				array('card_num' => $params->get('sandboxTesting') ? '4916064324171157' : '', 'card_code' => $params->get('sandboxTesting') ? '123' : '', 'exp_month' => '', 'exp_year' => '')
			); ?>
	</div>
</div>		

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_FIRST_NAME', 'first_name', $params->get('sandboxTesting') ? 'John' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_LAST_NAME', 'last_name', $params->get('sandboxTesting') ? 'Doe' : ''); ?>
			</div>
		</div>

		<?php echo $this->html('floatlabel.text', 'COM_PP_EMAIL_ADDRESS', 'email', $params->get('sandboxTesting') ? 'john@doe.com' : ''); ?>

		<?php echo $this->html('floatlabel.text', 'COM_PP_TELEPHONE_NUMBER', 'mobile', $params->get('sandboxTesting') ? '01234567' : ''); ?>

		<?php echo $this->html('floatlabel.text', 'COM_PP_ADDRESS', 'address', $params->get('sandboxTesting') ? 'Testing address line 1' : ''); ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_CITY', 'city', $params->get('sandboxTesting') ? 'City of Gotham' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_STATE', 'state', $params->get('sandboxTesting') ? 'State of Gotham' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_ZIP', 'zip', $params->get('sandboxTesting') ? '1234' : ''); ?>
			</div>
		</div>
		
		<?php echo $this->html('floatlabel.lists', 'COM_PP_COUNTRY', 'country', '', '', '', $countries); ?>
	</div>
</div>

<div class="o-grid-sm">
	<div class="o-grid-sm__cell o-grid-sm__cell--center">
		<a href="<?php echo $cancelUrl; ?>">
			&larr; <?php echo JText::_('COM_PP_CANCEL_BUTTON')?>
		</a>
	</div>


	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg" data-submit-payment>
			<?php echo JText::_('Complete Payment');?>
		</button>
	</div>
</div>

<?php echo $this->html('form.hidden', 'payment_key', $paymentKey); ?>
<?php echo $this->html('form.hidden', 'bn', 'StackIdeas'); ?>
<?php echo $this->html('form.hidden', 'cc_type', '', 'data-paypalpro-card-type'); ?>
</form>