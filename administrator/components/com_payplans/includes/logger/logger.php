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

class PPLogger extends PayPlans
{
	const LEVEL_DEBUG   = 0;
	const LEVEL_INFO    = 1;
	const LEVEL_NOTICE  = 2;
	const LEVEL_WARNING = 3;
	const LEVEL_ERROR   = 4;

	protected $level = null;

	static $_levels  = null;
		
	public function __construct($level = PPLogger::LEVEL_INFO)
	{
		$this->level = $level;
	}

	/**
	 * Main entry point to log data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function log($level, $message, $object_id, $class, $content = null, $type, $token, $sendemail= false)
	{
		if ($level < $this->level) {
			return false;
		}

		$logId = $this->save($level, $message, $object_id, $class, $content, $type, $token);

		// Send notification e-mails
		if ($level == self::LEVEL_ERROR && $sendemail) {

			$subject = JText::_('COM_PAYPLANS_ERROR_LOG_SUBJECT');
			$namespace = 'emails/log/error';

			$mailer = PP::mailer();
			$emails = $mailer->getAdminEmails();

			// IMP: when there are no users who can receive system emails then return log_id
			if (!$emails) {
				return $logId;
			}

			$params = array(
				'message' => $message,
				'object_id'=>$object_id,
				'class'=>$class,
				'content'=> $content
			);
			foreach ($emails as $email) {
				$mailer->send($email, $subject, $namespace, $params);
			}
		}

		return $logId;
	}

	/**
	 * Retrieves the current log level
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLogLevel()
	{
		return $this->level;
	}

	/**
	 * Allows caller to set the log level to log contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setLogLevel($level)
	{
		$this->level = $level;
	}

	static public function getLevels()
	{	
		if (self::$_levels === null) {
			self::$_levels[self::LEVEL_INFO] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_INFO');
			self::$_levels[self::LEVEL_NOTICE] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_NOTICE');
			self::$_levels[self::LEVEL_WARNING] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_WARNING');
			self::$_levels[self::LEVEL_ERROR] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_ERROR');
			self::$_levels[self::LEVEL_DEBUG] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_DEBUG');
		}
		
		return self::$_levels;
	}
	
	public function getLevelText($level)
	{
		$levels[self::LEVEL_INFO] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_INFO');
		$levels[self::LEVEL_NOTICE] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_NOTICE');
		$levels[self::LEVEL_WARNING] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_WARNING');
		$levels[self::LEVEL_ERROR] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_ERROR');
		$levels[self::LEVEL_DEBUG] = JText::_('COM_PAYPLANS_LOGGER_LEVEL_DEBUG');

		return isset($levels[$level]) ? $levels[$level] : JText::_('COM_PAYPLANS_LOGGER_UNKNOWN_LEVEL');
	}

	/**
	 * Retrieves the current user's ip address
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getIp()
	{
		static $ip = null;

		if (is_null($ip)) {

			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}

			if (!$ip && isset($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			// If IP is still not detected, use a default value
			if (!$ip) {
				$ip = JText::_('COM_PAYPLANS_LOGGER_REMOTE_IP_NOT_DEFINED');
			}
		}

		return $ip;
	}

	/**
	 * Method is not exposed as this is used internally to store into the database
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function save($level, $message, $objectId, $class, $content = null, $type, $token='')
	{	
		$user = JFactory::getUser();
		$ip = $this->getIp();

		$data = array(
			'log_id' => 0,
			'user_id' => $user->id ? $user->id : JFactory::getSession()->get('REGISTRATION_USER_ID'),
			'object_id' => $objectId,
			'class' => $class,
			'user_ip' => $ip,
			'message' => $message,
			'content' => '',
			'level' => $level,
			'current_token' => $token,
			'position' => '',
			'owner_id' => '',
			'previous_token' => '',
			'created_date' => JFactory::getDate()->toSql(),
			'legacy' => 0
		);

		// Try to get the owner id		
		if (isset($objectId) && $objectId != 0 && is_array($content)) {
			$data['owner_id'] = PPLog::getOwnerId($content);
		}
		
		// In order to not save the data of cron again and again, this work is done.
		// in this calculate the previous position of cron(if already exist) and update the row
		// else follow the normal process. 
		$model = PP::model('Log');
		$tokenExist = PPLog::calculatePreviousposition($token, $class);

		if ($tokenExist) {
			$data['previous_token'] = "";
			$data['position'] = $tokenExist;
			$id = $model->save($data, 0);

			return $id;
		}

		if (isset($content['current']) || isset($content['previous'])) {
			$data['previous_token'] = PPLog::calculatePreviousToken($objectId, $class, $content);
		}

		$log['type'] = $type;

		
		// When log for sample data migration is created then $content is in form of string. So it is required 
		// to convert in a array.
		if (!is_array($content)) {
			$content = array($content);
		}

		$log['content'] = $content;
		
		// If previous token is there, then store only the current token.
		if (!empty($data['previous_token']) || (isset($content['previous']) && empty($content['previous']))) {
			$log['content'] = isset($content['current']) ? $content['current'] : $content;
		}
		
		// Create a new record in <token>content</token> format
		$content = json_encode(serialize($log));
		$finalContent = '<' . $token . '>' . $content . '</'.$token.'>' . "\n";
		
		// Save the data on the log table first
		$id = $model->save($data, 0);

		if (!$id) {
			return false;
		}
	
		// Save the data in file
		$position = PPLog::writeToFile($id, $finalContent);
		$model->save(array('position' => $position, 'legacy' => 0), $id);		

		return $id;
	}
}
