<?php
/**
 * ------------------------------------------------------------------------
 * JA Conf Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
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
}

?>