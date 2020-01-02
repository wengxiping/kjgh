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

class PayplansViewApp extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('apps');
	}

	public function display($tpl = null)
	{
		$this->heading('Apps');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('app.publish');
		JToolbarHelper::unpublish('app.unpublish');
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_APPS'), 'app.delete');

		$model = PP::model('App');
		$model->initStates();

		$apps = $model->getItems();
		$pagination = $model->getPagination();

		$states = $this->getStates(array('search', 'type', 'published', 'limit', 'ordering', 'direction'), $model);

		$this->set('pagination', $model->getPagination());
		$this->set('apps', $apps);
		$this->set('states', $states);

		return parent::display('app/default/default');
	}

	/**
	 * Unique form to create apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function create()
	{
		$this->heading('CREATE_NEW_APPS');

		$element = $this->input->get('element', '', 'default');

		// Get a list of available apps
		$model = PP::model('App');
		$apps = $model->getApps();

		if (!$element) {

			$this->set('view', 'app');
			$this->set('layout', 'create');
			$this->set('apps', $apps);

			return parent::display('app/create/default');
		}

		// Simulate editing app, since this is a new app
		JToolbarHelper::apply('app.apply');
		JToolbarHelper::save('app.save');
		JToolbarHelper::cancel('app.cancel');

		$model = PP::model('App');
		$path = $model->getAppManifestPath($element);

		$form = PP::form('apps');
		$form->load($path, new JRegistry());

		$app = PP::app();

		// since it only render out the language constants, user might be not understand what is that string value for the title and description
		// $app->loadDefaultManifest($element);

		$activeTab = $this->input->get('activeTab', '', 'word');

		$this->set('controller', 'app');
		$this->set('element', $element);
		$this->set('form', $form);
		$this->set('activeTab', $activeTab);
		$this->set('app', $app);

		parent::display('app/form/default');
	}

	/**
	 * Renders the form to edit the payment method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$this->heading('EDIT_APPS');

		JToolbarHelper::apply('app.apply');
		JToolbarHelper::save('app.save');

		JToolbarHelper::cancel('app.cancel');

		$app = PP::app($id);
		$form = $app->getForm();

		$activeTab = $this->input->get('activeTab', '', 'word');

		$this->set('controller', 'app');
		$this->set('form', $form);
		$this->set('activeTab', $activeTab);
		$this->set('app', $app);

		return parent::display('app/form/default');
	}

	/**
	 * Used internally for building the list of payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function buildList()
	{
		$path = JPATH_PLUGINS . '/payplans';

		$folders = JFolder::folders($path, '.', false, true);
		$types = array();
		$payments = array();
		$apps = array();

		foreach ($folders as $folder) {
			$pluginXml = $folder . '/' . basename($folder) . '.xml';
			$xml = $folder . '/' . basename($folder) . '/app/' . basename($folder) . '/' . basename($folder) . '.xml';

			if (!JFile::exists($pluginXml)) {
				continue;
			}

			if (!JFile::exists($xml)) {
				continue;
			}

			$pluginParser = simplexml_load_file($pluginXml);
			$parser = simplexml_load_file($xml);
			$type = (string) $parser->tags;

			$type = strtolower($type);

			$name = (string) $parser->name;

			if (!$type) {
				$app = new stdClass();
				$app->name = (string) $parser->name;
				$app->element = str_replace('pp-', '', strtolower((string) $parser->alias));
				$app->description = (string) $parser->description;
				$app->documentation = 'https://stackideas.com/docs/payplans/apps/' . $app->element;

				$files = $pluginParser->files->children();

				foreach ($files as $file) {
					foreach ($file->attributes() as $attr) {
						$app->element = (string) $attr;
					}
				}
				// dump($pluginParser->files->children());
				// $app->help = (string) $parser->help;

				if ($app->element == 'tpg') {
					continue;
				}
				$apps[] = $app;
			}

			// if ($type == 'payment' || $type == 'payment gateway' || $type == 'payunity') {


			// }
			// $types[] = $type;
		}

		echo json_encode($apps);
		exit;
		
		dump($apps, $types);
	}
}