<?php

/**
 * ------------------------------------------------------------------------
 * JA Builder Package
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR . '/components/com_menus/models/item.php';
require_once JPATH_ADMINISTRATOR . '/components/com_menus/tables/menu.php';		
		
class JabuilderModelPage extends JModelAdmin
{
	protected $type = 'page';
	
	public function __construct($config = array()) {
		$lang = JFactory::getLanguage();
		$extension = 'com_menus';
		$language_tag = JFactory::getLanguage()->getTag();
		$lang->load($extension, JPATH_ADMINISTRATOR, $language_tag, true);
		parent::__construct($config);
	}
	
	public function getTable($type = 'Page', $prefix = 'JabuilderTable', $config = array())
	{
		$table = JTable::getInstance($type, $prefix, $config);
		
		return $table;
	}
	
	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		$input = JFactory::getApplication()->input;
		$id = $input->get('id',0);
		$menuid = $input->get('menuid', 0);
		if ($menuid) {
			$menu = $this->getMenuModel()->getItem($menuid);
		} elseif($id) {
			$menu = $this->getMenuByPage($id);
		} else {
			$menu = null;
		}

		$item = $this->syncMenuData($item, $menu);
		
		if (!empty($item) && !empty($item->params)) {
			$params = $item->params;
			$item->feature_image = !empty($params['feature_image']) ? $params['feature_image'] : '';
		}
		return $item;
	}
	
	public function syncMenuData($item, $menu) {
		if (!empty($menu)) {
			$item->menuid = $menu->id;
			if (is_array($menu->params)) {
				$menu_params = $menu->params;
			} else {
				$menu_params = (array) json_decode($menu->params);
			}
			$item->meta_description =  isset($menu_params['menu-meta_description']) ? $menu_params['menu-meta_description'] : '';
			$item->meta_keywords = isset($menu_params['menu-meta_keywords']) ? $menu_params['menu-meta_keywords'] : '';
			$item->robots = isset($menu_params['robots']) ? $menu_params['robots'] : '';
			$item->parent_id = $menu->parent_id;
			$item->state = $menu->published;
			$item->menutype = $menu->menutype;
			$item->access = $menu->access;
			$item->menuordering= $menu->id;
			$item->template_style_id = $menu->template_style_id;
		}
		return $item;
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_jabuilder.page',
			'page',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}
		
		return $form;
	}
	
	protected function loadFormData()
	{
		$data = $this->getItem();

		return $data;
	}
	
	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		
		$data['type'] = $this->type;

		if( $input->getCmd('id') ) 
		{
			$data['id'] = $input->getCmd('id');
			// unset $data['slug'] if created
			if (isset($data['slug'])) unset($data['slug']);
		} else {
			$data['slug'] = $this->createSlug();
		}

		
		if( empty($data['alias']) )
		{
			$data['alias'] = JabuilderHeper::stringUrlsafe($data['title']);
		}
		
		// solved duplicate alias
		if( $this->num_duplicated_alias( $data['id'], $data['alias'] ) )
		{
			for ( $i = 1; $i < 100; $i++) 
			{
				$new_alias = $data['alias'].'-'.$i;
				
				if( !$this->num_duplicated_alias( $data['id'], $new_alias ) )
				{
					$data['alias'] = $new_alias;
					break;
				}
			}
		}
		
		$params = new stdClass();
		$params->meta_description	= $data['meta_description'];
		$params->meta_keywords	= $data['meta_keywords'];
		$params->robots = $data['robots'];
		$params->feature_image = $data['feature_image'];
		$data['params'] = json_encode($params);
		if (parent::save($data)) {
			$menuModel = $this->getMenuModel();
			if (!empty($data['menuid'])) {
				$menu = $menuModel->getItem($data['menuid']);
				$menu->params['menu_image'] = $data['feature_image'];
				$menu->params['menu-meta_description'] = $data['meta_description'];
				$menu->params['menu-meta_keywords'] = $data['meta_keywords'];
				$menu->params['robots'] = $data['robots'];
				$menu->published = $data['state'];
				$id = $this->getState('page.id');
				$menu->link = 'index.php?option=com_jabuilder&view=page&id='.$id;
				$menu->access = $data['access'];
				$menu->menutype = $data['menutype'];
				$menu->parent_id = $data['parent_id'];
				$menu->template_style_id = $data['template_style_id'];
				$menu->menuordering = $data['menuordering'];
				$component = JComponentHelper::getComponent('com_jabuilder');
				$menu->component_id = $component->id;
				$menuModel->save((array)$menu);
			} else {
				$this->addNewMenu($data);
			}
			return true;
		}	
		return false;
	}
	

	protected function createSlug () {
		$slug = null;
		while (!$slug) {
			$slug = substr(md5(uniqid(rand(10000,99999), true)), 0, 13);
			if ($this->slugExisted ($slug)) $slug = null;
		}
		return $slug;
	}

	protected function slugExisted ($slug) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select ('id')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('slug').'='.$db->quote($slug));
		$db->setQuery($query);
		return $db->loadResult();
	}


	protected function num_duplicated_alias($id, $alias)
	{
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query->select( $db->quoteName('alias') )
				
				->from( $db->quoteName('#__jabuilder_pages') )
				
				->where( $db->quoteName('alias').'='.$db->quote($alias) )
				
				->where( $db->quoteName('id').'!='.$id );
		
		$db->setQuery($query);

		$db->execute();
		
		return $db->getNumRows();
	}
	
	public function getMenuModel() {
		$model = JModelAdmin::getInstance('Item', 'MenusModel');
		return $model;
	}
	
	public function getParentMenuLevel($parent_id) {
		$parent = $this->getMenuModel()->getItem($parent_id);
		return $parent->level;
	}
	
	function getMenuByPage($id=null) {
		$q = "SELECT * FROM #__menu ";
		$q .= "WHERE link = 'index.php?option=com_jabuilder&view=page&id=$id' and published != -2 order by id desc";
		$db = JFactory::getDbo()->setQuery($q);
		$menu = $db->loadObject();
		return $menu;
	}
	
	public function addNewMenu($data) {
		$table = JTable::getInstance('Menu', 'MenusTable');
		$table->title = $data['title'];
		$table->alias = $data['alias'];
		$table->published = $data['state'];
		$table->access = $data['access'];
		$table->menutype = $data['menutype'];
		$table->parent_id = $data['parent_id'];
		$id = $this->getState('page.id');
		$table->link = 'index.php?option=com_jabuilder&view=page&id='.$id;
		$table->type = 'component';
		$component = JComponentHelper::getComponent('com_jabuilder');
		$table->component_id = $component->id;
		$table->browserNav = 0;
		$table->template_style_id = $data['template_style_id'];
		$table->language = '*';
		$table->level = $this->getParentMenuLevel($table->parent_id) + 1;

		$params = new stdClass();
		$params->{'menu-meta_description'} = $data['meta_description'];
		$params->{'menu-meta_keywords'} = $data['meta_keywords'];
		$params->robots = $data['robots'];
		
		$table->params = json_encode($params);
		$table->setLocation($data['parent_id'], 'last-child');
		if($table->store()){
			return $table;
		}
		$error = $table->getError();
		JFactory::getApplication()->enqueueMessage($error, 'error');
		return false;
	}
	
	public function publish(&$pks, $value = 1) {
		$db = JFactory::getDbo();
		foreach($pks as $id) {
			$q = 'UPDATE `#__jabuilder_pages` SET state =' . (int) $value . ' WHERE id='. (int) $id;
			$db->setQuery($q);
			$db->execute();
		}
	}

}