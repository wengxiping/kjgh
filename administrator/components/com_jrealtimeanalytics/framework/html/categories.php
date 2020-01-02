<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined ( 'JPATH_BASE' ) or die ();

/**
 * Form Field class for the Joomla Framework.
 *
 * @package Joomla.Administrator
 * @subpackage com_categories
 * @since 1.6
 */
class JRealtimeHtmlCategories extends JObject {
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var string
	 * @since 1.6
	 */
	public $type = 'CategoryEdit';
	
	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param string $extension
	 *        	The extension option e.g. com_something.
	 * @param array $config
	 *        	An array of configuration options. By default, only
	 *        	published and unpublished categories are returned.
	 *        	
	 * @return array
	 *
	 * @since 1.5
	 */
	public static function getOptions($isFilter = false, $exclude = null) {
		$db = JFactory::getDbo ();
		$query = $db->getQuery ( true )->select ( 'a.id, a.title, a.level' )->from ( '#__realtimeanalytics_categories AS a' )->where ( 'a.parent_id > 0' );
		$query->where ( 'a.published = 1');
		$query->order ( 'a.lft' );
		
		$db->setQuery ( $query );
		$items = $db->loadObjectList ();
		
		$options = array();
		if(!$isFilter) {
			$options[] = JHtml::_ ( 'select.option', 1, JText::_('COM_JREALTIME_ROOTCAT'));
		} else {
			$options[] = JHtml::_ ( 'select.option', null, JText::_('COM_JREALTIME_FILTER_BYCAT'));
		}
		
		foreach ( $items as &$item ) {
			$repeat = ($item->level - 1 >= 0) ? $item->level : 0;
			$item->title = str_repeat ( '- ', $repeat - (int)$isFilter) . $item->title;
			$disabled = $item->id == $exclude ? true : false;
			$options[] = JHtml::_ ( 'select.option', $item->id, $item->title, 'value', 'text', $disabled);
		}
		
		return $options;
	}
}
