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

class MembersControllerGroups extends SocialAppsController
{
	/**
	 * Allows caller to filter members
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMembers()
	{
		$keyword = $this->input->get('keyword', '', 'default');

		// Get the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Get the current filter
		$filterType = $this->input->get('type', '', 'word');

		// Check whether the viewer can really view the contents
		if ((!$group->isOpen() && !$group->isMember()) || ($filterType == 'pending' && !$group->isMember()) && !$group->isAdmin()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION'));
		}

		$options = array();
		$emptyText = 'APP_GROUP_MEMBERS_EMPTY';

		// Get the pagination settings
		$limit = ES::getLimit('userslimit');

		// Members to display per page.
		$options['limit'] = $limit;

		// List only group admins
		if ($filterType == 'admin') {
			$options['admin'] = true;
		}

		// List only pending users
		if ($filterType == 'pending') {
			$options['state'] = SOCIAL_GROUPS_MEMBER_PENDING;
		}

		if ($filterType == 'invited') {
			$options['state'] = SOCIAL_GROUPS_MEMBER_INVITED;
		}

		if ($filterType == 'members') {
			$options['members'] = true;
			$options['state'] = true;
		}

		if (!empty($keyword)) {
			$options['search'] = $keyword;
			$emptyText = 'APP_GROUP_MEMBERS_EMPTY_SEARCH';
		}

		$model = ES::model('Groups');
		$users = $model->getMembers($group->id, $options);
		$pagination	= $model->getPagination();

		$pagination->setVar('view', 'groups');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $group->getAlias());
		$pagination->setVar('appId', $this->getApp()->getAlias() );
		$pagination->setVar('Itemid', ESR::getItemId('groups', 'item', $group->id));

		if ($pagination && $filterType && $filterType != 'all') {
			$pagination->setVar('filter', $filterType);
		}

		// Redirection url when an action is performed on a group member
		$redirectOptions = array('layout' => "item", 'id' => $group->getAlias(), 'appId' => $this->getApp()->getAlias());

		if ($filterType) {
			$redirectOptions['filter'] = $filterType;
		}

		$returnUrl = ESR::groups($redirectOptions, false);
		$returnUrl = base64_encode($returnUrl);

		// Load the contents
		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('pagination', $pagination);
		$theme->set('group', $group);
		$theme->set('users', $users);
		$theme->set('emptyText', $emptyText);

		$contents = $theme->output('apps/group/members/groups/wrapper');

		return $this->ajax->resolve($contents);
	}

}
