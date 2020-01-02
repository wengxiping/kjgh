<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\renderers\adapter;
/**
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @subpackage adapter
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 * Renderer PDF content
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @subpackage adapter
 * @since 1.2
 */
class JRealtimeRenderersAdapterXls extends JRealtimeRenderersBaseadapter implements JRealtimeRenderersAdapter {
	/**
	 * Format and output data in CSV format
	 *
	 * @param string $data        	
	 * @param Object $model        	
	 */
	public function renderContent($data, $model, $filename = 'analytics_stats_report_') {
		// Check for report by email submission
		if($this->cParams->get('report_byemail', 0) && is_object($this->mailer)) {
			// Send email function call
			$sent = $this->sendEmail($data, 'report.xls', 'application/vnd.ms-excel');
			echo $sent;
			exit();
		}
		
		// Set file date
		$dataExport = date ( 'Y-m-d H:i:s', time () );
		
		// Recupero output buffer content
		$exportedFileName = $filename . $dataExport . '.xls';
		header ( 'Pragma: public' );
		header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Content-Disposition: attachment; filename="' . $exportedFileName . '"' );
		header ( 'Content-Type: application/vnd.ms-excel' );
		
		echo $data;
		
		exit ();
	}
}