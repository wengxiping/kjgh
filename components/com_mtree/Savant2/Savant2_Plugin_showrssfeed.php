<?php
/**
* Mosets Tree 
*
* @package Mosets Tree 2.0
* @copyright (C) 2007-present Lee Cher Yeong
* @url http://www.mosets.com/
* @author Lee Cher Yeong <mtree@mosets.com>
**/
defined('_JEXEC') or die('Restricted access');

//Base plugin class.
require_once JPATH_ROOT.'/components/com_mtree/Savant2/Plugin.php';

class Savant2_Plugin_showrssfeed extends Savant2_Plugin {
	
	function plugin()
	{
		global $Itemid, $mtconf, $cat_id, $link_id, $user_id;

		list($type) = func_get_args();

		$url = '';

		if($type == 'listnew' || $type == 'listupdated') {
			$type = substr($type,4);
		}

		if( ($type == 'new' || $type == 'updated') && $mtconf->get('show_list' . $type . 'rss') ) {
			$url .= 'index.php?option=com_mtree&task=rss&type=' . $type;
			if( isset($cat_id) && $cat_id > 0 ) {
				$url .= '&cat_id=' . $cat_id;
			}
			$url .= '&Itemid=' . $Itemid;
		} elseif($type == 'userfavourites' && $mtconf->get('show_userfavouritesrss')) {
			$url .= 'index.php?option=com_mtree&task=rss&type=userfavourites';
			if( isset($user_id) && $user_id > 0 ) {
				$url .= '&user_id=' . $user_id;
			}
			$url .= '&Itemid=' . $Itemid;
		} elseif($type == 'reviews' && $mtconf->get('show_listingreviewsrss')) {
			$url .= 'index.php?option=com_mtree&task=rss&type=reviews';
			if( isset($link_id) && $link_id > 0 ) {
				$url .= '&link_id=' . $link_id;
			}
			$url .= '&Itemid=' . $Itemid;
		} else {
			return false;
		}

		$html = '<a class="mt-rss-feed-icon" href="' . $url . '">';
		$html .= '<img src="' . $mtconf->getjconf('live_site') . $mtconf->get('relative_path_to_images') . 'rss.png" width="14" height="14" hspace="5" alt="RSS" border="0" />';
		$html .= '</a>';

		return $html;
	}

}