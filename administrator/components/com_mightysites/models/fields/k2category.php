<?php
/**
* @package		Mightysites
* @copyright	Copyright (C) 2009-2017 AlterBrains.com. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JFormFieldContentLanguage for a select list of content languages.
 * @since       11.1
 */
class JFormFieldK2category extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'K2category';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
		
		if (file_exists(JPATH_SITE . '/components/com_k2/k2.php'))
		{
			$db = JFactory::getDBO();
			$query = 'SELECT id, name, parent FROM #__k2_categories ORDER BY parent, ordering';
			$db->setQuery($query);
			$items = $db->loadObjectList();
			
			$children = array();
			
			foreach ($items as $v)
			{
				$v->title = $v->name;
				$v->parent_id = $v->parent;
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
	
			$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
					
			foreach ($list as $item)
			{
				$options[] = JHTML::_('select.option', $item->id, JString::str_ireplace('&#160;', '- ', $item->treename));
			}
			}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
