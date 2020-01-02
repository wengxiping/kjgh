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

class JabuilderModelPages extends JModelList
{
	public function getListQuery() 
	{
		$db = JFactory::getDbo();
		
        $query = $db->getQuery(true);
		
        $query->select('*')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quotename('parent').'= 0')
				->where($db->quoteName('type').'='.$db->quote('page'))
				->order('id desc');
		
        return $query;
    }
}