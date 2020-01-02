<?php
/**
* @package    PayPlans
* @copyright  Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form action="<?php echo JRoute::_('index.php');?>" method="post" data-authorize-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_AUTHORIZE', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('form.card', array('card' => 'card_num', 'expire_month' => 'exp_month', 'expire_year' => 'exp_year', 'code' => 'card_code'), 
					array('card_num' => $params->get('sandbox') ? '370000000000002' : '', 'exp_month' => $params->get('sandbox') ? '12' : '', 'exp_year' => $params->get('sandbox') ? '2028' : '', 'card_code' => $params->get('sandbox') ? '1234' : '')
		); ?>
	</div>
</div>		

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

	<div class="o-card__body">
		<?php if ($params->get('company_name', false)) { ?>
			<?php echo $this->html('floatlabel.text', 'COM_PP_COMPANY_NAME_OPTIONAL', 'company', $params->get('sandbox') ? 'Company' : ''); ?>
		<?php } ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_FIRST_NAME', 'first_name', $params->get('sandbox') ? 'John' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_LAST_NAME', 'last_name', $params->get('sandbox') ? 'Doe' : ''); ?>
			</div>
		</div>

		<?php echo $this->html('floatlabel.text', 'COM_PP_EMAIL_ADDRESS', 'email', $params->get('sandbox') ? 'john@doe.com' : ''); ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_MOBILE_NUMBER', 'mobile', $params->get('sandbox') ? '1234567' : ''); ?>
			</div>
		</div>

		<?php echo $this->html('floatlabel.text', 'COM_PP_ADDRESS', 'address', $params->get('sandbox') ? 'Address line 1' : ''); ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_CITY', 'city', $params->get('sandbox') ? 'Gotham City' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_STATE', 'state', $params->get('sandbox') ? 'Gotham' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_ZIP', 'zip', $params->get('sandbox') ? '1234' : ''); ?>
			</div>
		</div>
		
		<?php echo $this->html('floatlabel.text', 'COM_PP_COUNTRY', 'country', $params->get('sandbox') ? 'United States' : ''); ?>
	</div>
</div>

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg" data-pp-authorizecim-submit>
			<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?>
		</button>
	</div>
</div>

<?php echo $this->html('form.hidden', 'view', 'payment'); ?>
<?php echo $this->html('form.hidden', 'task', 'complete'); ?>
<?php echo $this->html('form.hidden', 'amount', $amount); ?>
<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>
</form>