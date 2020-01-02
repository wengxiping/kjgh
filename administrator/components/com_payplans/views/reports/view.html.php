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

class PayPlansViewReports extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('reports');
	}
	
	/**
	 * Displays the export layout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function export($tpl = null)
	{
		$this->heading('Export Reports');

		JToolbarHelper::custom('reports.export', '', '', JText::_('COM_PP_REPORTS_CSV_EXPORT'), false);

		$types = array('invoice' => 'Invoices', 'user' => 'Users', 'subscription' => 'Subscriptions');
		$exportTypes = array();
		
		foreach($types as $key => $val) {
			$obj = new stdClass();
			$obj->title = $val;
			$obj->value = $key;

			$exportTypes[] = $obj;
		}

		// Retrieve available payment gateway
		$model = PP::model('App');
		$options = array('group' => 'payment', 'published' => 1);
		$gateways = $model->loadRecords($options);

		$this->set('exportTypes', $exportTypes);
		$this->set('gateways', $gateways);

		parent::display('reports/export/default');
	}

	/**
	 * Renders the download pdf invoice layout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function pdfinvoice($tpl = null)
	{
		$this->heading('PDF Invoice');

		JToolbarHelper::custom('reports.downloadPdf', '', '', JText::_('COM_PP_REPORTS_DOWNLOAD_PDF'), false);

		$types = array('invoiceKey' => 'Invoices Key', 'transactionDate' => 'Transaction Date');
		$exportTypes = array();
		
		foreach ($types as $key => $val) {
			$obj = new stdClass();
			$obj->title = $val;
			$obj->value = $key;

			$exportTypes[] = $obj;
		}

		$this->set('exportTypes', $exportTypes);

		parent::display('reports/pdfinvoice/default');
	}
}