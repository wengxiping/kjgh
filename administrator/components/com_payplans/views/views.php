<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018. Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/views');

abstract class PayPlansAdminView extends PayPlansView
{
	protected $page = null;

	public function __construct($config = array())
	{
		// Initialize page.
		$page = new stdClass();

		// Initialize page values.
		$page->heading = '';
		$page->description = '';

		$this->page = $page;
		$this->my = JFactory::getUser();
		$this->showSidebar = true;
		$this->theme = PP::themes();

		$view = $this->getName();

		// // Disallow access if user does not have sufficient permissions
		// $rule = 'payplans.access.' . $view;

		// if (!$this->authorise($rule)) {
		// 	$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		// }

		parent::__construct($config);
	}

	/**
	 * Checks for user access
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function authorise($command, $extension = 'com_payplans')
	{
		return $this->my->authorise($command, $extension);
	}

	/**
	 * Checks if the current viewer can really access this section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function checkAccess($rule)
	{
		$rule = 'payplans.' . $rule;

		if (!$this->my->authorise($rule , 'com_payplans')) {
			PP::info()->set(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return $this->app->redirect('index.php?option=com_payplans');
		}
	}

	/**
	 * Checks if the current viewer can really access this section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function hasAccess($rule)
	{
		$rule = 'payplans.' . $rule;

		if (!$this->my->authorise($rule , 'com_payplans')) {
			return false;
		}
		return true;
	}

	/**
	 * Central method that is called by child items to display the output.
	 * All views that inherit from this class should use display to output the html codes.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$format = $this->doc->getType();
		$tmpl = $this->input->get('tmpl', '', 'string');

		// Joomla page title should always display PayPlans
		JToolbarHelper::title(JText::_('COM_PP_TITLE'), 'payplans');

		if ($format == 'html') {

			// Initialize the necessary css / js
			JHTML::_('behavior.framework');
			PP::initialize('admin');

			$config = PP::config();

			$theme = PP::themes();

			$class = '';

			if ($tmpl == 'component') {
				$class = 'pp-window';
			}

			// Main wrapper
			$class = isset($class) ? $class : '';

			// Add the sidebar to the page obj.
			$sidebar = $this->getSideBar();

			// temp fix on styleguide to hide sidebar.
			$isStyleGuide = false;

			if ($this->input->get('view') == 'styleguide') {
				$isStyleGuide = true;
			}

			$result = $this->triggerPlugins('onPayplansViewBeforeExecute');
			// Capture contents.
			ob_start();
			parent::display('admin/' . $tpl);
			$html = ob_get_contents();
			ob_end_clean();

			$version = PP::getLocalVersion();

			$theme->set('showSidebar', $this->showSidebar);
			$theme->set('class', $class);
			$theme->set('version', $version);
			$theme->set('tmpl', $tmpl);
			$theme->set('contents', $html);
			$theme->set('sidebar', $sidebar);
			$theme->set('isStyleguide', $isStyleGuide);
			$theme->set('page', $this->page);
			$theme->set('customAction', $this->getCustomAction());

			$contents = $theme->output('admin/structure/default');

			echo $contents;

			return;
		}

		return parent::display($tpl);
	}

	/**
	 * Get custom action
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCustomAction()
	{
		if (isset($this->page->customAction) && $this->page->customAction) {
			return $this->page->customAction;
		}
	}

	/**
	 * This is only used for the model on the back end to retrieve available states
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getStates($availableStates = array(), $model = null)
	{
		if (is_null($model)) {
			$model = PP::model($this->getName());
		}

		$states = new stdClass();

		foreach ($availableStates as $state) {
			$states->$state = $model->getState($state);
		}

		return $states;
	}

	/**
	 * Allows caller to set the header title in the structure layout.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function heading($title, $description = '')
	{
		$title = str_replace(' ', '_', $title);
		$title = 'COM_PP_HEADING_' . strtoupper($title);
		$desc = $title . '_DESC';

		$this->page->heading = JText::_($title);
		$this->page->description = JText::_($desc);
	}

	/**
	 * Set custom action to be display on the header
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setCustomAction($html)
	{
		$this->page->customAction = $html;
	}

	/**
	 * Returns the sidebar html codes.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getSideBar()
	{
		$showSidebar = $this->input->get('sidebar', 1);
		$showSidebar = $showSidebar == 1 ? true : false;

		if (!$showSidebar) {
			return;
		}

		$view = $this->getName();
		$layout = $this->getLayout();

		$sidebar = PP::sidebar();
		$output = $sidebar->render($view, $layout);

		return $output;
	}

}
