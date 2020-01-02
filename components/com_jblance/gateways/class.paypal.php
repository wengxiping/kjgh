<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	22 March 2012
 * @file name	:	gateways/class.paypal.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

defined('_JEXEC') or die('Restricted access');

class paypal_class {
	var $last_error;
	var $ipn_log;
	var $ipn_log_file;
	var $ipn_response;
	var $ipn_data = array();
	var $fields = array();
	var $payconfig = array();
	var $details = array();
	
	function __construct($payconfig, $details){
		$this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
		$this->last_error = '';
		$this->ipn_log_file = '.ipn_results.log';
		$this->ipn_log = true; 
		$this->ipn_response = '';
		$this->add_field('rm','2');
		$this->add_field('cmd','_xclick'); 
		$this->payconfig = $payconfig;
		$this->details = $details;
	}
	
	function paypalPayment(){
		
		$payconfig   = $this->payconfig;
		$pptestmode  = $payconfig->ppTestmode;
		$paypalEmail = $payconfig->paypalEmail;
		$ppCurrency  = $payconfig->ppCurrency;
	
		$details 	= $this->details;
		$amount 	= $details['amount'];
		$taxrate 	= $details['taxrate'];
		$totamt 	= (float)($amount + $amount * ($taxrate/100));	$totamt = round($totamt, 2);
		$orderid 	= $details['orderid'];
		$itemname 	= $details['itemname'];
		$item_num 	= $details['item_num'];
		$invoiceNo 	= $details['invoiceNo'];
	
		$link_status = JUri::base().'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=paypal';
		$link_cancel = JRoute::_(JUri::base().'index.php?option=com_jblance&view=membership&layout=thankpayment&type=cancel');
		
		$this->paypal_url = ($pptestmode) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	
		$this->add_field('business', $paypalEmail);
		$this->add_field('return', $link_status);
		$this->add_field('cancel_return', $link_cancel);
		$this->add_field('notify_url', $link_status);
		$this->add_field('item_name', $itemname);
		$this->add_field('item_number', $item_num);
		$this->add_field('amount', $totamt);
		$this->add_field('invoice', $invoiceNo);
		$this->add_field('no_note', "1");
		$this->add_field('no_shipping', "1");
		$this->add_field('currency_code', $ppCurrency);
		$this->add_field('tax_rate', 0);
		$this->submit_paypal_post(); // auto submit the fields to paypal
		?>
	<script>
		document.paypal_form.submit();
	</script>
	<?php
	}
   
	function add_field($field, $value){
		$this->fields["$field"] = $value;
	}

	function submit_paypal_post(){
		echo '<form method="post" name="paypal_form" action="'.$this->paypal_url.'">';
		foreach ($this->fields as $name => $value){
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
		}
		echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', 'PayPal');
		echo '<br><br><input type="submit" value="'.JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT').'">';
		echo '</form>';
	}
	
	
	//15.Return Paypal
	function paypalReturn($data){
		$payconfig   = $this->payconfig;
		$pptestmode = $payconfig->ppTestmode;
		//$this->paypal_url = ($pptestmode) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$return = array();
		
		require(JPATH_SITE.'/components/com_jblance/gateways/includes/paypal/PaypalIPN.php');
		
		$ipn = new PaypalIPN();
		// Use the sandbox endpoint during testing.
		if($pptestmode){
			$ipn->useSandbox();
		}
		$isValid = $ipn->verifyIPN();
		
		//$isValid = $this->validate_ipn($data);
		
		//Check if the seller email is correct
		if($isValid){
			//Check if the seller email is correct
			if($payconfig->paypalEmail != $data['business']){
				$isValid = false;
				$return['failure_reason'] = 'Merchant Email does not match Receiver Email';
			}
		}
		
		//check if the amount paid is correct
		if($isValid){
			$invoice_num 	= array_key_exists('invoice', $data) ? $data['invoice'] : '';		// get the invoice number from the POST variable
			$mc_gross 		= floatval($data['mc_gross']);
			$financeHelp 	= JblanceHelper::get('helper.finance');		// create an instance of the class FinanceHelper
			$details 		= $financeHelp->getPaymentDetailsFromInvoice($invoice_num);
			
			$isValid = ($details['amount'] - $mc_gross) < 0.01;
			
			if(!$isValid)
				$return['failure_reason'] = 'The amount paid does not match the amount in the database';
		}
		

		if($isValid){
			$invoice_num 	= array_key_exists('invoice', $data) ? $data['invoice'] : '';		// get the invoice number from the POST variable
			$return['success'] = true;
			$return['invoice_num'] = $invoice_num;
		}
		else 
			$return['success'] = false;
		
		return $return;
	}
   
	/* function validate_ipn($data){
		$url_parsed = parse_url($this->paypal_url);
		
		$post_string = '';    
		foreach ($data as $field=>$value){ 
			$this->ipn_data["$field"] = $value;
			$post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
		}
		$post_string.="cmd=_notify-validate";

		$fp = fsockopen('ssl://'.$url_parsed['host'], 443, $errnum, $errstr, 30); 
		if(!$fp){
			$this->last_error = "Error : fsockopen error no. $errnum: $errstr";
			return false;
		}
		else { 
			fputs($fp, "POST ".$url_parsed['path']." HTTP/1.1\r\n");
			fputs($fp, "Host: ".$url_parsed['host'].":443\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $post_string . "\r\n\r\n");
			
			while(!feof($fp)){ 
				$this->ipn_response .= fgets($fp, 1024); 
			} 
			fclose($fp); // close connection
		}
		if (preg_match('/VERIFIED/i', $this->ipn_response)){
			return true;       
		}
		else {
			$this->last_error = 'Error : IPN Validation Failed.';
			return false;
		}
	} */
}
