<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * ZhBaiduMap Form Field class for the ZhBaiduMap component
 */
class JFormFieldMapMap extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'MapMap';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions() 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('h.*,c.title as category');
		$query->from('#__zhbaidumaps_maps as h');
		$query->leftJoin('#__categories as c on h.catid=c.id');
		$query->order('h.title');
		
		$db->setQuery((string)$query);
		$maps = $db->loadObjectList();
		$options = array();
		if ($maps)
		{
			foreach($maps as $map) 
			{
				$options[] = JHtml::_('select.option', $map->id, $map->title . ($map->catid ? ' (' . $map->category . ')' : ''));
			}
		}
        
        // Add a null option.
		array_unshift($options, JHtml::_('select.option', '', JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_FILTER_MAP')));

		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
