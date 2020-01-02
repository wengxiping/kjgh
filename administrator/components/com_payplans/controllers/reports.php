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

class PayplansControllerReports extends PayplansController
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('reports');
	}
	
	public function export()
	{
		$type = $this->input->get('type', 'invoice');
		$plans = $this->input->get('plans');
		$subsStatus = $this->input->get('subsStatus');
		$invStatus = $this->input->get('invStatus');
		$limit = $this->input->get('limit', 50, 'int');
		$gateway = $this->input->get('gateway');

		$status = $type == 'invoice' ? $invStatus : $subsStatus;

		$model = PP::model($type);

		$options = array();
		$options['plans'] = $plans;
		$options['status'] = $status;
		$options['limit'] = $limit;
		$options['gateway'] = $gateway;

		$dateRange = $this->input->get('daterange', array());
		$options['dateFrom'] = '';
		$options['dateTo'] = '';

		if ($dateRange) {
			list($from, $to) = $dateRange;

			if ($from) {
				$options['dateFrom'] = PP::date($from)->toSql();
			}

			if ($to) {
				$options['dateTo'] = PP::date($to)->toSql();	
			}
		}

		// If from and End date both are same then add 1 day to endDate
		if ($options['dateFrom'] == $options['dateTo']) {
			$dateTo = PP::date($to)->addExpiration('000001000000');
			$options['dateTo'] = $dateTo->toSql();
		}

		$records = $model->getDataToExport($options);

		if (empty($records)) {
			$this->info->set('COM_PP_REPORT_NO_RECORD', 'error');
			return $this->redirectToView('reports', 'export');
		}

		$header = array_keys((array)$records[0]);
		$output = fopen('php://output', 'w');
		fputcsv($output, (array) $header);

		foreach ($records as $record) {

			if ($type == 'invoice' || $type == 'subscription' ) {
				$record->status = $model->getStatusString($record->status);	

				if ($type == 'invoice') {
					$appLib = PP::app($record->gateway);
					$record->gateway = $appLib->getTitle();
				}

			}

			fputcsv($output, (array) $record);
		}

		$date = JFactory::getDate();

		$fileName = 'export_' . $type . '_' . $date->format('m_d_Y') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $fileName);

		fclose($output);
		exit;
	}

	public function downloadPdf()
	{
		// Todo: ability to download invoice by transaction date
		$invoiceKey = $this->input->get('invoice_key', '');
		$type = $this->input->get('type', '');

		// Debug Code
		// $invoiceKey = '8O56WJCMAUEK';

		if ($type == 'invoiceKey') {

			$filePrefix = base64_encode($invoiceKey);

			// Get the invoice Id from the provided key
			$invoiceId = (int) PP::encryptor()->decrypt($invoiceKey);

			// Load the invoice object
			$invoice = PP::invoice($invoiceId);

			if (!$invoice->invoice_id) {
				$this->info->set('COM_PP_INVALID_INVOICE_KEY', 'error');
				return $this->redirectToView('reports', 'pdfinvoice');
			}

			$pdf = PP::pdf($invoice);
			$pdfContent = $pdf->generateContent();

		} else {
			$dateRange = $this->input->get('daterange', array());
			$limit = $this->input->get('limit', 50, 'int');

			if (!$dateRange) {
				$this->info->set('COM_PP_INVALID_DATE', 'error');
				return $this->redirectToView('reports', 'pdfinvoice');
			}

			list($from, $to) = $dateRange;

			if (empty($from) || empty($to)) {
				$this->info->set('COM_PP_INVALID_DATE', 'error');
				return $this->redirectToView('reports', 'pdfinvoice');
			}

			$from = PP::date($from)->toSql();
			$to = PP::date($to)->toSql();

			$model = PP::model('invoice');
			$results = $model->getInvoiceWithinDates(array('from' => $from, 'to' => $to, 'limit' => $limit));

			if (empty($results)) {
				$this->info->set('COM_PP_NO_INVOICES_ON_SELECTED_DATES', 'error');
				return $this->redirectToView('reports', 'pdfinvoice');
			}

			$invoices = array();
			foreach ($results as $result) {
				$invoices[] = $result->invoice_id;
			}

			$from = explode(' ', $from);
			$to = explode(' ', $to);

			$filePrefix = 'invoices_' . $from[0] . '_' . $to[0];

			$pdfContent = '<div style="page-break-after: auto;"></div>';

			foreach ($invoices as $invoiceId) {
				$invoice = PP::invoice($invoiceId);
				$pdf = PP::pdf($invoice);
				$pdfContent .= $pdf->generateContent();
				
				// We need to use pagebreak for every pdf
				if (next($invoices) !== false) {
					$pdfContent .= '<div style="page-break-after: auto;"></div>';
				}
			}
		}

		// Convert it into a pdf format
		$pdfObj = $pdf->saveToPdf($pdfContent);
		$pdfObj->stream($filePrefix . '.pdf');

		exit;
	}
}