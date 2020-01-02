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

class PayPlansViewConfig extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		// Check for access
		$this->checkAccess('config');

		JToolBarHelper::apply('config.save');

		$this->heading('Configuration');
	
		$allowed = array('discounts', 'general', 'invoices', 'plans', 'layout', 'system');		
		$page = $this->input->get('layout', 'general', 'word');

		if (!in_array($page, $allowed)) {
			return PP::redirect('index.php?option=com_payplans&view=config&layout=general');
		}

		$activeTab = $this->input->get('activeTab', '', 'default');

		$tabs = $this->getTabs($page, $activeTab);

		$this->set('activeTab', $activeTab);
		$this->set('page', $page);
		$this->set('tabs', $tabs);

		parent::display('settings/default');
	}

	/**
	 * Retrieves the tabs
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTabs($page, $activeTab)
	{
		$path = PP_ADMIN . '/themes/default/settings/pages/' . $page;
		$files = JFolder::files($path, '.php');


		$tabs = array();

		// Get the current active tab
		$active = $this->input->get('activeTab', '', 'cmd');

		foreach ($files as $file) {

			$fileName = $file;
			$file = str_ireplace('.php', '', $file);

			$tab = new stdClass();
			$tab->id = str_ireplace(array(' ', '.', '#', '_'), '-', strtolower($file));
			$tab->title = JText::_('COM_PP_CONFIG_' . strtoupper($file));
			$tab->file = $path . '/' . $fileName;
			$tab->active = ($file == 'general' && !$active) || $active === $tab->id;

			// Get the contents of the tab now
			$theme = PP::themes();
			$namespace = 'admin/settings/pages/' . strtolower($page) . '/' . $file;

			// Only for system setting
			if ($page == 'system') {

				$ignoreLogTypes = $this->config->get('blockLogging', '');

				if ($ignoreLogTypes) {
					// convert to array
					$ignoreLogTypes = json_decode($ignoreLogTypes);
				}
				
				$theme->set('ignoreLogTypes', $ignoreLogTypes);
			}

			$tab->contents = $theme->output($namespace);

			$tabs[$tab->id] = $tab;
		}

		// Sort items manually. Always place "General" as the first item
		if (isset($tabs['general'])) {
		
			$general = $tabs['general'];

			unset($tabs['general']);

			array_unshift($tabs, $general);
		} else {
			// First tab should always be highlighted
			$firstIndex = array_keys($tabs);
			$firstIndex = $firstIndex[0];

			if ($active) {
				$tabs[$firstIndex]->active = $active === $tabs[$firstIndex]->id;
			} else {
				$tabs[$firstIndex]->active = true;
			}
		}

		return $tabs;
	}
}

