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

class JabuilderHeper extends JHelperContent
{
	public static function addSubmenu($vName)
	{
		
		JHtmlSidebar::addEntry(
			JText::_('Pages'),
			'index.php?option=com_jabuilder&view=pages',
			$vName == 'pages'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('Settings'),
			'index.php?option=com_jabuilder&view=settings',
			$vName == 'settings'
		);
		
	}
	
	public static function stringUrlsafe($string)
	{
		$trans = array(

		"đ"=>"d","ă"=>"a","â"=>"a","á"=>"a","à"=>"a",

		"ả"=>"a","ã"=>"a","ạ"=>"a",

		"ấ"=>"a","ầ"=>"a","ẩ"=>"a","ẫ"=>"a","ậ"=>"a",

		"ắ"=>"a","ằ"=>"a","ẳ"=>"a","ẵ"=>"a","ặ"=>"a",

		"é"=>"e","è"=>"e","ẻ"=>"e","ẽ"=>"e","ẹ"=>"e",

		"ế"=>"e","ề"=>"e","ể"=>"e","ễ"=>"e","ệ"=>"e",

		"í"=>"i","ì"=>"i","ỉ"=>"i","ĩ"=>"i","ị"=>"i",

		"ư"=>"u","ô"=>"o","ơ"=>"o","ê"=>"e",

		"Ư"=>"u","Ô"=>"o","Ơ"=>"o","Ê"=>"e",

		"ú"=>"u","ù"=>"u","ủ"=>"u","ũ"=>"u","ụ"=>"u",

		"ứ"=>"u","ừ"=>"u","ử"=>"u","ữ"=>"u","ự"=>"u",

		"ó"=>"o","ò"=>"o","ỏ"=>"o","õ"=>"o","ọ"=>"o",

		"ớ"=>"o","ờ"=>"o","ở"=>"o","ỡ"=>"o","ợ"=>"o",

		"ố"=>"o","ồ"=>"o","ổ"=>"o","ỗ"=>"o","ộ"=>"o",

		"ú"=>"u","ù"=>"u","ủ"=>"u","ũ"=>"u","ụ"=>"u",

		"ứ"=>"u","ừ"=>"u","ử"=>"u","ữ"=>"u","ự"=>"u",

		"ý"=>"y","ỳ"=>"y","ỷ"=>"y","ỹ"=>"y","ỵ"=>"y",

		"Ý"=>"Y","Ỳ"=>"Y","Ỷ"=>"Y","Ỹ"=>"Y","Ỵ"=>"Y",

		"Đ"=>"D","Ă"=>"A","Â"=>"A","Á"=>"A","À"=>"A",

		"Ả"=>"A","Ã"=>"A","Ạ"=>"A",

		"Ấ"=>"A","Ầ"=>"A","Ẩ"=>"A","Ẫ"=>"A","Ậ"=>"A",

		"Ắ"=>"A","Ằ"=>"A","Ẳ"=>"A","Ẵ"=>"A","Ặ"=>"A",

		"É"=>"E","È"=>"E","Ẻ"=>"E","Ẽ"=>"E","Ẹ"=>"E",

		"Ế"=>"E","Ề"=>"E","Ể"=>"E","Ễ"=>"E","Ệ"=>"E",

		"Í"=>"I","Ì"=>"I","Ỉ"=>"I","Ĩ"=>"I","Ị"=>"I",

		"Ư"=>"U","Ô"=>"O","Ơ"=>"O","Ê"=>"E",

		"Ư"=>"U","Ô"=>"O","Ơ"=>"O","Ê"=>"E",

		"Ú"=>"U","Ù"=>"U","Ủ"=>"U","Ũ"=>"U","Ụ"=>"U",

		"Ứ"=>"U","Ừ"=>"U","Ử"=>"U","Ữ"=>"U","Ự"=>"U",

		"Ó"=>"O","Ò"=>"O","Ỏ"=>"O","Õ"=>"O","Ọ"=>"O",

		"Ớ"=>"O","Ờ"=>"O","Ở"=>"O","Ỡ"=>"O","Ợ"=>"O",

		"Ố"=>"O","Ồ"=>"O","Ổ"=>"O","Ỗ"=>"O","Ộ"=>"O",

		"Ú"=>"U","Ù"=>"U","Ủ"=>"U","Ũ"=>"U","Ụ"=>"U",

		"Ứ"=>"U","Ừ"=>"U","Ử"=>"U","Ữ"=>"U","Ự"=>"U",);

		//remove any '-' from the string they will be used as concatonater

		$str = str_replace('-', ' ', $string);

		$str = strtr($str, $trans);

		$lang = JFactory::getLanguage();

		$str = $lang->transliterate($str);

		// remove any duplicate whitespace, and ensure all characters are alphanumeric

		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

		// lowercase and trim

		$str = trim(strtolower($str));

		return $str;
	}  
}