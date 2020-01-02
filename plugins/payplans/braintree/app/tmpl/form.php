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
<script src="https://js.braintreegateway.com/v2/braintree.js"></script>
<form action="<?php echo $post_url;?>" method="post" autocomplete="off" data-braintree-form >
	<div class="o-card o-card--borderless t-lg-mb--lg">
		<div class="o-card__body">
			<div id="dropin-container"></div>
		</div>

	</div>

	<div class="o-grid-sm">
		<div class="o-grid-sm__cell o-grid-sm__cell--center">
			<a href="<?php echo $cancel_url; ?>"><?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CANCEL')?></a>
		</div>

		<div class="o-grid-sm__cell o-grid-sm__cell--right">
			<button id="pp-payment-app-buy" type="submit" class="btn btn-pp-primary btn--lg">
				<?php echo JText::_('COM_PAYPLANS_PAYMENT_APP_BRAINTREE_BUY');?>
			</button>
		</div>
	</div>
</form>
