<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/views/views');

class EasySocialViewGroups extends EasySocialAdminView
{
	/**
	 * Displays a dialog confirmation before deleting a group category
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDeleteCategory()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/groups/dialogs/delete.category');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the new group creation form as we need the admin to select a category.
	 *
	 * @since	1.0
	 * @access	public
	 * @return
	 */
	public function createDialog()
	{
		$categoryList = ES::populateClustersCategories('category_id', false, array(), SOCIAL_TYPE_GROUP, 'data-input-category', false);

		$theme = ES::themes();

		$theme->set('categoryList', $categoryList);
		$contents = $theme->output('admin/groups/dialogs/create.group');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to delete a category avatar
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmRemoveCategoryAvatar()
	{

		$theme 	= FD::themes();
		$id		= JRequest::getInt( 'id' );

		$theme->set( 'id' , $id );
		$contents 	= $theme->output('admin/groups/dialogs/remove.category.avatar' );

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the owner switching form
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function switchOwner()
	{
		$theme	= FD::themes();

		$ids 	= JRequest::getVar( 'ids' );

		$theme->set( 'ids' , $ids );
		$contents 	= $theme->output('admin/groups/dialogs/browse.users' );

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the owner switching form
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmSwitchOwner()
	{
		$theme = ES::themes();

		$ids = JRequest::getVar('id');
		$userId = JRequest::getInt('userId');
		$newOwner = ES::user($userId);

		$theme->set('ids', $ids);
		$theme->set('user', $newOwner);
		$theme->set('clusterType', 'groups');

		$contents = $theme->output('admin/clusters/dialogs/switch.owner');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the new group creation form as we need the admin to select a category.
	 *
	 * @since	1.0
	 * @access	public
	 * @return
	 */
	public function deleteConfirmation()
	{
		$theme	= FD::themes();

		$contents 	= $theme->output('admin/groups/dialogs/delete.group' );

		return $this->ajax->resolve($contents);
	}

	/**
	 * Return the reformed data during save fields
	 *
	 * @since  1.1
	 * @access public
	 */
	public function saveFields( $data )
	{
		if( $data === false )
		{
			return FD::ajax()->reject( $this->getError() );
		}

		FD::ajax()->resolve( $data );
	}

	/**
	 * Allows caller to browse groups
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function browse()
	{
		$callback = JRequest::getVar('jscallback' , '');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/groups/dialogs/browse');

		return $this->ajax->resolve($content);
	}

	/**
	 * Allows caller to browse a category via the internal dialog system
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function browseCategory()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/groups/dialogs/browse.category');

		return $this->ajax->resolve($content);
	}

	/**
	 * Displays the reject dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function rejectGroup()
	{
		// Get the group ids that should be rejected
		$ids = $this->input->get('ids', array(), 'array');
		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/groups/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the approve dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function approveGroup()
	{

		// Get the group ids that should be rejected
		$ids 	= JRequest::getVar( 'ids' );
		$ids 	= FD::makeArray( $ids );

		$theme 	= FD::themes();
		$theme->set( 'ids' , $ids );
		$contents 	= $theme->output('admin/groups/dialogs/approve' );

		return $this->ajax->resolve($contents);
	}

	/**
	 * Show user listing to add users into the group
	 *
	 * @since  1.2
	 * @access public
	 */
	public function addMembers()
	{
		$clusterId = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterId', $clusterId);
		$contents = $theme->output('admin/groups/dialogs/browse.addusers');

		return $this->ajax->resolve($contents);
	}

	public function switchCategory()
	{
		$theme = FD::themes();

		$ids = $this->input->getVar('ids');

		$theme->set('ids', $ids);

		$categories = ES::model('GroupCategories')->getCategories(array('state' => SOCIAL_STATE_PUBLISHED, 'ordering' => 'ordering', 'excludeContainer' => true));

		$theme->set('categories', $categories);

		$contents = $theme->output('admin/groups/dialogs/switchCategory.browse');

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
