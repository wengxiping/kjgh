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

class EasySocialViewEvents extends EasySocialAdminView
{
	/**
	 * Renders the create event dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function createDialog()
	{
		$categoryList = ES::populateClustersCategories('category_id', false, array(), SOCIAL_TYPE_EVENT, 'data-input-category', false);

		$theme = ES::themes();
		$theme->set('categoryList', $categoryList);

		$contents = $theme->output('admin/events/dialogs/create');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the delete confirmation dialog
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function deleteDialog()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/events/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the user listings browser for admin to choose a new owner for an event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function switchOwner()
	{
		$ids = $this->input->get('ids', '', 'var');

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/events/dialogs/browse.users');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the dialog to confirm removal of category avatar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmRemoveCategoryAvatar()
	{
		$id = $this->input->get('id', 0, 'int');
		
		$theme = ES::themes();
		$theme->set('id', $id);
		$contents = $theme->output('admin/clusters/dialogs/remove.category.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the switch event owner dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmSwitchOwner()
	{
		$userid = $this->input->getInt('userId');
		$user = ES::user($userid);
		$ids = $this->input->get('ids', '', 'var');

		$theme = ES::themes();

		$theme->set('user', $user);
		$theme->set('ids', $ids);
		$theme->set('clusterType', 'events');

		$contents = $theme->output('admin/clusters/dialogs/switch.owner');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the invite guests dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function inviteGuests()
	{
		$clusterId = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterId', $clusterId);
		$contents = $theme->output('admin/events/dialogs/invite.guests');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the delete event category dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteCategoryDialog()
	{
		$theme = FD::themes();

		$contents = $theme->output('admin/events/dialogs/delete.category');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after an event avatar has been removed
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function removeCategoryAvatar()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the browse events dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback');
		$multiple = $this->input->get('multiple', true, 'bool');

		$theme = ES::themes();
		$theme->set('multiple', $multiple);
		$theme->set('callback', $callback);
		$content = $theme->output('admin/events/dialogs/browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Browses for category
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function browseCategory()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/events/dialogs/browse.category');

		return $this->ajax->resolve($content);
	}

	/**
	 * Renders the switch category form for event
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function switchCategory()
	{
		$theme = ES::themes();

		$ids = $this->input->get('ids', '', 'var');

		$model = ES::model('EventCategories');
		$categories = $model->getCategories(array('state' => SOCIAL_STATE_PUBLISHED, 'ordering' => 'ordering', 'excludeContainer' => true));

		$theme->set('ids', $ids);
		$theme->set('categories', $categories);

		$contents = $theme->output('admin/events/dialogs/switch.category');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after applying recurring dialog
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function applyRecurringDialog()
	{
		$theme = ES::themes();

		$contents = $theme->output('admin/events/dialogs/apply.recurring');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after saving the event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function store()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after creating recurring events
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function createRecurring()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the reject dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function rejectEvent()
	{
		// Get the page ids that should be rejected
		$ids = $this->input->get('ids');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/events/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the approve dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function approveEvent()
	{
		// Get the page ids that should be rejected
		$ids = $this->input->get('ids');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/events/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	public function createBlankCategory($data)
	{
		if ($data === false) {
			return $this->ajax->reject($this->getError());
		}

		$this->ajax->resolve($data);
	}
}
