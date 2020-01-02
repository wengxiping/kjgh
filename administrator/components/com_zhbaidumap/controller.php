<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * Zh BaiduMap Component Controller
 */
class ZhBaiduMapController extends JControllerLegacy
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) 
	{

		$view   = $this->input->get('view', 'ZhBaiduMaps');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');


		JRequest::setVar('view', $view);


		// call parent behavior
		parent::display($cachable, $urlparams);
		
		// Set the submenu
		ZhBaiduMapHelper::addSubmenu($view);

		return $this;
		
	}
}
