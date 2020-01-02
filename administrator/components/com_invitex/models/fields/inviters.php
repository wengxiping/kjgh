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
class JFormFieldInviters extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'inviters';

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
		$sql = "SELECT DISTINCT(u.id), u.name " . " FROM #__users AS u , #__invitex_imports AS ii " . " WHERE u.id=ii.inviter_id";
		$db->setQuery($sql);
		$inviters = $db->loadAssocList('id');

		$options = array();

		foreach ($inviters as $key => $value)
		{
			$options[] = JHtml::_('select.option', $key, $value['name']);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
