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
 * ZhBaidu MapRouter Form Field class for the ZhBaiduMap component
 */
class JFormFieldMapType extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'MapType';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions() 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('h.*, c.title as category ');
		$query->from('#__zhbaidumaps_maptypes as h');
		$query->leftJoin('#__categories as c on h.catid=c.id');
		$query->order('h.title');
		
		$db->setQuery((string)$query);
		$maptypes = $db->loadObjectList();
		$options = array();
		if ($maptypes)
		{
			foreach($maptypes as $maptype) 
			{
				$options[] = JHtml::_('select.option', $maptype->id, $maptype->title . ($maptype->catid ? ' (' . $maptype->category . ')' : ''));
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
