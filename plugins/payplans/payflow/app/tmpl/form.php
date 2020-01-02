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
<form action="<?php echo $formUrl;?>" method="post" data-payflow-form>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_CARD_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_PAYFLOW', '<b>' . $this->html('html.amount', $amount, $invoice->getCurrency()) . '</b>'); ?>
		</div>

		<?php echo $this->html('form.card', array('cardType' => 'CARDTYPE' ,'card' => 'ACCT', 'expire_month' => 'EXPDATE', 'expire_year' => 'EXPYEAR', 'code' => 'CVV2'),
			array('CARDTYPE' => "" ,'ACCT' => $sandbox ? '4032039172350615' : '', 'EXPDATE' => $sandbox ? '08' : '', 'EXPYEAR' => $sandbox ? '2023' : '', 'CVV2' => '123')
		); ?>
	</div>
</div>

<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_YOUR_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_FIRST_NAME', 'BILLTOFIRSTNAME',  $sandbox ? 'John' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_LAST_NAME', 'BILLTOLASTNAME',  $sandbox ? 'doe' : ''); ?>
			</div>
		</div>

		<?php echo $this->html('floatlabel.text', 'COM_PP_EMAIL_ADDRESS', 'BILLTOEMAIL',  $sandbox ? 'john@doe.com' : ''); ?>

		<?php echo $this->html('floatlabel.text', 'COM_PP_TELEPHONE_NUMBER', 'BILLTOPHONENUM',  $sandbox ? '01234567' : ''); ?>

		<?php echo $this->html('floatlabel.text', 'COM_PP_ADDRESS', 'BILLTOSTREET', $sandbox ? 'Address line 1' : ''); ?>

		<div class="o-grid o-grid--gutters">
			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_CITY', 'BILLTOCITY', $sandbox ? 'Gotham City' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_STATE', 'BILLTOSTATE', $sandbox ? 'State of Gotham' : ''); ?>
			</div>

			<div class="o-grid__cell">
				<?php echo $this->html('floatlabel.text', 'COM_PP_ZIP', 'BILLTOZIP', $sandbox ? '1234' : ''); ?>
			</div>
		</div>
		
		<?php echo $this->html('floatlabel.text', 'COM_PP_COUNTRY', 'BILLTOCOUNTRY', $sandbox ? 'United States' : ''); ?>
	</div>
</div>

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg" data-submit-payment>
			<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?>
		</button>
	</div>
</div>

<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>
<?php echo $this->html('form.hidden', 'card_type', '', 'data-payflow-card-type'); ?>

</form>