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

class PayplansViewLog extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('logs');
	}

	public function display($tpl = null)
	{
		$this->heading('Logs');

		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_CONFIRMATION'), 'log.remove');
		JToolbarHelper::custom('log.purge', '', '', JText::_('COM_PP_PURGE_ALL'), false);

		$view = $this->input->get('view', '', 'cmd');
		$renderFilterBar = true;

		// Only render the filter bar in the log listing page.
		if ($view !== 'log') {
			$renderFilterBar = false;
		}

		$model = PP::model('Log');
		$model->initStates();

		$logs = $model->getItems();

		$states = $this->getStates(array('search', 'dateRange', 'level', 'class', 'user_ip', 'username', 'object_id', 'created_date', 'ordering', 'direction', 'limit'));
		$pagination = $model->getPagination();

		$this->set('editable', true);
		$this->set('form', true);
		$this->set('sortable', true);
		$this->set('states', $states);
		$this->set('logs', $logs);
		$this->set('pagination', $pagination);
		$this->set('renderFilterBar', $renderFilterBar);

		// when accessing from logs page, we always allow to perform actions.
		$this->set('editable', true);

		return parent::display('logs/default/default');
	}

	/**
	 * Screen to notify users about fixing legacy log files
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function fixLegacy()
	{
		$this->heading('Legacy Log Files');

		JToolbarHelper::custom('fix', '', '', JText::_('Run Maintenance'), false);

		$files = PP::log()->getLegacyLFiles();

		$this->set('files', $files);

		return parent::display('logs/maintenance/default');
	}

	/**
	 * Renders the payment notifications received by PayPlans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function payments()
	{
		$this->heading('Payment Notifications');

		JToolbarHelper::custom('log.purgeIpn', '', '', JText::_('COM_PP_PURGE_ITEMS'), false);

		$model = PP::model('Log');
		$model->initStates();

		$items = $model->getPaymentNotifications();
		$pagination = $model->getPagination();

		$states = $this->getStates(array('search', 'dateRange', 'app_id', 'ordering', 'direction', 'limit'));

		$this->set('states', $states);
		$this->set('pagination', $pagination);
		$this->set('items', $items);

		return parent::display('logs/payments/default');	
	}

	/**
	 * Renders the log export layout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function export($tpl = null)
	{
		// redirect back to logs main page.
		$this->app->redirect('index.php?option=com_payplans&view=log');


		// $this->heading('Export Logs');

		// JToolbarHelper::custom('export', '', '', JText::_('Export'), false);

		// parent::display('logs/export/default');
		// return true;
	}
}
