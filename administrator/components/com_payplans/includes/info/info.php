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

class PPInfo extends PayPlans
{
	static $instance = null;

	/**
	 * Object initialisation for the class to fetch the info object.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance	= new self();
		}

		return self::$instance;
	}

	/**
	 * Sets a message in the queue.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function set($message = '', $class = 'success', $namespace = null)
	{
		$session = JFactory::getSession();

		$obj = $message;

		if (! is_object($message)) {
			$obj = new StdClass();
			$obj->message = JText::_($message);
			$obj->type = $class;
		}

		$data = serialize($obj);

		$messages = $session->get('messages', array(), PP_SESSION_NAMESPACE);

		// Namespacing purposes to prevent duplication
		// Without namespacing (backwards/legacy), messages will just get queued indefinitely
		// With namespacing, only 1 instance of the same message should exist
		if (empty($namespace)) {
			$messages[]	= $data;
		} else {
			$messages[$namespace] = $data;
		}

		$session->set('messages', $messages, PP_SESSION_NAMESPACE);
	}

	/**
	 * Generates the info html block
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function html($additionalClasses = '')
	{
		$output = '';
		$session = JFactory::getSession();
		$messages = $session->get('messages', array(), PP_SESSION_NAMESPACE);

		// Remove this data once we retrieved it.
		$session->clear('messages', PP_SESSION_NAMESPACE);

		// If there's nothing stored in the session, ignore this.
		if (!$messages) {
			return;
		}

		if ($additionalClasses && !is_array($additionalClasses)) {
			$additionalClasses = array($additionalClasses);
		}

		if ($additionalClasses) {
			$additionalClasses = implode(' ', $additionalClasses);
		}

		foreach ($messages as $message) {
			$data = unserialize($message);

			if (!is_object($data)) {

				$obj = new stdClass();
				$obj->message = $data;
				$obj->type = 'info';

				$data = $obj;
			}


			$theme = PP::themes();

			$theme->set('content', $data->message);
			$theme->set('class', $data->type);
			$theme->set('additionalClasses', $additionalClasses);

			$output .= $theme->output('site/info/default');
		}

		return $output;
	}
}
