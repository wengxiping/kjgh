<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('site:/views/views');

class PayPlansViewCron extends PayPlansSiteView
{
	public function display($event=null, $args=null)
	{
		$debug = $this->input->get('debug', false, 'bool');

		// This ensures that there are no empty spaces messing with the image output
		@ob_get_flush();
		@ob_clean();		

		if (!headers_sent() && !$debug) {
			header("Content-type: image/png");
		}

		$cron = PP::cron();

		if (!$debug) {
			echo $cron->getImage();
		}

		// Ensure that cronjobs don't run too frequently
		if (!$cron->shouldRun()) {
			PP::markExit('Cron Job NOT REQUIRED');
			return false;
		}

		// Purge expired logs
		$cron->purgeExpiredLogs();
			
		// If simultaneous requests are coming then allow only one and reject the other request
		// XiTODO: We can increase timeOut instead of 0, if we want to execute the other request to wait for some given timeout
		$lock = PP::lock('payplansCron');
		$state = $lock->create();

		// Simulteneous process could be running
		if (!$state) {
			PP::markExit('Cron Job NOT REQUIRED');

			return;
		}

		$date = PP::date();

		$model = PP::model('Config');
		$model->save(array(
			'currentCronAcessTime' => $date->toUnix()
		));


		// Mark Start
		$message = JText::_('COM_PAYPLANS_LOGGER_CRON_START');
		PPLog::log(PPLogger::LEVEL_INFO, $message, null, array('Message' => $message), 'PayplansFormatter', 'Payplans_Cron');
					
		// trigger plugin and apps
		$args = array();
		PPEvent::trigger('onPayplansCron', $args);

		// Mark exit
		$message = JText::_('COM_PAYPLANS_LOGGER_CRON_EXECUTED');
		PP::markExit($message);
		
		PPLog::log(PPLogger::LEVEL_INFO, $message, null, array('Message' => $message), 'PayplansFormatter', 'Payplans_Cron');

		// Make it independent of XML file
		$model->save(array(
			'cronAcessTime' => $this->config->get('currentCronAcessTime')
		));

		// //create a log for rejection of one of simultaneous cron request, in case debug is on
		// if(JDEBUG){
		// 	$msg = JText::_('COM_PAYPLANS_LOGGER_SIMULTANEOUS_CRON_REQUEST_REJECTED');
		// 	PayplansHelperUtils::markExit($msg);
		// 	$content = array('Message'=>$msg);
		// 	PayplansHelperLogger::log(XiLogger::LEVEL_INFO, $msg, null, $content, 'PayplansFormatter','Payplans_Cron');
		// }
		exit;
	}
}