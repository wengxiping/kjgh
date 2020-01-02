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

class PayplansViewLanguages extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('languages');
	}

	public function display($tpl = null)
	{
		$this->heading('Languages');

		JToolbarHelper::custom('languages.discover' , 'refresh' , '' , JText::_('Discover') , false);
		JToolbarHelper::custom('languages.purge' , 'purge' , '' , JText::_('Purge'), false);
		JToolbarHelper::custom('languages.install', 'upload' , '' , JText::_('Install'));
		JToolbarHelper::custom('languages.uninstall', 'remove' , '' , JText::_('Uninstall'));

		$key = $this->config->get('main_apikey');

		$model = PP::model('Languages');
		$initialized = $model->initialized();

		if (!$initialized) {
			$this->set('key', $key);

			return parent::display('languages/initialize');
		}

		// Get languages
		$result = $model->getLanguages();
		$languages = array();

		foreach ($result as $row) {
			$language = PP::table('Language');
			$language->bind($row);

			$languages[] = $language;
		}

		$this->set("languages", $languages);

		parent::display('languages/default');
	}
}