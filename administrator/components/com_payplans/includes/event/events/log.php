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

PP::load('Log');

class PPEventLog extends PayPlans
{
	static $prevConfig = null;

	public function delete($itemId, $type='PLAN', $formatter='PayplansFormatter')
	{
		$string = strtoupper($type) . '_' . 'DELETED';
		$message = JText::_('COM_PAYPLANS_LOGGER_' . $string);
		$session = JFactory::getSession();
		
		$object = $session->get('OBJECT_TO_BE_DELETED_' . $itemId . '_' . $type, null);
		$content['previous'] = $object ? $object->toArray() : array();
	
		PPLog::log(PPLogger::LEVEL_INFO, $message, $object, $content, $formatter);

		$session->clear('OBJECT_TO_BE_DELETED_' . $itemId . '_' . $type);

		return true;
	}

	/**
	 * Writes to the log queue
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save($previous, $current, $type = 'ORDER', $formatter = 'PayplansFormatter')
	{
		$string = strtoupper($type) . '_' . ($previous ? 'UPDATED' : 'CREATED');

		$message = JText::_('COM_PAYPLANS_LOGGER_' . $string);

		$content = PPLogFormatter::writer($previous, $current);

		$previousContent = $content['previous'];
		$currentContent = $content['current'];

		//IMP: Don't required log when Migration is running. 
		if ((defined('PAYPLANS_MIGRATION_START') && !defined('PAYPLANS_MIGRATION_END')) || $this->isEqual($previousContent, $currentContent, $previous) == false) {
			return true;
		}

		// Log the update in status/amount
		PPLog::log(PPLogger::LEVEL_INFO, $message, $current, $content, $formatter);
		
		return true;
	}

	/**
	 * Triggers after order is stored
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansOrderAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'ORDER', 'PayplansOrderFormatter');
	}
	
	/**
	 * Triggers after a subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'SUBSCRIPTION', 'PayplansSubscriptionFormatter');
	}
	
	public function onPayplansPaymentAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'PAYMENT');
	}
	
	public function onPayplansPlanAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'PLAN', 'PayplansPlanFormatter');
	}
	
	public function onPayplansAppAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'APP', 'PayplansAppFormatter');
	}
	
	public function onPayplansPlanAfterDelete($itemId)
	{
		return $this->delete($itemId,'PLAN', 'PayplansPlanFormatter');
		
	}
	
	public function onPayplansOrderAfterDelete($itemId)
	{
		return $this->delete($itemId, 'ORDER', 'PayplansOrderFormatter');
	}
	
	public function onPayplansSubscriptionAfterDelete($itemId, $obj)
	{
		$message = JText::_('COM_PAYPLANS_LOGGER_SUBSCRIPTION_DELETED');
			
		$content = array('previous' => $obj->toArray());
	
		PPLog::log(PPLogger::LEVEL_INFO, $message, $obj->toArray(), $content, 'PayplansSubscriptionFormatter', '', true);		
		return true;
		// return $this->delete($itemId, $obj, 'SUBSCRIPTION', '');
	}
	
	public function onPayplansPaymentAfterDelete($itemId)
	{
		return $this->delete($itemId,'PAYMENT');
	}
	
	public function onPayplansAppAfterDelete($itemId)
	{
		return $this->delete($itemId,'APP', 'PayplansAppFormatter');
	}

	/**
	 * Curently only available execute for the configuration setting
	 *
	 * @since	4.0.3
	 * @access	public
	 */	
	public function onPayplansControllerBeforeExecute($controller, $task)
	{
		if ($task == 'save' && $this->app->isAdmin()) {
			self::$prevConfig = PP::config(true);
		}

		return true;
	}

	/**
	 * Curently only available execute for the configuration setting
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function onPayplansControllerAfterExecute($controller, $task)
	{		
		if ($task != 'save' || !$this->app->isAdmin()) {				
			return true;
		}

		$message = JText::_('COM_PAYPLANS_LOGGER_CONFIG_UPDATED');

		// Return config data before save
		$previousConfig = self::$prevConfig;

		// Return config data after save
		$currentConfig = PP::config(true);

		// Format the config data before do the comparison
		$finalContent = PPLogFormatter::writer($previousConfig, $currentConfig);

		$previousContent = $finalContent['previous'];
		$currentContent = $finalContent['current'];

		if (self::isEqual($previousContent, $currentContent, $previousConfig) == false) {
			return true;
		}

		// log these previous and after data into log
		PPLog::log(PPLogger::LEVEL_INFO, $message, null, $finalContent, 'PayplansConfigFormatter', 'PPConfig');

		return true;
	}
	
	/**
	 * Determines if two copy of the objects are the same
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isEqual($previous, $current, $prevObject)
	{
		//only record log if there is a real change.
		$params = array('_appplans', '_planapps', '_groups','_plans');
		
		foreach ($current as $key => $val) {
			
			if (preg_match('/^_/',$key) && !in_array($key, $params)) {
				continue;
			}
			
			// if empty params in both previous and current
			// there are some cases where previous is empty array and current is unintialized
			if (in_array($key, $params)) {
				if (empty($current[$key]) && empty($previous[$key])) {
					continue;
				}
			}
			
			if (!$prevObject || ($current[$key] != $previous[$key])) {
				return true;
			}
		}

		return false;
	}
	
	
	// during post migration add log and also delete previous logs of OPS , plan and App
	static function onPayplansStartMigration()
	{
		// XiTODO:: delete invoice and transaction  logs
		$model = PP::log();
		
		// For joomla3.0 compatibility
		$conditions = array('class'=>'"PayplansSubscription"');
		$model->deleteMany($conditions, 'OR');

		$conditions = array('class'=>'"PayplansPayment"');
		$model->deleteMany($conditions, 'OR');	
		
		$conditions = array('class'=>'"PayplansOrder"');
		$model->deleteMany($conditions, 'OR');
		
		$conditions = array('class'=>'"PayplansPlan"');
		$model->deleteMany($conditions, 'OR');
		
		$conditions = array('class'=> '"PayplansApp%"');
		$model->deleteMany($conditions, 'OR', 'LIKE');

		return true;
	}

	public function onPayplansPostMigration($pluginKey)
	{	
		$message = JText::_('COM_PAYPLANS_MIGRATION_SUCCESS_'.JString::strtoupper($pluginKey));
		PPLog::log(PPLogger::LEVEL_INFO, $message, null, $message);
		return true;
	}
	
	public function onPayplansGroupAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'GROUP', 'PayplansGroupFormatter');
	}

	public function onPayplansGroupAfterDelete($itemId, $obj)
	{
		$message = JText::_('COM_PAYPLANS_LOGGER_GROUP_DELETED');
			
		$content = array('previous' => $obj->toArray());
		
		PPLog::log(PPLogger::LEVEL_INFO, $message, $obj->toArray(), $content, 'PayplansGroupFormatter');
		
		return true;
	}
	
	public function onPayplansUserAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'USER', 'PayplansUserFormatter');
	}
	
	public function onPayplansInvoiceAfterSave($previous, $current)
	{
		return $this->save($previous, $current, 'INVOICE', 'PayplansInvoiceFormatter');
	}

	public function onPayplansInvoiceAfterDelete($itemId)
	{
		return $this->delete($itemId,'INVOICE', 'PayplansInvoiceFormatter');
	}
	
}