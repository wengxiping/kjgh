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

class PPPdfAbstract extends PayPlans
{
	/**
	 * Save PDF contents into a file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function saveToPdf($contents)
	{
		// Load the dompdf lib
		require_once(PP_LIB . '/pdf/dompdf/autoload.inc.php');

		$theme = PP::themes();
		$theme->set('contents', $contents);
		$output = $theme->output('site/invoice/pdf');

		$pdf = new Dompdf\Dompdf();
		$pdf->set_paper("a4", "portrait");
		$pdf->set_option("isHtml5ParserEnabled", true);
		$pdf->set_option("isRemoteEnabled", true);

		$context = stream_context_create([
			'ssl' => [
				'verify_peer' => FALSE,
				'verify_peer_name' => FALSE,
				'allow_self_signed'=> TRUE
			]
		]);

		$pdf->setHttpContext($context);
		$pdf->load_html($output);
		$pdf->render();
		
		return $pdf;
	}
}