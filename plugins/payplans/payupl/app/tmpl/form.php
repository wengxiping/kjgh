<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
	<form action="<?php echo $formUrl ?>" method="post" data-payupl-form>

		<?php echo $this->html('html.redirection'); ?>

		<?php echo $this->html('form.hidden', 'notifyUrl', $payload['notifyUrl']); ?>
		<?php echo $this->html('form.hidden', 'continueUrl', $payload['continueUrl']); ?>
		<?php echo $this->html('form.hidden', 'customerIp', $payload['customerIp']); ?>
		<?php echo $this->html('form.hidden', 'merchantPosId', $payload['merchantPosId']); ?>
		<?php echo $this->html('form.hidden', 'description', $payload['description']); ?>
		<?php echo $this->html('form.hidden', 'currencyCode', $payload['currencyCode']); ?>
		<?php echo $this->html('form.hidden', 'totalAmount', $payload['totalAmount']); ?>
		<?php echo $this->html('form.hidden', 'extOrderId', $payload['extOrderId']); ?>
		<?php echo $this->html('form.hidden', 'products[0].name', $payload['description']); ?>
		<?php echo $this->html('form.hidden', 'products[0].quantity', "1"); ?>
		<?php echo $this->html('form.hidden', 'products[0].unitPrice', $payload['totalAmount']); ?>
		<?php echo $this->html('form.hidden', 'OpenPayu-Signature', $payload['signature']); ?>

	</form>
</div>