<?php
/**
* @package		Payplans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayplansGdprSubscription extends PayplansGdprAbstract
{
	public $type = 'subscription';
	public $tab = null;

	/**
	 * Process user profile data downloads in accordance to GDPR rules
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function execute(PayplansGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$model = PP::model('Subscription');
		$subscriptions = $model->loadRecords(array('user_id' => $this->userId));

		if (!$subscriptions) {
			return $this->tab->finalize();
		}

		foreach ($subscriptions as $value) {
			$subscription = PP::subscription($value);

			$item = $this->getTemplate($subscription, $this->type);

			$item->view = false;
			$item->title = '';
			$item->created = $subscription->getSubscriptionDate();
			$item->intro = $this->getIntro($subscription);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display each of the item title on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getTitle($subscription)
	{
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($subscription)
	{
		if (!$subscription) {
			return;
		}

		$order = $subscription->getOrder();
		$amount = $order->getTotal();
		$currency = $order->getCurrency();

		ob_start();
		?>
		<table class="gdpr-table" style="width:520px;">
			<thead>
			   <th colspan="2" style="float:left;">
					<?php echo $subscription->getId()."(".$subscription->getKey().")";?>
			   </th>
			</thead>
			<tbody>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_ID');?></td>
					<td style="text-align:left;"><?php echo $subscription->getId(); ?></td>
				</tr>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_PLAN');?></td>
					<td style="text-align:left;"><?php echo $subscription->getTitle(); ?></td>
				</tr>

				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_TOTAL');?></td>

					<td style="text-align:left;"><?php echo $currency." ".$amount; ?></td>
				</tr>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_STATUS');?></td>
					<td style="text-align:left;">
						<?php echo $subscription->getLabel();?>	
					</td>
				</tr>

				<?php if ($subscription->getSubscriptionDate()) { ?>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_SUBSCRIPTION_DATE');?></td>
					<td style="text-align:left;">
						<?php echo $subscription->getSubscriptionDate()->format(JText::_('DATE_FORMAT_LC2')); ?>
					</td>
				</tr>
				<?php } ?>

				<?php if ($subscription->getExpirationDate()) { ?>
				<tr>
					<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_SUBSCRIPTION_TAB_EXPIRATION_DATE');?></td>
					<td style="text-align:left;">
						<?php echo PPFormats::date($subscription->getExpirationDate()); ?>
					</td>
				</tr>
				<?php } ?>

				<?php 	
				$subscriptionParams = $subscription->getParams();
				$subscriptionParams = $subscriptionParams->toArray();
				$subArray = array('expirationtype', 'trial_price_1', 'trial_time_1', 'trial_price_2', 'trial_time_2', 'price', 'expiration', 'recurrence_count', 'currency');

				foreach ($subscriptionParams as $key => $value) {
					if (in_array($key, $subArray)) { 
						unset($subscriptionParams[$key]);
					}
				}

				if (!empty($subscriptionParams)) { 
				?>
					<?php foreach ($subscriptionParams as $key => $value) { ?>
							<tr>
								<td width="180"><?php echo $key;?></td>
								<td style="text-align:left;"><?php echo $value; ?></td>
							</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Display the content on the sub page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getContent($subscription)
	{
	}

}	