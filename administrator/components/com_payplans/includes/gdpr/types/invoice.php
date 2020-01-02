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

class PayplansGdprInvoice extends PayplansGdprAbstract
{
	public $type = 'invoice';
	public $tab = null;

	/**
	 * Process user profile data downloads in accordance to GDPR rules
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function execute(PayplansGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$model = PP::model('Invoice');
		$invoices = $model->loadRecords(array('user_id' => $this->userId));

		// Nothing else to process, finalize it now.
		if (!$invoices) {
			return $this->tab->finalize();
		}

		foreach ($invoices as $value) {
			$invoice = PP::invoice($value->invoice_id);

			$item = $this->getTemplate($invoice, $this->type);

			$item->view = false;
			$item->title = '';
			$item->created = $invoice->getCreatedDate();
			$item->intro = $this->getIntro($invoice);

			$this->tab->addItem($item);
		}
	
	}

	/**
	 * Display each of the item title on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getTitle($invoice)
	{
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  4.0.0
	 * @access public
	 */
	public function getIntro($invoice)
	{
		$plan = $invoice->getPlan();
		$order = $invoice->getReferenceObject();
		$subscription = $order->getSubscription();
		$subscriptionId = PP::getKeyFromId($subscription->getId());
		$currency = $invoice->getCurrency();

		ob_start();
		?>

			<?php if (!empty($invoice)) { ?>
				<table class="gdpr-table" style="width:520px;">
					<thead>
					   <th colspan="2" style="float:left;">
							<?php echo $invoice->getId()."(".$invoice->getKey().")";?>
					   </th>
					</thead>
					<tbody>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_ID');?></td>
							<td style="text-align:left;"><?php echo $invoice->getId(); ?></td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_SERIAL_NUMBER');?></td>
							<td style="text-align:left;"><?php echo $invoice->getSerial(); ?></td>
						</tr>

						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_PLAN');?></td>
							<td style="text-align:left;"><?php echo $plan->getTitle(); ?></td>
						</tr>

						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_SUBCRIPTION_ID');?></td>
							<td style="text-align: left;">
								<?php echo $subscription->getId();?> (<?php echo $subscription->getKey();?>)
							</td>
						</tr>

						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_SUBTOTAL');?></td>
							<td style="text-align:left;">
								<?php echo $currency . " " . $invoice->getSubtotal(); ?>
							</td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_DISCOUNT');?></td>
							<td style="text-align:left;">
								<?php echo $currency." ".$invoice->getDiscount(); ?>
							</td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_TAX');?></td>
							<td style="text-align:left;"><?php echo $currency." ".$invoice->getTaxAmount(); ?></td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_TOTAL');?></td>
							<td style="text-align:left;"><?php echo $currency." ".$invoice->getTotal(); ?></td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_STATUS');?></td>
							<td style="text-align:left;">
								<?php echo $invoice->getStatusName();?>
							</td>
						</tr>
						<tr>
							<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_CREATED_DATE');?></td>
							<td style="text-align:left;"><?php echo PPFormats::date($invoice->getCreatedDate()); ?></td>
						</tr>

						<?php if ($invoice->isPaid()) { ?>
							<?php $payment = $invoice->getPayment(); ?>

							<tr>
								<td width="180"><?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_PAYMENT_METHOD');?></td>
								<td style="text-align:left">
									<?php if ($payment && ($payment instanceof PPPayment) && $payment->getId()) { ?>
										<?php echo $payment->getAppName(); ?>
									<?php } else { ?>
										<?php echo JText::_('COM_PAYPLANS_TRANSACTION_PAYMENT_GATEWAY_NONE'); ?>
									<?php } ?>
								</td>
							</tr>

							<tr>
								<td width="180">
									<?php echo JText::_('COM_PAYPLANS_GDPR_INVOICE_TAB_PAID_DATE');?>
								</td>
								<td style="text-align:left;">
									<?php echo PPFormats::date($invoice->getPaidDate()); ?>
								</td>
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
	public function getContent($invoice)
	{

	}

}	