<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('list');
/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5
 */
class JFormFieldUberlayout extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'uberlayout';

	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query	->clear()
				->select('*')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('type') . '=' . $db->quote('layout'));
		
		$db->setQuery($query);

		$layouts = $db->loadObjectList();

		foreach ($layouts as $layout) {
			$tmp = array();
			$tmp['value'] = $layout->id;
			$tmp['text'] = $layout->title;
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
