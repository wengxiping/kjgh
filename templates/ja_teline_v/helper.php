<?php
/**
 * ------------------------------------------------------------------------
 * JA Teline V Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$com_path = JPATH_SITE . '/components/com_content/';
JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');

JLoader::register('JAContentTypeModelItems', JPATH_ROOT . '/plugins/system/jacontenttype/models/items.php');

class JATemplateHelper
{
	public static function getArticles($params, $catid, $count, $front = NULL)
	{
		require_once dirname(__FILE__) . '/helper.content.php';
		$aparams = clone $params;
		$aparams->set('count', $count);
		if ($front != null) $aparams->set('show_front', $front);
		$aparams->set('catid', (array)$catid);
		$aparams->set('show_child_category_articles', 1);
		$aparams->set('levels', 2);
		$aparams->set('created_by_alias', -1);

		$alist = JATemplateHelperContent::getList($aparams);
		self::prepareItems($alist, $params);

		return $alist;
	}
    
	public static function getArticleByStart($params, $catid, $count, $front = NULL)
	{
    require_once dirname(__FILE__) . '/helper.content.php';
		$aparams = clone $params;
		$aparams->set('count', $count);
        if ($front != null) $aparams->set('show_front', $front);
		$aparams->set('catid', (array)$catid);
		$aparams->set('show_child_category_articles', 1);
		$aparams->set('levels', 2);
		$aparams->set('created_by_alias', -1);
		
		// Get an instance of the generic articles model
		//$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		$articles = new JAContentTypeModelItems(array('ignore_request' => true));
		
		// Set application parameters in model
		$app       	= JFactory::getApplication();
		$input		= $app->input;	
		$appParams 	= $app->getParams();
		$articles->setState('params', $appParams);
        
		$limit = $aparams->get('count',0);
		$limitstart =$app->input->get('_module_page', 0);
		$limitstart = ($limit != 0 ? ($limitstart > 1 ?(($limitstart - 1) * $limit): $limitstart) : 0);
		
		// Set the filters based on the module params
		$articles->setState('list.limit', (int) $aparams->get('count', 0));
		$articles->setState('filter.published', 1);
        
    // Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$articles->setState('filter.access', $access);
        
    // Prep for Normal or Dynamic Modes
		$mode = $aparams->get('mode', 'normal');
        
    switch ($mode)
		{
			case 'dynamic' :
				$option = $app->input->get('option');
				$view   = $app->input->get('view');

				if ($option === 'com_content')
				{
					switch ($view)
					{
						case 'category' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'categories' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'article' :
							if ($aparams->get('show_on_article_page', 1))
							{
								$article_id = $app->input->getInt('id');
								$catid      = $app->input->getInt('catid');

								if (!$catid)
								{
									// Get an instance of the generic article model
									$article = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));

									$article->setState('params', $appParams);
									$article->setState('filter.published', 1);
									$article->setState('article.id', (int) $article_id);
									$item   = $article->getItem();
									$catids = array($item->catid);
								}
								else
								{
									$catids = array($catid);
								}
							}
							else
							{
								// Return right away if show_on_article_page option is off
								return;
							}
							break;

						case 'featured' :
						default:
							// Return right away if not on the category or article views
							return;
					}
				}
				else
				{
					// Return right away if not on a com_content page
					return;
				}

				break;

			case 'normal' :
			default:
				$catids = $aparams->get('catid');
				$articles->setState('filter.category_id.include', (bool) $aparams->get('category_filtering_type', 1));
				break;
		}
        
    // Category filter
		if ($catids)
		{
			if ($aparams->get('show_child_category_articles', 0) && (int) $aparams->get('levels', 0) > 0)
			{
				// Get an instance of the generic categories model
				$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$categories->setState('params', $appParams);
				$levels = $aparams->get('levels', 1) ? $aparams->get('levels', 1) : 9999;
				$categories->setState('filter.get_children', $levels);
				$categories->setState('filter.published', 1);
				$categories->setState('filter.access', $access);
				$additional_catids = array();

				foreach ($catids as $catid)
				{
					$categories->setState('filter.parentId', $catid);
					$recursive = true;
					$items     = $categories->getItems($recursive);

					if ($items)
					{
						foreach ($items as $category)
						{
							$condition = (($category->level - $categories->getParent()->level) <= $levels);

							if ($condition)
							{
								$additional_catids[] = $category->id;
							}
						}
					}
				}

				$catids = array_unique(array_merge($catids, $additional_catids));
			}

			$articles->setState('filter.category_id', $catids);
		}

		// New Parameters
		$articles->setState('filter.featured', $aparams->get('show_front', 'show'));
		$articles->setState('filter.author_id', $aparams->get('created_by', ""));
		$articles->setState('filter.author_id.include', $aparams->get('author_filtering_type', 1));
		$articles->setState('filter.author_alias', $aparams->get('created_by_alias', ""));
		$articles->setState('filter.author_alias.include', $aparams->get('author_alias_filtering_type', 1));
		$excluded_articles = $aparams->get('excluded_articles', '');
        
    if ($excluded_articles)
		{
			$excluded_articles = explode("\r\n", $excluded_articles);
			$articles->setState('filter.article_id', $excluded_articles);

			// Exclude
			$articles->setState('filter.article_id.include', false);
		}

		$date_filtering = $aparams->get('date_filtering', 'off');

		if ($date_filtering !== 'off')
		{
			$articles->setState('filter.date_filtering', $date_filtering);
			$articles->setState('filter.date_field', $aparams->get('date_field', 'a.created'));
			$articles->setState('filter.start_date_range', $aparams->get('start_date_range', '1000-01-01 00:00:00'));
			$articles->setState('filter.end_date_range', $aparams->get('end_date_range', '9999-12-31 23:59:59'));
			$articles->setState('filter.relative_date', $aparams->get('relative_date', 30));
		}
        
        // Filter by language
		$articles->setState('filter.language', $app->getLanguageFilter());

		JATemplateHelperContent::jaFilter($articles, $params);
        
		$db = JFactory::getDbo();
		$query = $articles->getListQuery();
		$query->clear('order');
		$query->join('INNER','#__content_meta as ctm ON ctm.content_id = a.id');
		$query->where('ctm.'.$db->quoteName('meta_key').'='.$db->quote('start'));
		$query->order('ctm.'.$db->quoteName('meta_value').' '.$aparams->get('article_ordering_direction','ASC'));
		
		$db->setQuery($query, $limitstart, $limit);
		$items = $db->loadObjectList();
		
		// Display options
		$show_date        = $aparams->get('show_date', 0);
		$show_date_field  = $aparams->get('show_date_field', 'created');
		$show_date_format = $aparams->get('show_date_format', 'Y-m-d H:i:s');
		$show_category    = $aparams->get('show_category', 0);
		$show_hits        = $aparams->get('show_hits', 0);
		$show_author      = $aparams->get('show_author', 0);
		$show_introtext   = $aparams->get('show_introtext', 0);
		$introtext_limit  = $aparams->get('introtext_limit', 100);
        
    // Find current Article ID if on an article page
		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if ($option === 'com_content' && $view === 'article')
		{
			$active_article_id = $app->input->getInt('id');
		}
		else
		{
			$active_article_id = 0;
		}
        
    // Prepare data for display using display options
		foreach ($items as &$item)
		{	
			$attribs = $item->attribs;
			$ItemParams = new \Joomla\Registry\Registry;
			$ItemParams->loadString($item->attribs);
			
			$item->alternative_readmore = $ItemParams->get('alternative_readmore');
			$item->layout = $ItemParams->get('layout');
			
			$item->params = clone $articles->getState('params');
			
			/*For blogs, article params override menu item params only if menu param = 'use_article'
			Otherwise, menu item params control the layout
			If menu item is 'use_article' and there is no article param, use global*/
			if (($input->getString('layout') == 'blog') || ($input->getString('view') == 'featured')
			|| ($articles->getState('params')->get('layout_type') == 'blog'))
			{
				// Create an array of just the params set to 'use_article'
				$menuParamsArray = $articles->getState('params')->toArray();
				$articleArray = array();

				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_article')
					{
						// If the article has a value, use it
						if ($articleParams->get($key) != '')
						{
							// Get the value from the article
							$articleArray[$key] = $articleParams->get($key);
						}
						else
						{
							// Otherwise, use the global value
							$articleArray[$key] = $globalParams->get($key);
						}
					}
				}

				// Merge the selected article params
				if (count($articleArray) > 0)
				{
					$articleParams = new Registry;
					$articleParams->loadArray($articleArray);
					$item->params->merge($articleParams);
				}
			}
			else
			{
				// For non-blog layouts, merge all of the article params
				$item->params->merge($ItemParams);
			}
			
			// Get display date
			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}
			
			// Compute the asset access permissions.
			// Technically guest could edit an article, but lets not check that to improve performance a little.
			$user = JFactory::getUser();
			$userId = $user->get('id');
			$guest = $user->get('guest');
			$groups = $user->getAuthorisedViewLevels();
			if (!$guest)
			{
				$asset = 'com_content.article.' . $item->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}

				// Now check if edit.own is available.
				elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by)
					{
						$item->params->set('access-edit', true);
					}
				}
			}
			
			$access = $articles->getState('filter.access');

			if ($access)
			{
				// If the access filter has been set, we already have only the articles this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || $item->category_access === null)
				{
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else
				{
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}
			
			// Get the tags
			$item->tags = new JHelperTags;
			$item->tags->getItemTags('com_content.article', $item->id);
		
		
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->event   = new stdClass;
			
			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available
			if (!isset($item->text))
			{
				$item->text = $item->introtext;
			}

			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array ('com_content.category', &$item, &$item->params, 0));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			}
			else
			{
				$app       = JFactory::getApplication();
				$menu      = $app->getMenu();
				$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuitems[0]))
				{
					$Itemid = $menuitems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$Itemid = $app->input->getInt('Itemid');
				}

				$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
			}

			// Used for styling the active article
			$item->active      = $item->id == $active_article_id ? 'active' : '';
			$item->displayDate = '';

			if ($show_date)
			{
				$item->displayDate = JHTML::_('date', $item->$show_date_field, $show_date_format);
			}

			if ($item->catid)
			{
				$item->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
				$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				$item->displayCategoryTitle = $show_category ? $item->category_title : '';
			}

			$item->displayHits       = $show_hits ? $item->hits : '';
			$item->displayAuthorName = $show_author ? $item->author : '';

			if ($show_introtext)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'mod_jacontenttype.content');
				$item->introtext = self::_cleanIntrotext($item->introtext);
			}

			$item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, $introtext_limit) : '';
			$item->displayReadmore  = $item->alternative_readmore;
			
		}
        
        self::prepareItems($items, $params);

		return $items;
	}

	public static function getCategories($parent = 'root', $count = 0)
	{
		require_once dirname(__FILE__) . '/helper.content.php';
		$params = new JRegistry();
		$params->set('parent', $parent);
		$params->set('count', $count);
		return JATemplateHelperContent::getList($params);
	}

	public static function loadCategories($catids)
	{
		if ($catids) {
			$db = JFactory::getDbo();
			$query = "SELECT id FROM `#__categories` WHERE id IN (" . implode(',', (array) $catids) . ") AND published = 1 ORDER BY rgt ASC";
			$catids = $db->setQuery($query)->loadColumn();
		}

		$categories = array();
		foreach ($catids as $catid) {
			$cat = JTable::getInstance('category');
			$cat->load ($catid);
			if ($cat->published == 1) $categories[] = $cat;
		}
		return $categories;
	}

	public static function loadModule($name, $style = 'raw')
	{
		jimport('joomla.application.module.helper');
		$module = JModuleHelper::getModule($name);
		$params = array('style' => $style);
		echo JModuleHelper::renderModule($module, $params);
	}

	public static function loadModules($position, $style = 'raw')
	{
		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules($position);
		$params = array('style' => $style);
		foreach ($modules as $module) {
			echo JModuleHelper::renderModule($module, $params);
		}
	}

	public static function getParams () {
		static $menuParams = null;
		if (!$menuParams) {
			$app = JFactory::getApplication();
			// Load the parameters. Merge Global and Menu Item params into new object
			$params = JComponentHelper::getParams('com_content', true);
			$menuParams = new JRegistry;

			if ($menu = $app->getMenu()->getActive())
			{
				$menuParams->loadString($menu->params);
			}

			$menuParams->merge($params);
		}
		$params2 = clone $menuParams;
		return $params2;
	}

	public static function getCategoryClass($catid, $recursive = true) {
		$cats = JCategories::getInstance('content');
		$cat = $cats->get($catid);
		$params = new JRegistry;
		while ($cat) {
			$params->loadString($cat->params);
			if ($params->get ('classes')) return $params->get ('classes');
			$cat = $recursive ? $cat->getParent() : null;
		}
		return '';
	}

	/* render content item base on content type */
	public static function render ($item, $path, $displayData) {
		$attribs = new JRegistry ($item->attribs);
		$content_type = $attribs->get('ctm_content_type', 'article');
		// try to render the content with content type layout
		$html = JLayoutHelper::render($path . '.' . $content_type, $displayData);
		if (!$html) {
			// render with default layout
			$html = JLayoutHelper::render($path . '.default', $displayData);
		}
		return $html;
	}

	/* get Related items base on topic, tags, category */
	public static function getRelatedItems ($item, $params, $type = '') {
		JModelLegacy::addIncludePath(JPATH_SITE. '/plugins/system/jacontenttype/models', 'JAContentTypeModel');
		$model = JModelLegacy::getInstance('Items', 'JAContentTypeModel', array('ignore_request' => true));
		$model->setState('params', $params);
		if ($type == 'category' || $params->get ('same_cat') == 1) {
			$model->setState('filter.category_id', (array)$item->catid);
		}
		if ($type == 'topic' || $params->get ('same_topic') == 1) {
			$model->metaFilter ('topic_id', $item->params->get ('ctm_topic_id'));
		}
		if ($type == 'tags' || $params->get ('same_tags') == 1) {
			$model->setState('filter.tags', $item->id);
		}
		$model->setState('list.limit', $params->get ('count', 4));
		$model->setState('list.start', 0);
		$contenttype = $params->get ('same_contenttype') ? $item->params->get ('ctm_contenttype') : null;
		$items = $model->getMetaItems($contenttype, array('a.`id` != '.$item->id, 'a.`state`=1'));
		self::prepareItems($items, $params);

		return $items;
	}

	public static function countModules ($condition) {
		if (!$condition) return 0;
		// not render in component tmpl
		if (JFactory::getApplication()->input->get ('tmpl' == 'component')) return 0;
		return JFactory::getDocument()->countModules ($condition);
	}

	public static function renderModules ($position, $attribs = array()) {
		if (!$position) return null;
		// not render in component tmpl
		if (JFactory::getApplication()->input->get ('tmpl' == 'component')) return null;

		static $buffers = array();
		if (isset($buffers[$position])) return $buffers[$position];
		// init cache to prevent nested parse
		$buffers[$position] = '';
		// prevent cache
		$attribs['params'] = '{"cache":0}';
		$buffers[$position] = JFactory::getDocument()->getBuffer('modules', $position, $attribs);
		return $buffers[$position];
	}

	public static function prepareItems (&$items, $params) {
		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();

		// Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));

		$articles->setState('filter.access', $access);
		// Display options
		$show_date        = $params->get('show_date', 0);
		$show_date_field  = $params->get('show_date_field', 'created');
		$show_date_format = $params->get('show_date_format', 'Y-m-d H:i:s');
		$show_category    = $params->get('show_category', 0);
		$show_hits        = $params->get('show_hits', 0);
		$show_author      = $params->get('show_author', 0);
		$show_introtext   = $params->get('show_introtext', 0);
		$introtext_limit  = $params->get('introtext_limit', 100);

		// Find current Article ID if on an article page
		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if ($option === 'com_content' && $view === 'article')
		{
			$active_article_id = $app->input->getInt('id');
		}
		else
		{
			$active_article_id = 0;
		}

		// Prepare data for display using display options
		foreach ($items as &$item)
		{
			if($item->params instanceof JRegistry) {
				$iparams = new JRegistry($item->attribs);
				$item->params->merge($iparams);

			} else {
				$item->params = new JRegistry($item->attribs);
			}
			$item->slug    = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid ? $item->catid . ':' . $item->category_alias : $item->catid;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			}
			else
			{
				$app       = JFactory::getApplication();
				$menu      = $app->getMenu();
				$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuitems[0]))
				{
					$Itemid = $menuitems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$Itemid = $app->input->getInt('Itemid');
				}

				$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
                
			}
            
			// Used for styling the active article
			$item->active      = $item->id == $active_article_id ? 'active' : '';
			$item->displayDate = '';

			if ($show_date)
			{
				$item->displayDate = JHTML::_('date', $item->$show_date_field, $show_date_format);
			}

			if ($item->catid)
			{
				$item->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
				$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				$item->displayCategoryTitle = $show_category ? $item->category_title : '';
			}

			$item->displayHits       = $show_hits ? $item->hits : '';
			$item->displayAuthorName = $show_author ? $item->author : '';

			if ($show_introtext)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'mod_articles_category.content');
				$item->introtext = self::_cleanIntrotext($item->introtext);
			}

			$item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, $introtext_limit) : '';
			$item->displayReadmore  = $item->alternative_readmore;
		}

		$dispatcher    = JEventDispatcher::getInstance();

		foreach ($items as &$item) {
			$item->event = new stdClass;

			// Old plugins: Ensure that text property is available
			if (!isset($item->text)) {
				$item->text = $item->introtext;
			}
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_content.featured', &$item, &$params, 0));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.featured', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.featured', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.featured', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}
	}

	public static function countItemsByDate ($catids, $duration) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('count(*)');
		$query->from('#__content');
		// get list of catids
		if ($catids)
		{
			$catids = (array)$catids;
			// Get an instance of the generic categories model
			JLoader::register('ContentModelCategories', JPATH_SITE . '/components/com_content/models/categories.php');
			$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$categories->setState('params', JFactory::getApplication()->getParams());
			$categories->setState('filter.get_children', 999);
			$categories->setState('filter.published', 1);
			// $categories->setState('filter.access', $access);
			$additional_catids = array();

			foreach ($catids as $catid)
			{
				$categories->setState('filter.parentId', $catid);
				$recursive = true;
				$items     = $categories->getItems($recursive);

				if ($items)
				{
					foreach ($items as $category)
					{
						$additional_catids[] = $category->id;
					}
				}
			}

			$catids = array_unique(array_merge($catids, $additional_catids));
		}

		// cat group
		if (count($catids)) {
			$query->where('`catid` in (' . implode(',', $catids) . ')');
		}

		// limit by time
		$nullDate	= $db->quote($db->getNullDate());
		$nowDate	= $db->quote(JFactory::getDate()->toSql());
		$query->where('state = 1');
		$query->where('(publish_up = '.$nullDate.' OR publish_up <= '.$nowDate.')');
		$query->where('(publish_down = '.$nullDate.' OR publish_down >= '.$nowDate.')');
		$query->where( 'publish_up >= DATE_SUB(' . $nowDate . ', INTERVAL ' . (int) $duration . ' DAY)');
		//filter by language
		$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$db->setQuery($query);
		$count = $db->loadResult();
		return $count;
	}
	

}

?>