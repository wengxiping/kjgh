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

use Joomla\Utilities\ArrayHelper;

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * ZhBaiduRouters Model
 */
class ZhBaiduMapModelMapTypes extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'h.id',
				'title', 'h.title',
				'published', 'h.published',
				'catid', 'h.catid', 'category_title',
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		parent::populateState('h.title','asc');

		$app = JFactory::getApplication();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		
		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_zhbaidumap');
		$this->setState('params', $params);

	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('h.id,h.title,h.published,h.publish_up,h.publish_down,h.catid,c.title as category');
		$query->from('#__zhbaidumaps_maptypes as h');
		$query->leftJoin('#__categories as c on h.catid=c.id');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%', false);
			$query->where('(h.title LIKE '.$search.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('h.published = '.(int) $published);
		} elseif ($published === '') {
			$query->where('(h.published IN (0, 1))');
		}
		
		// Filter by a single or group of categories.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('h.catid = '.(int) $categoryId);
		}
		else if (is_array($categoryId)) {
			if(version_compare(JVERSION, '3.5.0', 'ge'))
                        {
                            $categoryId = ArrayHelper::toInteger($categoryId);
                        }
                        else
                        {
                            JArrayHelper::toInteger($categoryId);
                        }
			$categoryId = implode(',', $categoryId);
			$query->where('h.catid IN ('.$categoryId.')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == '') {
			$orderCol = 'h.title';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));
		
		return $query;
	}



}
