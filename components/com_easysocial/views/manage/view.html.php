<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewManage extends EasySocialSiteView
{
	/**
	 * Renders the cluster moderation layout
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function clusters($tpl = null)
	{
		ES::requireLogin();
		ES::checkCompleteProfile();
		ES::setMeta();

		if (!$this->config->get('pages.enabled') && !$this->config->get('events.enabled') && !$this->config->get('groups.enabled')) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Ensure that the user's acl is allowed to manage pending items
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('pendings.manage')) {
			$this->setMessage(JText::_('COM_ES_MANAGE_NOT_ALLOWED_TO_VIEW'), ES_ERROR);
			$this->info->set($this->getMessage());

			return $this->redirect(ESR::dashboard(array(), false));
		}

		$filter = $this->input->get('filter', 'event', 'cmd');
		$options = array('filter' => $filter);

		$model = ES::model('Clusters');
		$clusters = $model->getPendingModeration($options);

		$pendingCounters = array();
		$pendingCounters['event'] = $model->getTotalPendingModeration(array('filter' => 'event'));
		$pendingCounters['group'] = $model->getTotalPendingModeration(array('filter' => 'group'));
		$pendingCounters['page'] = $model->getTotalPendingModeration(array('filter' => 'page'));

		// Get pagination
		$pagination	= $model->getPagination();

		// Set additional params for the pagination links
		$pagination->setVar('view', 'manage');
		$pagination->setVar('layout', 'clusters');

		// Set page attributes
		$this->page->title('COM_ES_PAGE_TITLE_ITEMS_MODERATION');
		$this->page->breadcrumb('COM_ES_PAGE_TITLE_ITEMS_MODERATION');

		$this->set('clusters', $clusters);
		$this->set('filter', $filter);
		$this->set('pagination', $pagination);
		$this->set('pendingCounters', $pendingCounters);

		return parent::display('site/manage/clusters/default');
	}
}
