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

PP::import('admin:/includes/views');

abstract class PayPlansSiteView extends PayPlansView
{
	protected $page = null;
	protected $toolbar = false;

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

		parent::__construct($config);
	}

	/**
	 * Central method that is called by child items to display the output.
	 * All views that inherit from this class should use display to output the html codes.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$type = $this->doc->getType();
		$show = $this->input->get('show', '', 'string');

		// Get the current view.
		$view = $this->input->get('view', '', 'cmd');
		$view = !empty($view) ? ' view-' . $view : '';

		// Get the current task
		$task = $this->input->get('task', '', 'cmd');
		$task = !empty($task) ? ' task-' . $task : '';

		// Set vary user-agent
		JResponse::setHeader('Vary', 'User-Agent', true);

		PP::initialize('site');

		// Render the custom styles
		if ($type == 'html') {
			$theme = PP::themes();
			$customCss = $theme->output('site/structure/css');

			// Compress custom css
			$customCss = PP::minifyCSS($customCss);

			$this->doc->addCustomTag($customCss);
		}

		// Include main structure here.
		$theme = PP::themes();
		$config = PP::config();

		// Maybe we can change the trigger name to 'onPayplansViewBeforeExecute' instead.
		// Before rendering the output, trigger plugins
		// for 'onPayplansViewBeforeRender', the execution moved to controller
		// see PayPlansController::display()

		$args = array(&$this, &$task);
		PPEvent::trigger('onPayplansViewBeforeRender', $args, '', $this);

		// Do not allow zooming on mobile devices
		if ($theme->isMobile()) {
			$this->doc->setMetaData('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
		}

		// Capture output.
		ob_start();
		parent::display($tpl);
		$contents = ob_get_contents();
		ob_end_clean();

		// Trigger apps to allow them to attach html output on the page too.
		// $dispatcher = PP::dispatcher();
		// $dispatcher->trigger('user', 'onComponentOutput', array(&$contents));

		// Get the menu's suffix
		$suffix = $this->getMenuSuffix();

		// Get any "id" or "cid" from the request.
		$object = $this->input->get('id', $this->input->get('cid', 0, 'int'), 'int');
		$object = !empty($object) ? ' object-' . $object : '';

		// Get any layout
		$layout = $this->input->get('layout', '', 'cmd');
		$layout = !empty($layout) ? ' layout-' . $layout : '';

		// Determines if the layout is a full page layout
		$fullPage = $this->input->get('tmpl', '', 'word');
		$fullPage = $fullPage == 'component' ? true : false;

		$theme->set('fullPage', $fullPage);
		$theme->set('suffix', $suffix);
		$theme->set('layout', $layout);
		$theme->set('object', $object);
		$theme->set('task', $task);
		$theme->set('view', $view);
		$theme->set('show', $show);
		$theme->set('contents', $contents);

		// Component template scripts
		$page = PP::document();
		$scripts = '<script>' . implode('</script><script>', $page->inlineScripts) . '</script>';
		$theme->set('scripts', $scripts);

		// Ensure component template scripts don't get added to the head.
		$page->inlineScripts = array();

		if ($fullPage) {
			$output = $theme->output('site/structure/full');
		} else {
			$toolbar = $this->hasToolbar();

			$theme->set('toolbar', $toolbar);

			$output = $theme->output('site/structure/default');
		}

		$args = array(&$this, &$task, &$output);
		PPEvent::trigger('onPayplansViewAfterRender', $args, '', $this);

		echo $output;
	}

	/**
	 * Generic 404 error page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function error()
	{
		return JError::raiseError(404, JText::_('Page is not available currently'));
	}

	/**
	 * Retrieve the menu suffix for a page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMenuSuffix()
	{
		$menu 	= $this->app->getMenu()->getActive();
		$suffix	= '';

		if ($menu) {
			$suffix = $menu->params->get('pageclass_sfx', '');
		}

		return $suffix;
	}

	/**
	 * Determines if the toolbar should be rendered
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasToolbar()
	{
		if (!$this->config->get('layout_toolbar')) {
			return false;
		}

		return $this->toolbar;
	}

	/**
	 * Allows overriden objects to redirect the current request only when in html mode.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirect($uri, $message = '', $class = '')
	{
		return $this->app->redirect($uri, $message, $class);
	}
}
