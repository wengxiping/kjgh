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
<form action="<?php echo JRoute::_('index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $payment->getKey());?>" method="post">
<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_TRANSACTION_DETAILS');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
		<?php if ($points >= $cost) { ?>
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_POINTS', $cost); ?>
		<?php } else { ?>
			<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_POINTS_INSUFFICIENT', $points, $cost); ?>	
		<?php } ?>
		</div>
	</div>
</div>

<?php if ($invoice->isRecurring() && $sufficient) { ?>
<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_POINTS_RECURRING_HEADING');?></div>

	<div class="o-card__body">
		<div class="o-form-group">
			<?php echo JText::sprintf('COM_PP_POINTS_RECURRING_DETAILS', $cost); ?>
		</div>
	</div>
</div>
<?php } ?>

<div class="o-grid-sm">
	<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

	<?php if ($sufficient) { ?>
	<div class="o-grid-sm__cell o-grid-sm__cell--right">
		<button type="submit" class="btn btn-pp-primary btn--lg">
			<?php echo JText::_('COM_PP_COMPLETE_PAYMENT_BUTTON');?>
		</button>
	</div>
	<?php } ?>
</div>

<?php echo $this->html('form.hidden', 'payment_key', $payment->getKey()); ?>
</form>