<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Cron header
 *
 * @since  1.6
 */
class JFormFieldHeader extends JFormField
{
	public $type = 'Header';

	/**
	 * Function to get input
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
	public function getInput()
	{
		$document = JFactory::getDocument();

		if ($this->class == 'show_notes')
		{
			$return = '<div class="alert alert-info">
							' . JText::_($this->value) . '
						</div>';

			return $return;
		}
		else
		{
			$document->addStyleSheet(JUri::base() . 'components/com_invitex/css/invitex.css');
			$return = '<div class="invitex_header_div_outer">
							<div class="invitex_header_div_inner">
								' . JText::_($this->value) . '
							</div>
						</div>';

			return $return;
		}
	}
}
