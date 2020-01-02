<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	controller.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */

// No direct access
defined('_JEXEC') or die;

class JblanceController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false){
		require_once JPATH_COMPONENT.'/helpers/jblance.php';
		require_once JPATH_COMPONENT.'/helpers/link.php';
		$app  = JFactory::getApplication();

		$view		= $app->input->get('view', 'admproject', 'string');
        $app->input->set('view', $view);
		
        $layout		= $app->input->get('layout', 'dashboard', 'string');
        $app->input->set('layout', $layout);

		parent::display();
		return $this;
	}
}
