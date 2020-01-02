<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

/**
 * Integration filed
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldIntegrations_Point extends JFormField
{
	/**
	 * Function to get input
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
	}

	/**
	 * Function to get element
	 *
	 * @param   STRING  $name          names
	 * @param   STRING  $value         value
	 * @param   STRING  &$node         node
	 * @param   STRING  $control_name  control name
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function fetchElement($name,$value,&$node,$control_name)
	{
		$communityfolder = JPATH_SITE . '/components/com_community';
		$cbfolder = JPATH_SITE . '/components/com_comprofiler';
		$esfolder = JPATH_SITE . '/components/com_easysocial';
		$jwfolder = JPATH_SITE . '/components/com_awdwall';
		$alphafolder = JPATH_SITE . '/components/com_alphauserpoints';
		$vmfolder = JPATH_SITE . '/components/com_virtuemart';
		$payplansfolder = JPATH_SITE . '/components/com_payplans';
	}
}
