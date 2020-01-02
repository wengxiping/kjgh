<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/textbox/textbox');

class SocialFieldsEventTitle extends SocialFieldsUserTextbox
{
	/**
	 * Support for generic getFieldValue('TITLE')
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function getValue()
	{
		$container = $this->getValueContainer();

		if ($this->field->type == SOCIAL_TYPE_EVENT && !empty($this->field->uid)) {
			$event = FD::event($this->field->uid);

			$container->value = $event->getName();

			$container->data = $event->title;
		}

		return $container;
	}

	/**
	 * Displays the event title textbox.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		// The value will always be the event title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->getName();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the event description textbox.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$cluster, $errors)
	{
		$clusterName = JText::_($this->params->get('default'), true);

		if ($cluster->id) {
			$clusterName = $cluster->getName();
		}
		
		// The value will always be the event title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $clusterName;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to a user.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		$this->value = $cluster->getName();

		return parent::onDisplay($cluster);
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$post, &$cluster)
	{
		return $this->processDateTitle($post, $cluster);
	}

	/**
	 * Executes before the event is save.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function processDateTitle(&$post, &$cluster)
	{
		$title = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';
		$format = $this->params->get('title_date_format');

		// Set the title on the event
		$model = ES::model('Clusters');

		if ($cluster->parent_id) {
			// we now this is a recurring event creation.
			// lets add a date into the title.
			if (isset($post['startDatetime']) && $post['startDatetime']) {

				$parentEvent = ES::event($cluster->parent_id);

				$startDate = $post['startDatetime'];

				// Get the correct event timezone. #2444
				// Joomla timezone
				$original_TZ = new DateTimeZone(JFactory::getConfig()->get('offset'));
				$eventTimezone = isset($post['startendTimezone']) ? $post['startendTimezone'] : false;

				// Get the date with timezone
				$newStartDate = JFactory::getDate($startDate, $original_TZ);

				// Check for timezone. If the timezone has been changed, get the new startend date
				if (!empty($eventTimezone) && $eventTimezone !== 'UTC') {
					$dtz = new DateTimeZone($eventTimezone);

					// Creates a new datetime string with user input timezone as predefined timezone
					$newStartDate = JFactory::getDate($startDate, $dtz);
				}

				$title = $parentEvent->title . ' - ' . $newStartDate->format($format);
			}
		}

		$cluster->title = $model->getUniqueTitle($title, SOCIAL_TYPE_EVENT);

		unset($post[$this->inputName]);
	}
}
