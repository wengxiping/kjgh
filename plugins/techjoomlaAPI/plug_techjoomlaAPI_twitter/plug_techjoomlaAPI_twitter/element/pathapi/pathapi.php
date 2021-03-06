<?php
 /**
	* @package JomSocial Network Suggest
	* @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link     http://www.techjoomla.com
	*/

	// Check to ensure this file is within the rest of the framework
	defined('JPATH_BASE') or die();

	jimport("joomla.html.parameter.element");
	jimport('joomla.html.html');
	jimport('joomla.form.formfield');

	$lang = JFactory::getLanguage();
	$lang->load('plug_techjoomlaAPI_twitter', JPATH_ADMINISTRATOR);
	if(JVERSION>=1.6)
	{
			class JFormFieldPathapi extends JFormField
			{
				/**
				 * The form field type.
				 *
				 * @var		string
				 * @since	1.6
				 */
				public $type = 'Pathapi';

				/**
				 * Method to get the field input markup.
				 *
				 * TODO: Add access check.
				 *
				 * @return	string	The field input markup.
				 * @since	1.6
				 */

				protected function getInput()
				{

					if($this->id=='jform_params_pathapi_twitter')
						$return	='<div class="instructions">
								Go to <a href="http://techjoomla.com/documentation-for-invitex/configuring-twitter-api-plugin.html" target="_blank">How to configure Techjoomla-Twitter API</a><br />
								</div>';
						return $return;

				} //function

			}
	}
	else
	{
		class JElementPathapi extends JElement
		{

				var $_name = 'pathapi';
				function fetchElement($name, $value, &$node, $control_name)
				{
					$return	='<div class="instructions">
								Go to <a href="http://techjoomla.com/documentation-for-invitex/configuring-twitter-api-plugin.html" target="_blank">How to configure Techjoomla-Twitter API</a><br />
								</div>';
						return $return;

				}//function
		}//class

	}


