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
<?php
$regularCounter = $invoice->getCounter();
if ($recurring === PP_RECURRING_TRIAL_1) {
	$regularCounter = $invoice->getCounter() + 1;
}

if($recurring === PP_RECURRING_TRIAL_2) {
	$regularCounter = $invoice->getCounter() + 2;
}

$class = (!in_array($recurring, array(PP_RECURRING_TRIAL_1, PP_RECURRING_TRIAL_2)))? " t-hidden " : " show ";?>
<div class="o-card o-card--borderless t-lg-mb--lg">
	<div class="o-card__header o-card__header--nobg t-lg-pl--no">
		<div class="o-grid">
			<div style="font-weight: normal;">
				<span class="<?php echo $class;?>">
					<!-- for recurring plans -->
					<?php if ($recurring) { ?>
						<!-- plan have trials -->	
						<?php if (in_array($recurring, array(PP_RECURRING_TRIAL_1, PP_RECURRING_TRIAL_2))) { ?>
									<span>
										<?php echo $this->html('html.amount', $invoice->getPrice(), $invoice->getCurrency()); ?>	
									</span>
									<span>
										<?php echo JText::sprintf('COM_PAYPLANS_ORDER_CONFIRM_FIRST_CHARGABLE_AMOUNT', $this->html('html.plantime', $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_1)));?>
									</span>
									<!-- plan have 2 trials -->	
									<?php if ($recurring === PP_RECURRING_TRIAL_2) { ?>
											<span>
												<?php $amount = $invoice->getPrice($invoice->getCounter() + 1);?>
												<?php echo $this->html('html.amount', $amount, $invoice->getCurrency()); ?>	
											</span>
											<span> 
												<?php echo JText::sprintf('COM_PAYPLANS_ORDER_CONFIRM_SECOND_CHARGABLE_AMOUNT', $this->html('html.plantime', $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_2)));?>
											</span>
									<?php } ?>
							
								<?php } else { ?>
									<!-- plan do not have trials -->
									<span><?php echo JText::sprintf('COM_PAYPLANS_ORDER_CONFIRM_FIRST_TRIAL_AMOUNT', $this->html('html.plantime', $invoice->getExpiration()));?></span>	
								<?php } ?>
						<?php } ?>	
					</span>

				<?php if ($recurring) { ?>
						<?php $recurrenceCount = $invoice->getRecurrenceCount();?>
						<?php $amount = $invoice->getPrice($regularCounter);?>
						<?php $amountHtml = $this->html('html.amount', $amount, $invoice->getCurrency()); ?>	
									
						<?php if ($recurrenceCount <= 0 ) { ?>
							<span><?php echo JText::sprintf('COM_PAYPLANS_ORDER_CONFIRM_FIRST_RECURRENCE_COUNT_ZERO_RECURRENCE_COUNT', $amountHtml, $this->html('html.plantime', $invoice->getExpiration()));?></span>
						<?php } else { ?>
							<span><?php echo JText::sprintf('COM_PAYPLANS_ORDER_CONFIRM_FIRST_RECURRENCE_COUNT', $amountHtml, $this->html('html.plantime', $invoice->getExpiration()), $recurrenceCount);?></span>
						<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
