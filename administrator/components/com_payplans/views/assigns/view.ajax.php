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

class PayPlansViewAssigns extends PayPlansAdminView
{
	/**
	 * Triggers via ajax calls to retrieve profiletypes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderProfileDropdown()
	{
		$source = $this->input->get('source', 'joomla_usertype', 'string');

		if ($source == 'joomla_usertype') {
			$model = PP::model('User');
			$profileTypes = $model->getAllUserGroups();
		}
		
		if ($source == 'easysocial_profiletype') {
			$lib = PP::easysocial();

			if (!$lib->exists()) {
				return $this->reject(JText::_('COM_PP_EASYSOCIAL_NOT_INSTALLED'));
			}

			$profileTypes = $lib->getProfileTypes();
		}

		if ($source == 'jomsocial_profiletype') {
			$db = PP::db();
			$query = 'SELECT `id`, `name` as title FROM ' . $db->qn('#__community_profiles');

			$db->setQuery($query);
			$profileTypes = $db->loadObjectList();
		}

		$themes = PP::themes();
		$themes->set('profileTypes', $profileTypes);
		$themes->set('selectedProfile', array());

		$profileTypesHtml = $themes->output('admin/assigns/form/profiletypes');

		return $this->resolve($profileTypesHtml);
	}
}
