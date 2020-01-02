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
<div class="db-stats__item">
	<a href="index.php?option=com_payplans&view=invoice&status=<?php echo PP_INVOICE_PAID;?>">
		<div class="db-stat">
			<div class="db-stat__icon">
				<div class="db-icon db-icon--1">
					<i class="fas fa-cart-arrow-down"></i>
				</div>
			</div>
			<div class="db-stat__content">
				<div class="db-stat__title"><?php echo JText::_('COM_PAYPLANS_STATISTICS_NUMERIC_SALES'); ?></div>
				<div class="db-stat__counter"><?php echo $statistics->totalSales; ?></div>
			</div>
		</div>
	</a>
</div>

<div class="db-stats__item">
	<a href="index.php?option=com_payplans&view=invoice&status=<?php echo PP_INVOICE_PAID;?>">
		<div class="db-stat">
			<div class="db-stat__icon">
				<div class="db-icon db-icon--2">
					<i class="fas fa-money-bill-alt"></i>
				</div>
			</div>
			<div class="db-stat__content">
				<div class="db-stat__title"><?php echo JText::_('COM_PAYPLANS_STATISTICS_NUMERIC_REVENUE'); ?></div>
				<div class="db-stat__counter"><?php echo $this->html('html.amount', $statistics->totalRevenue, PP::getCurrency($this->config->get('currency'))->symbol); ?></div>
			</div>
		</div>
	</a>
</div>

<div class="db-stats__item">
	<a href="index.php?option=com_payplans&view=subscription&status=<?php echo PP_SUBSCRIPTION_ACTIVE;?>">
		<div class="db-stat">
			<div class="db-stat__icon">
				<div class="db-icon db-icon--3">
					<i class="fas fa-user"></i>
				</div>
			</div>
			<div class="db-stat__content">
				<div class="db-stat__title"><?php echo JText::_('DASHBOARD_STATISTICS_ACTIVE_SUBSCRIPTIONS'); ?></div>
				<div class="db-stat__counter"><?php echo $statistics->currentActiveSubscription; ?></div>
			</div>
		</div>
	</a>
</div>

<div class="db-stats__item">
	<a href="index.php?option=com_payplans&view=subscription&status=<?php echo PP_SUBSCRIPTION_EXPIRED;?>">
		<div class="db-stat">
			<div class="db-stat__icon">
				<div class="db-icon db-icon--4">
					<i class="fas fa-user"></i>	
				</div>
			</div>
			<div class="db-stat__content">
				<div class="db-stat__title"><?php echo JText::_('DASHBOARD_STATISTICS_EXPIRE_SUBSCRIPTIONS'); ?></div>
				<div class="db-stat__counter"><?php echo $statistics->currentExpiredSubscription; ?></div>
			</div>
		</div>
	</a>
</div>