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

	$apiURL = 'api.map.baidu.com';


	$mainScriptBegin = $urlProtocol.'://'.$apiURL.'/api';
	
	$mainScriptMiddle = "";
        $scriptParametersExist = 0;

	if ($apiversion != "")
	{
            $scriptParametersExist = 1;
            if ($mainScriptMiddle == "")
            {
                    $mainScriptMiddle = 'v='.$apiversion;
            }
            else
            {
                    $mainScriptMiddle .= '&amp;v='.$apiversion;
            }
		
	}

	if ($apikey4map != "")
	{
            $scriptParametersExist = 1;
            if ($mainScriptMiddle == "")
            {
                    $mainScriptMiddle = 'ak='.$apikey4map;
            }
            else
            {
                    $mainScriptMiddle .= '&amp;ak='.$apikey4map;
            }		
		
	}


    /* do not use CALLBACK, because document not ready
    if ($loadtype == "9")
    {
        $scriptParametersExist = 1;
        if ($mainScriptMiddle =="")
        {
            $mainScriptMiddle .= "callback=initialize";
        }
        else
        {
            $mainScriptMiddle .= '&amp;callback=initialize';
        }
    }
    
    */


    $mainLang = "";
    $mainScriptAdd ="";
    
    if (isset($main_lang) && $main_lang != "")
    {
            $mainLang = substr($main_lang,0, strpos($main_lang, '-'));

            if ($mainLang != "")
            {
                $scriptParametersExist = 1;
                $mainScriptAdd .= '&amp;language='.$mainLang;
            }

    }    
    
    if ($scriptParametersExist != 0)
    {
        $mainScriptBegin .= '?';
    }


    $mainScriptBegin .= $mainScriptMiddle;

    $document->addScript($mainScriptBegin . $mainScriptAdd);

    if (isset($markercluster) && (int)$markercluster == 1)
    {
            //new version of MarkerClusterer
            $document->addScript($urlProtocol.'://'."api.map.baidu.com/library/TextIconOverlay/1.2/src/TextIconOverlay_min.js");
            $document->addScript($urlProtocol.'://'."api.map.baidu.com/library/MarkerClusterer/1.2/src/MarkerClusterer_min.js");       
            //$document->addScript($current_custom_js_path.'TextIconOverlay/1.2/TextIconOverlay_min.js');
            //$document->addScript($current_custom_js_path.'MarkerClusterer/1.2/MarkerClusterer_min.js');       
    }
    
    
    if (isset($area_restriction) && (int)$area_restriction == 1)
    {
            //new version of MarkerClusterer
            $document->addScript($urlProtocol.'://'."api.map.baidu.com/library/AreaRestriction/1.2/src/AreaRestriction_min.js");       
            //$document->addScript($current_custom_js_path.'AreaRestriction/1.2/AreaRestriction_min.js');     
    }
    


$document->addScript($current_custom_js_path.'common-min.js');
if (isset($use_object_manager) && (int)$use_object_manager == 1)
{
        $document->addScript($current_custom_js_path.'objectmanager-min.js');
}

if (isset($compatiblemode) && (int)$compatiblemode == 1)
{
        $document->addScript($current_custom_js_path.'compatibility-min.js');
}