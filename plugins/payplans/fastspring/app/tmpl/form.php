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
<script
	id="fsc-api"
	src="https://d1f8f9xcsvx3ha.cloudfront.net/sbl/0.7.4/fastspring-builder.min.js"
	type="text/javascript"
	data-storefront="<?php echo $storeFrontUrl;?>"
	data-access-key="<?php echo $accessKey;?>" 
	data-popup-event-received="popupEventReceived" 
	data-popup-closed="onPopupClose" 
	data-debug="false" 
	data-continuous="false"
></script>

<form action="<?php echo JRoute::_('index.php?tmpl=component');?>" method="post" class="o-form-horizontal" data-fastspring-form>

	<div class="o-card o-card--borderless t-lg-mb--lg">
		<div class="o-card__header o-card__header--nobg t-lg-pl--no"><?php echo JText::_('COM_PP_PAYMENT_DETAILS');?></div>

		<div class="o-card__body">
			<div class="o-form-group">
				<?php echo JText::sprintf('COM_PP_PAYMENT_VIA_FASTSPRING', '<b>' . $this->html('html.amount', $invoice->getTotal(), $invoice->getCurrency()) . '</b>'); ?>
			</div>
		</div>
	</div>

	<div class="o-grid-sm">
		<?php echo $this->output('site/payment/default/cancel', array('payment' => $payment)); ?>

		<div class="o-grid-sm__cell o-grid-sm__cell--right">
			<a href="javascript:void(0);" class="btn btn-pp-primary btn--lg" data-fsc-action="Add, Checkout" data-fsc-item-path-value="<?php echo $productId;?>">
				<?php echo JText::_('Complete Payment');?>
			</a>
			
		</div>
	</div>
</form>


