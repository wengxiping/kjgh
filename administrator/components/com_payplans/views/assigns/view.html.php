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
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Assigns');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('assigns.publish');
		JToolbarHelper::unpublish('assigns.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'assigns.delete');

		$model = PP::model('App');
		$model->initStates();

		$assigns = $model->getAppInstances(array('type' => 'profilebasedplan'));
		$pagination = $model->getPagination();

		foreach ($assigns as $assign) {
			$params = new JRegistry($assign->app_params);

			$assign->source = $params->get('source');

			$assign->plans = array();
			$plans = $params->get('signup_plans', false);

			if ($plans) {
				foreach ($plans as $planId) {
					$plan = PP::plan($planId);
					$assign->plans[] = $plan;
				}
			}
		}

		// dump($assigns);

		$this->set('pagination', $pagination);
		$this->set('assigns', $assigns);

		parent::display('assigns/default/default');
	}

	/**
	 * Renders the modifier form
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function form()
	{
		$this->heading('New Plan Assign');

		JToolbarHelper::apply('assigns.apply');
		JToolbarHelper::save('assigns.save');
		JToolbarHelper::save2new('assigns.saveNew');
		JToolbarHelper::cancel('assigns.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		// Load the app instance
		$app = PP::app($id);

		$selectedProfile = array();
		$profileSource = $this->config->get('profile_used');
		$signupPlans = array();

		if ($app->getId()) {
			$this->heading('Editing Plan Assign');

			// Get the app params
			$appParams = $app->getAppParams();
			$selectedProfile = $appParams->get('profile_type');
			$profileSource = $appParams->get('source', $profileSource);
			$signupPlans = $appParams->get('signup_plans');
		}

		$profileTypes = $this->getProfileTypes($profileSource);

		$esEnabled = PP::easysocial()->exists() ? true : false;
		$communityEnabled = JComponentHelper::isEnabled('com_community') ? true : false;

		$this->set('activeTab', $activeTab);
		$this->set('app', $app);
		$this->set('profileTypes', $profileTypes);
		$this->set('selectedProfile', $selectedProfile);
		$this->set('profileSource', $profileSource);
		$this->set('esEnabled', $esEnabled);
		$this->set('communityEnabled', $communityEnabled);
		$this->set('signupPlans', $signupPlans);

		parent::display('assigns/form/default');
	}

	/**
	 * Retrieve profile types based on the source
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getProfileTypes($source)
	{
		$profileTypes = array();

		if ($source == 'joomla_usertype') {
			$model = PP::model('User');
			$profileTypes = $model->getAllUserGroups();
		}
		
		if ($source == 'easysocial_profiletype') {
			$lib = PP::easysocial();

			if (!$lib->exists()) {
				return JText::_('COM_PAYPLANS_PLEASE_INSTALL_EASYSOCIAL_BEFORE_USING_THIS_APPLICATION');
			}

			$profileTypes = $lib->getProfileTypes();
		}

		if ($source == 'jomsocial_profiletype') {
			$db = PP::db();
			$query = 'SELECT `id`, `name` as title FROM ' . $db->qn('#__community_profiles');
			
			$db->setQuery($query);
			$profileTypes = $db->loadObjectList();
		}

		return $profileTypes;
	}
}
