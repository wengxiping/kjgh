<?php

/*------------------------------------------------------------------------
# com_affiliatetracker - Affiliate system for Joomla
# ------------------------------------------------------------------------
# author				Joomlathat
# copyright 			Copyright (C) 2012 Joomlathat.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.Joomlathat.com
# Technical Support:	Forum - http://www.Joomlathat.com/support
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgAffiliatesPayment_stripe extends JPlugin
{

    var $_payment_type = 'payment_stripe';

    function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
    }

    function onRenderPaymentOptions( $row, $user_id )
    {
        /*
         * get all necessary data and prepare vars for assigning to the template
         */

        $user = JFactory::getUser($user_id);

        $payment_options = AffiliateHelper::getUserPaymentOptions($row->user_id);

        $vars = new JObject();

        $vars->action_url = JURI::root()."index.php?option=com_affiliatetracker&task=process_payment&ptype={$this->_payment_type}&paction=process&tmpl=component&item_number=".$row->id;
        $vars->note = $this->params->get( 'description' );
        $vars->row = $row;
        $vars->user = $user;
        $vars->secret_key = $payment_options->payment_stripe->secret_key;
        $vars->publishable_key = $payment_options->payment_stripe->publishable_key;
        $vars->currency = $this->params->get('currency', 'USD');

        $html = $this->_getLayout('form', $vars);

        $text = array();
        $text[] = $html;
        $text[] = $this->params->get( 'title', 'Stripe' );

        return $text;
    }

    function onRenderPaymentInputOptions( $vars )
    {
        $vars->params = $this->params ;

        $html = $this->_getLayout('inputform', $vars);

        $text = array();
        $text[] = $html;
        $text[] = $this->params->get( 'title', 'Stripe' );

        return $text;
    }

    function onProcessPayment($row, $user)
    {
        $payment_options = AffiliateHelper::getUserPaymentOptions($row->user_id);

        $vars = new JObject();
        $vars->secret_key = $payment_options->payment_stripe->secret_key;
        $vars->publishable_key = $payment_options->payment_stripe->publishable_key;

        require_once('config.php');

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

                    break;
                case "cancel":

                    break;
                default:

                    break;
            }

            return $html;
        }
        return;
    }

    private function _process()
    {
        $app = JFactory::getApplication();

        $token  = $_POST['stripeToken'];

        $data = JRequest::get( 'post' );

        $customer = \Stripe\Customer::create(array(
            'email' => $data['stripeEmail'],
            'card'  => $token
        ));

        $payment_details = $this->_getFormattedPaymentDetails($data);

        try {
            $charge = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount' => $data['amount']*100, // *100 because stripe amount only accepts cents.
                'currency' => $this->params->get( 'currency', 'USD' )
            ));

            if ($charge->__get('paid')) {
                $model = $this->getPaymentsModel();
                $payment = AffiliateHelper::getPaymentData($data['item_number']);
                $payment->payment_status = 1; //paid
                $payment->payment_type = $this->_payment_type;
                $payment->payment_details = $payment_details;
                $payment->payment_datetime = date('Y-m-d H:i:s');
                $model->store(get_object_vars($payment));
            }

            $app->enqueueMessage($charge->__get('status'));

        } catch(Exception $e) {
            $msg = $e->getMessage();
            $app->enqueueMessage($msg);
        }

        $app->redirect(JURI::root()."/administrator/index.php?option=com_affiliatetracker&controller=payment&task=edit&cid[]=".$data['item_number']);
    }

    private function _getLayout($layout, $vars = false, $plugin = '', $group = 'affiliates')
    {
        if ( ! $plugin) {
            $plugin = $this->_payment_type;
        }

        ob_start();
        $layout = $this->_getLayoutPath( $plugin, $group, $layout );
        //print_r($layout);die;
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    private function _getLayoutPath($plugin, $group, $layout = 'default')
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

    private function getPaymentsModel()
    {
        if (!class_exists( 'PaymentsModelPayment' ))
        {
            // Build the path to the model based upon a supplied base path
            $path = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_affiliatetracker'.DS.'models'.DS.'payment.php';
            $false = false;

            // If the model file exists include it and try to instantiate the object
            if (file_exists( $path )) {
                require_once( $path );
                if (!class_exists( 'PaymentsModelPayment' )) {
                    JError::raiseWarning( 0, 'Model class PaymentsModelPayment not found in file.' );
                    return $false;
                }
            } else {
                JError::raiseWarning( 0, 'Model PaymentsModelPayment not supported. File not found.' );
                return $false;
            }
        }

        $model = new PaymentsModelPayment();
        return $model;
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
