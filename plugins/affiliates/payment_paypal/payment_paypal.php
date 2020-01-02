<?php

/*------------------------------------------------------------------------
# com_invoices - Invoices for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2012 JoomlaInvoices.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaInvoices.com
# Technical Support:	Forum - http://www.JoomlaFinances.com/forum
-------------------------------------------------------------------------*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgAffiliatesPayment_paypal extends JPlugin {


	var $_payment_type = 'payment_paypal';


	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}


	function _process()
	{
		$data = JRequest::get('post');
		$errors = array();

		//$this->_logData($data);
		if ($ipnError = $this->_validateIPN($data)) {
			$errors[] = $ipnError;
		}

		// prepare some data
		$item_number_array	= explode(':', @$data['item_number']);
		$data["payment_id"] = (int) @$item_number_array[0];

		$payment_details = $this->_getFormattedPaymentDetails($data);

		// process the payment based on its type
		if ( ! empty($data['txn_type']) && $data['txn_type'] != 'subscr_cancel') {
			if ( ! ($user =& $this->_getUser( (int)$data['custom'], $data['payer_email'], $data['payer_id'] ))) {
				$errors[] = JText::_('Paypal Message Unknown User');

				$user =& JFactory::getUser();
				$user->set('id', 0);
			}

			if ($data['txn_type'] == 'web_accept') {
				$errors = $this->_processSale($data, $user, $payment_details, $errors);
			}

		}

		return count($errors) ? implode("\n", $errors) : 'processed';
	}


	function _processSale($data, $user, $payment_details, $errors)
	{

		//germi
		//we load the payment
		$payment = AffiliateHelper::getPaymentData($data['payment_id']);

		$payment_options = AffiliateHelper::getUserPaymentOptions($payment->user_id);

		$the_email = $payment_options->payment_paypal->email ;

		/*
		 * validate the payment data
		 */
		if (@$data['receiver_email'] != $the_email && @$data['business'] != $the_email ) {
			$errors[] = JText::_('PAYPAL MESSAGE RECEIVER INVALID');
		}
		//$this->_logData($data);
		// here we will make sure we haven't received less money,
		// we can't use the equal operation because of probable tax additions to the final sum
		if ((float)$data['mc_gross'] < (float)$payment->payment_amount) {
			$errors[] = JText::_('PAYPAL MESSAGE AMOUNT INVALID');
		}

		if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
			$errors[] = JText::sprintf('PAYPAL MESSAGE STATUS INVALID', @$data['payment_status']);
		}

		if(count($errors)) {

			$payment->payment_status = 0; //unpaid - errors
		}
		elseif(@$data['payment_status'] == 'Pending'){
			$payment->payment_status = 2; //pending
		}
		else {
			$payment->payment_status = 1; //paid
		}

		$payment->payment_type = $this->_payment_type;
		$payment->payment_details = $payment_details;
		//print_r($payment);print_r($errors);die;
		if(!count($errors)) {
			$this->update_payment($payment);
		}

		return $errors;
	}

	//germi
	function update_payment($payment){

		$db =& JFactory::getDBO();

		$query = 	' UPDATE #__affiliate_tracker_payments SET '.
					' payment_type =  "'.$payment->payment_type.'", '.
					' payment_datetime =  NOW(), '.
					' payment_details =  "'.$payment->payment_details.'", '.
					' payment_status =  '.$payment->payment_status.' '.
					' WHERE id = '.$payment->id .
					' LIMIT 1 ';

		$db->setQuery($query);
		$db->query();

	}


	function _logData($data)
	{
		$f = fopen(JPATH_CACHE . '/affiliates_paypal.txt', 'a');
		fwrite($f, "\n" . date('F j, Y, g:i a') . "\n");
		fwrite($f, print_r($data, true));
		fclose($f);
	}


	function _validateIPN($data)
	{

        return '';
	}


	function onRenderPaymentOptions( $row, $user_id )
	{
		/*
		 * get all necessary data and prepare vars for assigning to the template
		 */

		$user = JFactory::getUser($user_id);

		$payment_options = AffiliateHelper::getUserPaymentOptions($row->user_id);

		$payment_options->payment_paypal->email ;

		$vars = new JObject();

		$vars->receiver_email = $payment_options->payment_paypal->email;
		$vars->return_url = JURI::root()."administrator/index.php?option=com_affiliatetracker&controller=payment&task=process_payment&ptype={$this->_payment_type}&paction=display_message&item_number=".$row->id;
		$vars->cancel_url = JURI::root()."administrator/index.php?option=com_affiliatetracker&controller=payment&task=process_payment&ptype={$this->_payment_type}&paction=cancel&item_number=".$row->id;
		$vars->notify_url = JURI::root()."index.php?option=com_affiliatetracker&task=process_payment&ptype={$this->_payment_type}&paction=process&tmpl=component&item_number=".$row->id;
		$vars->currency = $this->params->get( 'currency', 'USD' );
		$vars->custom = $user->get('id');
		$vars->note = $this->params->get( 'description_back' );
		$vars->subParams =& $subParams;
		$vars->action_url = $this->_getPaypalUrl();
		$vars->row =& $row;
		$vars->user =& $user;

		$html = $this->_getLayout('form', $vars);

		$text = array();
		$text[] = $html;
		$text[] = $this->params->get( 'title', 'PayPal' );

		return $text;
	}

	function onRenderPaymentInputOptions( $vars )
	{

		$vars->params = $this->params ;

		$html = $this->_getLayout('inputform', $vars);

		$text = array();
		$text[] = $html;
		$text[] = $this->params->get( 'title', 'PayPal' );

		return $text;
	}


	function onProcessPayment( $row, $user )
	{
		$ptype 		= JRequest::getVar( 'ptype' );
		if ($ptype == $this->_payment_type)
		{
			$paction 	= JRequest::getVar( 'paction' );
			$html = "";

			switch ($paction) {
				case "display_message":

				  break;
				case "process":
					$html .= $this->_process();

					echo $html;
					$app =& JFactory::getApplication();
					//$app->close();
				  break;
				case "cancel":

				  break;
				default:

				  break;
			}

			return $html;
		}

	}

	function & _getUser($id, $email, $unique_gateway_id)
	{
		$user =& JFactory::getUser($id);

		return $user;
	}

	function _getPaypalUrl($full = true)
	{
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		if ($full) {
			$url = 'https://' . $url . '/cgi-bin/webscr';
		}

		return $url;
	}


    function _getParam($name, $default = '')
    {
    	$sandbox_param = "sandbox_$name";
    	$sb_value = $this->params->get($sandbox_param);

        if ($this->params->get('sandbox') && !empty($sb_value)) {
            $param = $this->params->get($sandbox_param, $default);
        }
        else {
        	$param = $this->params->get($name, $default);
        }

        return $param;
    }


	function _getLayout($layout, $vars = false, $plugin = '', $group = 'affiliates')
	{
		if ( ! $plugin) {
			$plugin = $this->_payment_type;
		}

		ob_start();
        $layout = $this->_getLayoutPath( $plugin, $group, $layout );
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

		return $html;
	}


    function _getLayoutPath($plugin, $group, $layout = 'default')
    {
        $app = JFactory::getApplication();

        // get the template and default paths for the layout
        $templatePath = JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'plugins'.DS.$group.DS.$plugin.DS.$plugin.DS.$layout.'.php';
        $defaultPath = JPATH_SITE.DS.'plugins'.DS.$group.DS.$plugin.DS.$plugin.DS.'tmpl'.DS.$layout.'.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists( $templatePath ))
        {
            return $templatePath;
        }
        else
        {
            return $defaultPath;
        }
    }


	function _getFormattedPaymentDetails($data)
	{
		$separator = "\n";
		$formatted = array();

		foreach ($data as $key => $value) {
			if ($key != 'view' && $key != 'layout' && $key != 'custom') {
				$formatted[] = $key . ' = ' . $value;
			}
		}

		return count($formatted) ? implode("\n", $formatted) : '';
	}

}

if ( ! function_exists('plg_affiliates_escape')) {

	function plg_affiliates_escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
}
