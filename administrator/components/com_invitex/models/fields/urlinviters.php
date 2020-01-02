<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JFormHelper::loadFieldClass('list');

/**
 * List of Url inviters
 *
 * @since  3.0.7
 */
class JFormFieldUrlInviters extends JFormFieldList
{
	protected $type = 'urlinviters';

	/**
	 * Method to get a list of inviters
	 *
	 * @return  array An array of JHtml options.
	 *
	 * @since   3.0.7
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT' . $db->quoteName('u.name'));
		$query->select($db->quoteName('iis.inviter_id'));
		$query->from($db->quoteName('#__users', 'u'));
		$query->join('INNER', $db->quoteName('#__invitex_invite_success', 'iis') .
		' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('iis.inviter_id') . ')');
		$db->setQuery($query);

		$inviters = $db->loadAssocList();
		$options = array();

		foreach ($inviters as $key => $value)
		{
			$options[] = JHtml::_('select.option', $value['inviter_id'], $value['name']);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
