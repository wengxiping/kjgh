<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class PPPdfInvoiceAdapter extends PPPdfAbstract
{
	private $invoice = null;

	public function __construct(PPInvoice $invoice)
	{
		$this->invoice = $invoice;

		parent::__construct();
	}

	/**
	 * Generate a physical pdf file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function generateFile()
	{
		$contents = $this->generateContent();
		
		// Convert it into a pdf format
		$pdf = $this->saveToPdf($contents);
		$path = $this->getPath();

		if (!JFolder::exists($path)) {
			JFolder::create($path);
		}

		$invoiceKey = $this->invoice->getKey();

		$filePath = $path . '/' . $this->getFileName();

		file_put_contents($filePath, $pdf->output());
		return;
	}

	/**
	 * Get pdf filename
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getFileName()
	{
		$invoiceKey = $this->invoice->getKey();
		
		return base64_encode($invoiceKey) . '.pdf';
	}

	/**
	 * Generate content for pdf
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function generateContent()
	{
		$invoice = $this->invoice;

		$buyer = $invoice->getBuyer();
		$payment = $invoice->getPayment();
		$modifiers = $invoice->getModifiers();

		$discountablesSerials = array(PP_MODIFIER_FIXED_DISCOUNTABLE, PP_MODIFIER_PERCENT_DISCOUNTABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE,
									PP_MODIFIER_FIXED_DISCOUNT, PP_MODIFIER_PERCENT_DISCOUNT);

		$nonTaxesSerials = array(PP_MODIFIER_FIXED_NON_TAXABLE, PP_MODIFIER_PERCENT_NON_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE,
								PP_MODIFIER_FIXED_NON_TAXABLE_TAX_ADJUSTABLE);

		$taxableSerials = array(PP_MODIFIER_PERCENT_TAXABLE, PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE,
							PP_MODIFIER_FIXED_TAX, PP_MODIFIER_PERCENT_TAX);

		$digitAfterDecimal = $this->config->get('fractionDigitCount');
		$separator = $this->config->get('price_decimal_separator');

		$total = round($invoice->getTotal(), $digitAfterDecimal);
		$amount = number_format($total, $digitAfterDecimal, $separator, '');

		$theme = PP::themes();
		$theme->set('invoice', $invoice);
		$theme->set('user', $buyer);
		$theme->set('amount', $amount);
		$theme->set('modifiers', $modifiers);
		$theme->set('discountablesSerials', $discountablesSerials);
		$theme->set('taxableSerials', $taxableSerials);
		$theme->set('nonTaxesSerials', $nonTaxesSerials);
		$theme->set('config', PP::config());
		$theme->set('payment', $payment);
		$contents = $theme->output('site/invoice/pdf_content');

		return $contents;
	}

	/**
	 * Get the folder path
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPath()
	{
		$buyer = $this->invoice->getBuyer();
		$path = JPATH_ROOT . '/media/com_payplans/tmp/pdfinvoices/' . $buyer->getId();
		
		return $path;
	}

	/**
	 * Get pdf file path
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getFilePath()
	{
		$filePath = $this->getPath() . '/' . $this->getFileName();

		if (!JFile::exists($filePath)) {
			return false;
		}

		return $filePath;
	}

	public function delete()
	{
		$path = $this->getPath();

		if (JFolder::exists($path)) {
			JFolder::delete($path);
		}

		return true;
	}
}