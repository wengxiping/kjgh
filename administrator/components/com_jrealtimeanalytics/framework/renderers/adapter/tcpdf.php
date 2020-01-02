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
define ( 'PDF_CREATOR', 'TCPDF' );
define ( 'K_TCPDF_EXTERNAL_CONFIG', true );
define ( "K_PATH_MAIN", JPATH_COMPONENT_ADMINISTRATOR . "/framework/renderers/tcpdf" );
define ( "K_PATH_URL", JPATH_BASE );
define ( "K_PATH_FONTS", K_PATH_MAIN . '/fonts/' );
define ( "K_PATH_CACHE", K_PATH_MAIN . "/cache" );
define ( "K_PATH_URL_CACHE", K_PATH_URL . "/cache" );
define ( "K_PATH_IMAGES", K_PATH_MAIN . "/images" );
define ( "K_BLANK_IMAGE", K_PATH_IMAGES . "/_blank.png" );
define ( "K_CELL_HEIGHT_RATIO", 1.25 );
define ( "K_TITLE_MAGNIFICATION", 1.3 );
define ( "K_SMALL_RATIO", 2 / 3 );
define ( "HEAD_MAGNIFICATION", 1.1 );
require_once JPATH_COMPONENT_ADMINISTRATOR . '/framework/renderers/tcpdf/tcpdf.php';

/**
 * Renderer PDF content
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage renderers
 * @subpackage adapter
 * @since 1.2
 */
class JRealtimeRenderersAdapterTcpdf extends JRealtimeRenderersBaseadapter implements JRealtimeRenderersAdapter {
	/**
	 * TCPDF instance
	 *
	 * @access private
	 * @var Object
	 */
	private $tcpdf;
	
	/**
	 * String data to convert from HTML
	 *
	 * @access private
	 * @var string
	 */
	private $html_pdf;
	
	/**
	 * Conversione PDF del report ML
	 *
	 * @param string $data        	
	 * @param Object $model        	
	 */
	public function renderContent($data, $model, $filename = 'analytics_stats_report_', $mode = 'D') {
		$this->html_pdf = $data;
		$dataExport = date ( 'Y-m-d H:i:s', time () );
		// create new PDF document
		$this->tcpdf = new TCPDF ( 'P', 'mm', 'A4', true, 'UTF-8', false );
		
		$config = JFactory::getConfig ();
		$sitename = $config->get ( 'sitename' ) . ' - ' . JUri::root ();
		$from = $model->getState ( 'fromPeriod' );
		$to = $model->getState ( 'toPeriod' );
		
		// set document information
		$this->tcpdf->SetCreator ( PDF_CREATOR );
		$this->tcpdf->SetAuthor ( 'Analytics stats' );
		$this->tcpdf->SetTitle ( 'JRealtime Analytics' );
		$this->tcpdf->SetSubject ( 'Stats' );
		$this->tcpdf->SetHeaderData ( null, 50, $sitename, $from . ' - ' . $to );
		$this->tcpdf->setDisplayMode ( 'real' );
		
		// set header and footer fonts
		$this->tcpdf->setHeaderFont ( array (
				'freesans',
				'',
				10 
		) );
		$this->tcpdf->setFont ( 'freesans', '', 8 );
		$this->tcpdf->setFooterFont ( array (
				'freesans',
				'',
				8 
		) );
		
		// set margins
		$this->tcpdf->SetMargins ( 10, 20, 10 );
		$this->tcpdf->SetHeaderMargin ( 10 );
		$this->tcpdf->SetFooterMargin ( 10 );
		
		// set auto page breaks
		$this->tcpdf->SetAutoPageBreak ( TRUE, 10 );
		
		// set image scale factor
		$this->tcpdf->setImageScale ( 1.5 );
		
		// For security safe convertiamo in UTF-8 per TCPDF
		// $this->html_pdf = iconv ( 'ISO-8859-1', 'UTF-8', $this->html_pdf );
		// Print text using writeHTMLCell()
		$this->html_pdf = preg_replace ( '/[\t]*[\s]+/', ' ', $this->html_pdf );
		$chunks = explode ( '#newpagestart#', $this->html_pdf );
		foreach ( $chunks as $chunk ) {
			// Add a page
			$this->tcpdf->AddPage ();
			$this->tcpdf->writeHTML ( $chunk );
		}
		
		if($this->cParams->get('report_byemail', 0) && is_object($this->mailer)) {
			$mode = 'S';
		}

		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		if ($mode == 'S') {
			$data = $this->tcpdf->Output ( null, $mode );
			// Check for report by email submission
			if($this->cParams->get('report_byemail', 0) && is_object($this->mailer)) {
				// Send email function call
				$sent = $this->sendEmail($data, 'report.pdf', 'application/pdf');
				echo $sent;
			}
		} else {
			$this->tcpdf->Output ( $filename . $dataExport . '.pdf', $mode );
		}
		exit ();
	}
}