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
<?php $count = 0;?> 
<?php if(is_array($order_records) && !empty($order_records)) :?>
<?php foreach($order_records as $record) :?>
	<?php $order->bind($record);?> 
	<?php $order_sub = $order->getSubscription();?>
	<?php if(!($order_sub instanceof PayplansSubscription)):?>
		<?php continue;?>
	<?php endif;?>
	<?php $sub_id  = $order_sub->getId();?>
	<fieldset class="form-horizontal">
		<legend onClick="xi.jQuery('.pp-order-details-<?php echo $sub_id?>').slideToggle();" class="pp-cursor-pointer">
			<span class="show pp-order-details-<?php echo $sub_id?>">[+]</span>
			<?php $invoice_records = $order->getInvoices();?>			
			<?php echo "#".$sub_id." : ".$order_sub->getTitle()." (".$order_sub->getStatusName().")"; ?>
		</legend>
		
		<div class="hide pp-order-details-<?php echo $sub_id?>">
			<div>
				<?php echo JText::_('COM_PAYPLANS_USER_EDIT_ORDER_SUBSCRIPTION_SUBSCRIPTION_DATE')." ";?> 
				<strong><?php echo XiDate::timeago($order_sub->getSubscriptionDate()->toMysql())." ";?></strong>
				<?php echo JText::_('COM_PAYPLANS_AND')." ";?>
				<?php echo JText::_('COM_PAYPLANS_USER_EDIT_ORDER_SUBSCRIPTION_SUBSCRIPTION_EXPIRATION_DATE')." ";?>
				<strong><?php echo XiDate::timeago($order_sub->getExpirationDate()->toMysql()).".";?></strong>
			</div>
			<div class="clr"></div>
			<div class="offset1">
				<fieldset class="form-horizontal">
				<legend><?php echo JText::_('COM_PAYPLANS_ORDER_EDIT_INVOICE' ); ?></legend>
				</fieldset>
				<?php echo $this->output('site/partials/partial_invoice_table', compact('invoice_records'));?>
			</div>
		</div> 
	</fieldset>
<?php endforeach;?>
<?php endif;?>
<?php 
