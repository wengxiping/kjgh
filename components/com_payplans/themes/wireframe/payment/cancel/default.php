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
<div class="pp-checkout-container">
	<?php echo $this->output('site/checkout/default/header', array('step' => 'payment', 'title' => 'COM_PP_PAYMENT_CANCELLED')); ?>

	<div class="pp-checkout-wrapper">
		<div class="pp-checkout-wrapper__sub-content">
			<div class="pp-checkout-menu">
				<div class="t-lg-mb--lg">
					<div class="pp-result-container">
						<div class="pp-result">
							<div class="pp-result__icons t-lg-mb--md">
								<i class="fas fa-frown"></i>
							</div>

							<div class="pp-result__title">
								<?php echo JText::_('COM_PAYPLANS_PAYMENT_CANCEL'); ?>
							</div>

							<div class="pp-result__desc">
								<?php echo JText::_('COM_PAYPLANS_PAYMENT_CANCEL_MSG');?>
							</div>

							<div class="pp-result__action">
								<a href="<?php echo JRoute::_('index.php'); ?>" class="t-lg-mr--lg">
									&larr; <?php echo JText::_('COM_PP_RETURN_TO_SITE'); ?>
								</a>

								<a href="<?php echo PPR::_('index.php?option=com_payplans&view=checkout&invoice_key='.$invoice->getKey() . '&tmpl=component'); ?>" class="btn btn-pp-primary btn--lg">
									<?php echo JText::_('COM_PAYPLANS_PAYMENT_PAYNOW'); ?>
								</a>
							</div>

							<div class="pp-result__note t-lg-mt--xl">
								<a href="javascript:void(0);" data-pp-contact>
									<?php echo JText::_('COM_PP_HELP_COMPLETE_PAYMENT'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>