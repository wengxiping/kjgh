<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class TasksViewForm extends SocialAppsView
{
	/**
	 * Renders the milestone form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function display($uid = null, $docType = null)
	{
		$event = ES::event($uid);

		// Check if the viewer is allowed here.
		if (!$event->canViewItem() || !$event->canAccessTasks()) {
			return $this->redirect($event->getPermalink(false));
		}

		if (!$event->canCreateMilestones()) {
			return $this->redirect($event->getAppPermalink('tasks'));
		}

		// Get app params
		$params = $this->app->getParams();

		// Load the milestone
		$id = JRequest::getInt('milestoneId');
		$milestone = FD::table('Milestone');
		$milestone->load($id);

		if (!empty($milestone->id)) {
			ES::document()->title(JText::_('APP_EVENT_TASKS_TITLE_EDITING_MILESTONE'));
		} else {
			ES::document()->title(JText::_('APP_EVENT_TASKS_TITLE_CREATE_MILESTONE'));
		}

		$cancelLink = $event->getAppPermalink('tasks');

		// get the assignee
		$assignee = null;
		if ($milestone->user_id) {
			$assignee = ES::user($milestone->user_id);
		}

		$this->set('cluster', $event);
		$this->set('milestone', $milestone);
		$this->set('params', $params);
		$this->set('assignee', $assignee);
		$this->set('appId', $this->app->id);

		echo parent::display('themes:/site/tasks/form/default');
	}
}
