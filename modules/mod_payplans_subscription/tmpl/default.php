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
<?php if ($subscriptions) { ?>
	<?php foreach ($subscriptions as $subscription) { ?>
		<?php $subscription = PP::subscription($subscription); ?>

		<a href="<?php echo $subscription->getPermalink();?>">
			<li class="latestnews word-wrap">
				<span class="plan" ><?php echo $subscription->getTitle(); ?></span>
		</a>

		<div id="pp" class="modifydate" style="height: auto;">
			<?php if ($subscription->isActive() && !$subscription->getSubscriptionDate() || ($subscription->getExpirationType() == 'forever') ) { ?>
				<?php echo JText::_('MOD_PAYPLANS_SUBSCRIPTION_EXPIRATION_DATE_LIFETIME'); ?>
			<?php } else { ?>
				<?php echo JText::_('MOD_PAYPLANS_SUBSCRIPTION_EXPIRATION_DATE_' . strtoupper(PP::model('subscription')->getStatusString($subscription->status))) . " " . $subscription->getExpirationDate(); ?>
			<?php } ?>
		</div>
		<?php if ($subscription->isRenewable()) { ?>
			<div id="pp" class="o-btn-group">
				<a href="<?php echo PPR::_('index.php?option=com_payplans&view=order&layout=processRenew&subscription_key=' . $subscription->getKey() . '&tmpl=component'); ?>" class="btn btn-pp-primary">
				<?php echo JText::_('COM_PP_APP_RENEW_BUTTON'); ?>
				</a>
			</div>
		<?php } ?>
	<?php } ?>
<?php } ?>
