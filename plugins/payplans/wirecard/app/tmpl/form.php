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
<div class="pp-result-container">
	<form action="<?php echo $formUrl;?>" method="post" data-pp-wirecard-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'customerId', $customerId); ?>
		<?php echo $this->html('form.hidden', 'language', $language); ?>
		<?php echo $this->html('form.hidden', 'paymentType', 'SELECT'); ?>
		<?php echo $this->html('form.hidden', 'orderDescription', $orderDescription); ?>
		<?php echo $this->html('form.hidden', 'currency', $currency); ?>
		<?php echo $this->html('form.hidden', 'amount', $amount); ?>
		<?php echo $this->html('form.hidden', 'successUrl', $successUrl); ?>
		<?php echo $this->html('form.hidden', 'cancelUrl', $cancelUrl); ?>
		<?php echo $this->html('form.hidden', 'failureUrl', $failureUrl); ?>
		<?php echo $this->html('form.hidden', 'serviceUrl', $serviceUrl); ?>
		<?php echo $this->html('form.hidden', 'orderReference', $orderReference); ?>
		<?php echo $this->html('form.hidden', 'customerStatement', $customerStatement); ?>
		<?php echo $this->html('form.hidden', 'autoDeposit', $autoDeposit); ?>
		<?php echo $this->html('form.hidden', 'transactionIdentifier', $transactionIdentifier); ?>
		<?php echo $this->html('form.hidden', 'requestFingerprintOrder', $requestFingerprintOrder); ?>
		<?php echo $this->html('form.hidden', 'requestFingerprint', $fingerprint); ?>
	</form>
</div>
