<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap Component
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Zh BaiduMap Helper
 */
abstract class comZhBaiduMapMapsHelper
{


	public static function get_control_position_option($pos, $offx, $offy)
	{
		if ((int)$pos != 0)
		{
			$def_val = '';
			
			switch ((int)$pos) 
			{
				case 1:
       					$def_val .= "\n".'  anchor: BMAP_ANCHOR_TOP_LEFT';
				break;
				case 2:
	       				$def_val .= "\n".'  anchor: BMAP_ANCHOR_TOP_RIGHT';
				break;
				case 3:
       					$def_val .= "\n".'  anchor: BMAP_ANCHOR_BOTTOM_LEFT';
				break;
				case 4:
       					$def_val .= "\n".'  anchor: BMAP_ANCHOR_BOTTOM_RIGHT';
				break;
				default:
					$def_val .= '';
				break;
			}
			
			if ($def_val != "")
			{
				$def_val .= "\n".', offset: new BMap.Size('.(int)$offx.', '.(int)$offy.')';
			}

			if ($def_val != "")
			{
				$ret_val = $def_val;
			}
			else
			{
				$ret_val = '';
			}
			
			return $ret_val;
		}
		else
		{
			return '';
		}	
	}

	
}
