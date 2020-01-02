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

class PayplansControllerConfig extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('config');
	}

	/**
	 * Allows caller to save the settings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$model = PP::model('Config');
		$post = $this->input->post->getArray(array());
		$page = $this->input->get('page', '', 'word');
		$activeTab = $this->input->get('activeTab', '', 'default');
		$task = $post['task'];

		unset($post['activeTab']);
		unset($post['page']);
		unset($post['task']);
		unset($post['option']);
		unset($post['controller']);
		unset($post[PP::token()]);

		$companyLogo = $this->input->files->get('companyLogo', '', array());

		if (isset($companyLogo['tmp_name']) && !$companyLogo['error']) {
			$post['companyLogo'] = $model->updateCompanyLogo($companyLogo);
		}

		if ($page == 'system' && (!isset($post['blockLogging']) || !$post['blockLogging'])) {
			$post['blockLogging'] = '';
		}

		// Retrieve the previous configuration setting data
		$prevConfig = PP::config();

		// Only get the main function from the task
		$task = explode('.', $task);

		$args = array($prevConfig, $task[1]);
		$beforeExecuteResult = PPEvent::trigger('onPayplansControllerBeforeExecute', $args);

		$state = $model->save($post);

		// Only store this log data if stored it successfully
		if ($state) {
			$args = array($prevConfig, $task[1]);
			$afterExecuteResult = PPEvent::trigger('onPayplansControllerAfterExecute', $args);		
		}

		$message = $state ? JText::_('COM_PP_CONFIG_STORE_SUCCESS') : JText::_('COM_PP_CONFIG_STORE_ERROR');
		$type = $state ? 'success' : 'error';

		// Set info
		$this->info->set($message, $type);

		// Clear the component's cache
		$cache = JFactory::getCache('com_payplans');
		$cache->clean();

		$extended = '';

		if ($activeTab) {
			$extended .= '&activeTab=' . $activeTab;
		}

		return $this->redirectToView('config', $page, $extended);
	}

	/**
	 * Allow user to remove the company logo
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeLogo()
	{
		$model = PP::model('Config');
		$state = $model->removeCompanyLogo();

		$message = JText::_('Company logo deleted successfully');

		if (!$state) {
			$message = JText::_('There was an error removing the company logo');
		}

		$this->info->set($message, $state ? 'success' : 'error');

		$this->redirectToView('config', 'invoices');
	}	
}