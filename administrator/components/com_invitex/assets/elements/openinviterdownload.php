<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Open inviter download filed
 *
 * @package     Invitex
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldOpeninviterdownload extends JFormField
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
		$this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
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
		$oi_path = JPATH_SITE . '/components/com_invitex/openinviter/openinviter.php';
		$document = JFactory::getDocument();

		if (!file_exists($oi_path))
		{
			$params = JComponentHelper::getParams('com_invitex');
			$private_key_cronjob = $params->get('private_key_cronjob');
			$download_link = "<a href='http://openinviter.com/'>" . JText::_('INV_OI_DOWNLOAD_LINK') . "</a>";

			echo "<span id='openinviter_download_link'><b>" . JText::sprintf('INV_OI_NOTICE', $download_link) . "</b></span>";

			$script = 'jQuery(document).ready(function(){
				jQuery("#openinviter_download_link").parent().removeClass("controls");
				jQuery("#jform_oi_download-lbl").parent().parent().removeClass("controls");
				jQuery("#jform_oi_download-lbl").addClass("etwet");
				jQuery("#jform_oi_download-lbl").parent().removeClass("control-label");
				jQuery("#jform_oi_update-lbl").hide();
				jQuery("#jform_oi_update_cron-lbl").hide();
				jQuery("#jform_selectionssocial-lbl").hide();
				jQuery("#jform_selectionsemail-lbl").hide();
			});
			';

			$document->addScriptDeclaration($script);
		}
	}
}
