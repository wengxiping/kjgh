<?php
/**
 * ------------------------------------------------------------------------
 * JA Focus Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */


class JATemplateHelper
{
	public static function getCustomFields($id, $context) {
		if ($context == 'article')
			$context = 'com_content.article';
		else if ($context == 'category')
			$context = 'com_content.categories';
		else if ($context == 'contact')
			$context = 'com_contact.contact';
		else if ($context == 'user')
			$context = 'com_users.user';
		$currentLanguage = JFactory::getLanguage();
		$currentTag = $currentLanguage->getTag();

		$sql = 'SELECT fv.value, fg.title AS gtitle, f.title AS ftitle, f.name
				FROM #__fields_values fv
				LEFT JOIN #__fields f ON fv.field_id = f.id
				LEFT JOIN #__fields_groups fg ON fg.id = f.group_id
				WHERE fv.item_id = '.$id.'
				AND f.context = "'.$context.'"
				AND f.language IN ("*", "'.$currentTag.'")
				AND f.access = 1
				';
			// echo $sql;
		$db = JFactory::getDbo();
		$db->setQuery($sql);
		$result = $db->loadObjectList();
		$arr = array();
		foreach ($result AS $r) {
			$arr[$r->name] = $r->value;
		}

		return $arr;
	}

	/*Article Category*/
	public static function categoryGroup($idbase=array()) {
		if (empty($idbase)) return array();
		  $sql = '
			SELECT DISTINCT sub.*
			FROM #__categories AS sub
			INNER JOIN #__categories AS this ON sub.lft > this.lft AND sub.rgt < this.rgt
			WHERE this.id IN ('.implode(',', $idbase).')';
		$db = JFactory::getDbo();
		$db->setQuery($sql);
		$result = $db->loadObjectList();
		$html = '<div class="category-menu"><ul>';
		$ct_showup = 6;
		$i=0;
		$show='';
		$hide='';
		foreach ($result AS $cat) {
			if ($cat->published!=1) continue;
			if ($cat->access!=1) continue;
			$cat->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($cat->id));
			
			if (++$i>$ct_showup) {
				$hide .= '<li>
						<a href="'.$cat->displayCategoryLink.'">'.$cat->title.'</a>
					</li>';
			} else {
				$show .= '<li>
						<a href="'.$cat->displayCategoryLink.'">'.$cat->title.'</a>
					</li>';
			}
			
		}
		$html .= $show;
		if ($ct_showup<count($result)) {
			$html .= '<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">More <span class="fa fa-angle-down" aria-hidden="true"></span></a>
				<ul class="dropdown-menu">'.$hide.'</ul>
				</li>';
		}
		$html .= '</ul></div>';
		return $html;
	}
}

?>