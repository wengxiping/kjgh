<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldProviders extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'providers';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$sql = "SELECT DISTINCT(provider_email) " . " FROM #__invitex_imports " . " WHERE provider_email<>'' ";

		$db->setQuery($sql);
		$providers = $db->loadAssocList('provider_email');

		$options = array();

		foreach ($providers as $key => $value)
		{
			$provider_email = "";
			$provider_email = strtolower($value['provider_email']);
			$provider_email = str_replace("plug_techjoomlaapi_", "", $provider_email);
			$provider_email = str_replace("send_", "", $provider_email);
			$provider_email = ucwords($provider_email);

			if ($provider_email == "Js_messaging")
			{
				$provider_email = JText::_('COM_INVITEX_MESSAGING');
			}

			$options[] = JHtml::_('select.option', $key, $provider_email);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
