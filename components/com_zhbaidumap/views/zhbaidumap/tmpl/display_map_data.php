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

$document	= JFactory::getDocument();

$allowUserMarker = 0;
$scripttext = '';
$scripttextBegin = '';
$scripttextEnd = '';

$divmapheader ="";
$divmapfooter ="";
$currentUserInfo ="";
$currentUserID = 0;

$scripthead ="";

// Change translation language and load translation
$currentLanguage = JFactory::getLanguage();
$currentLangTag = $currentLanguage->getTag();

$main_lang_little = "";

if (isset($map->lang) && $map->lang != "")
{
        $main_lang = $map->lang;
        $main_lang_little = substr($main_lang,0, strpos($main_lang, '-'));

      
	$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $map->lang, true);	
	$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $map->lang, true);	
	
	// fix translation problem on plugin call
	$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap' , $map->lang, true);	

	if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
	{
		$this->main_lang = $main_lang;

	}

	
}
else
{
	$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $currentLangTag, true);	
	$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $currentLangTag, true);		
	$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap' , $currentLangTag, true);	
	
}


require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/maps.php';
require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/placemarks.php';
require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/paths.php';
require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/map_divs.php';



if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
	// all OK
	if ((int)$MapXdoLoad == 0)
	{   // ***** Plugin call *****
		//   hide loading call
		//   but passing composite ID
		if (isset($MapXArticleId) && ($MapXArticleId != ""))
		{
			$mapInitTag = $MapXArticleId;
			// Map DIV suffix
			$mapDivSuffix = '_'.$MapXArticleId;
		}
		else
		{
			if (isset($MapXsuffix) && ($MapXsuffix != ""))
			{
				$mapInitTag = $MapXsuffix;
				$mapDivSuffix = "";
			}
			else
			{
				$mapInitTag = "";
				$mapDivSuffix = "";
			}
		}
	}
	else
	{
		if (isset($MapXsuffix) && ($MapXsuffix != ""))
		{
			$mapInitTag = $MapXsuffix;
			$mapDivSuffix = "";
		}
		else
		{
			$mapInitTag = "";
			$mapDivSuffix = "";
		}
	}

}
else
{
	$MapXdoLoad = 1;

	if (isset($MapXsuffix) && ($MapXsuffix != ""))
	{
		$mapInitTag = $MapXsuffix;
		$mapDivSuffix = "";
	}
	else
	{
		$mapInitTag = "";
		$mapDivSuffix = "";
	}
}




if (isset($map->usermarkers) 
  && ((int)$map->usermarkers == 1
	  ||(int)$map->usermarkers == 2)) 
{
    $currentUser = JFactory::getUser();

    if ($currentUser->id == 0)
    {
		if ((int)$map->usermarkers == 1)
		{
			$currentUserInfo .= '<div id="BDMapsLogin'.$mapDivSuffix.'" class="zhbdm-login">';
			$currentUserInfo .= JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NOTLOGIN' );
			$currentUserInfo .= '</div>';
		}
		$allowUserMarker = 0;
		$currentUserID = 0;
    }
    else
    {
		if ((int)$map->usermarkers == 1)
		{
			$currentUserInfo .= '<div id="BDMapsLogin'.$mapDivSuffix.'" class="zhbdm-login">';
			$currentUserInfo .= JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LOGIN' ) .' '. $currentUser->name;
			$currentUserInfo .= '</div>';
		}
		$allowUserMarker = 1;
		$currentUserID = $currentUser->id;
    }
    
} 
else
{
	$allowUserMarker = 0;
	$currentUserID = 0;
}

// if post data to load
if ($allowUserMarker == 1
 && isset($_POST['marker_action']))
{		
$scripttext .= '<script type="text/javascript">';
	
	$db = JFactory::getDBO();

	if (isset($_POST['marker_action']) && 
		($_POST['marker_action'] == "insert") ||
		($_POST['marker_action'] == "update") 
		)
	{

		$title = substr($_POST["markername"], 0, 249);
		if ($title == "")
		{
			$title = 'Placemark';
		}

		$markericon = substr($_POST["markerimage"], 0, 249);
		if ($markericon == "")
		{
			$markericon ='default#';
		}
		
		$description = $_POST["markerdescription"];
		$latitude = substr($_POST["markerlat"], 0, 100);
		$longitude = substr($_POST["markerlng"], 0, 100);
		$group = substr($_POST["markergroup"], 0, 100);
		$markercatid = substr($_POST["markercatid"], 0, 100);
		$markerbaloon = substr($_POST["markerbaloon"], 0, 100);
		$markermarkercontent = substr($_POST["markermarkercontent"], 0, 100);
		if (isset($_POST['markerid']))
		{
			$markerid = (int)substr($_POST["markerid"], 0, 100);
		}
		else
		{
			$markerid = '';
		}
		$markerhrefimage = substr($_POST["markerhrefimage"], 0, 500);
		
		if (isset($map->usercontact) && (int)$map->usercontact == 1) 
		{
			$contactid = substr($_POST["contactid"], 0, 100);
		}
		else
		{
			$contactid = '';
		}
		
		$contactDoInsert = 0;
		
		if (isset($map->usercontact) && (int)$map->usercontact == 1) 
		{
			$contact_name = substr($_POST["contactname"], 0, 250);
			$contact_position = substr($_POST["contactposition"], 0, 250);
			$contact_phone = substr($_POST["contactphone"], 0, 250);
			$contact_mobile = substr($_POST["contactmobile"], 0, 250);
			$contact_fax = substr($_POST["contactfax"], 0, 250);
			$contact_address = substr($_POST["contactaddress"], 0, 250);
			$contact_email = substr($_POST["contactemail"], 0, 250);
			
			if (($contact_name != "") 
			  ||($contact_position != "")
			  ||($contact_phone != "")
			  ||($contact_mobile != "")
			  ||($contact_fax != "")
			  ||($contact_email != "")
			  ||($contact_address != "")
				)
			{
				$contactDoInsert = 1;
			}
		}

		$newRow = new stdClass;
		
		if ($_POST['marker_action'] == "insert")
		{
			$newRow->id = NULL;
			$newRow->userprotection = 0;
			$newRow->openbaloon = 0;
			$newRow->actionbyclick = 1;
			$newRow->access = 1;
			
			if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
			 &&($contactDoInsert == 1))
			{				
				$newRow->showcontact = 2;
			}
			else
			{				
				$newRow->showcontact = 0;
			}
		}
		else
		{
			$newRow->id = $markerid;

			if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
			 &&($contactDoInsert == 1) && ((int)$contactid == 0))
			{				
				$newRow->showcontact = 2;
			}
			
		}
		
		// Data for Contacts - begin
		if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
		  &&($contactDoInsert == 1))
		{
			$newContactRow = new stdClass;
			
			if ($_POST['marker_action'] == "insert")
			{
				$newContactRow->id = NULL;
				$newContactRow->published = (int)$map->usercontactpublished;
				$newContactRow->language = '*';
				$newContactRow->access = 1;
			}
			else
			{
				if ((int)$contactid == 0)
				{
					$newContactRow->id = NULL;
					$newContactRow->published = (int)$map->usercontactpublished;
					$newContactRow->language = '*';
					$newContactRow->access = 1;
				}
				else
				{
					$newContactRow->id = $contactid;
				}
			}
			
		}			
		// Data for Contacts - end
		
		// because it (quotes) escaped
		$newRow->title = str_replace('\\','', htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8'));
		$newRow->description = str_replace('\\','', htmlspecialchars($description, ENT_NOQUOTES, 'UTF-8'));
		// because it escaped
		$newRow->latitude = htmlspecialchars($latitude, ENT_QUOTES, 'UTF-8');
		$newRow->longitude = htmlspecialchars($longitude, ENT_QUOTES, 'UTF-8');
		$newRow->mapid = $map->id;
		$newRow->icontype = htmlspecialchars($markericon, ENT_QUOTES, 'UTF-8');
                
                if ($_POST['marker_action'] == "insert") {
                    $newRow->published = (int)$map->usermarkerspublished;
                    $newRow->createdbyuser = $currentUserID;
                } else {
                    // do not change state
                }

		
		$newRow->markergroup = htmlspecialchars($group, ENT_QUOTES, 'UTF-8');
		$newRow->catid = htmlspecialchars($markercatid, ENT_QUOTES, 'UTF-8');

		$newRow->baloon = htmlspecialchars($markerbaloon, ENT_QUOTES, 'UTF-8');
		$newRow->markercontent = htmlspecialchars($markermarkercontent, ENT_QUOTES, 'UTF-8');
		$newRow->hrefimage = htmlspecialchars($markerhrefimage, ENT_QUOTES, 'UTF-8');
		

		if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
		  &&($contactDoInsert == 1))
		{
			$newContactRow->name = str_replace('\\','', htmlspecialchars($contact_name, ENT_NOQUOTES, 'UTF-8'));
			if ($newContactRow->name == "")
			{
				$newContactRow->name = $newRow->title;
			}
			$newContactRow->con_position = str_replace('\\','', htmlspecialchars($contact_position, ENT_NOQUOTES, 'UTF-8'));
			$newContactRow->telephone = str_replace('\\','', htmlspecialchars($contact_phone, ENT_NOQUOTES, 'UTF-8'));
			$newContactRow->mobile = str_replace('\\','', htmlspecialchars($contact_mobile, ENT_NOQUOTES, 'UTF-8'));
			$newContactRow->fax = str_replace('\\','', htmlspecialchars($contact_fax, ENT_NOQUOTES, 'UTF-8'));
			$newContactRow->email_to = str_replace('\\','', htmlspecialchars($contact_email, ENT_NOQUOTES, 'UTF-8'));
			$newContactRow->address = str_replace('\\','', htmlspecialchars($contact_address, ENT_NOQUOTES, 'UTF-8'));
		}
		
		if ($_POST['marker_action'] == "insert")
		{
			if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
			  &&($contactDoInsert == 1))
			{
				$dml_contact_result = $db->insertObject( '#__contact_details', $newContactRow, 'id' );
				
				$newRow->contactid = $newContactRow->id;
			}

			// 9.03.2015 set creation date
			$newRow->createddate = JFactory::getDate()->toSQL();
			
			$dml_result = $db->insertObject( '#__zhbaidumaps_markers', $newRow, 'id' );
		}
		else
		{
			if ((isset($map->usercontact) && (int)$map->usercontact == 1) 
			  &&($contactDoInsert == 1))
			{
				if (isset($newContactRow->id))
				{
					$dml_contact_result = $db->updateObject( '#__contact_details', $newContactRow, 'id' );
				}
				else
				{
					$dml_contact_result = $db->insertObject( '#__contact_details', $newContactRow, 'id' );
					$newRow->contactid = $newContactRow->id;
				}
			}

			$dml_result = $db->updateObject( '#__zhbaidumaps_markers', $newRow, 'id' );
			//$scripttext .= 'alert("Updated");'."\n";
		}
		
		if ((!$dml_result) || 
			(isset($map->usercontact) && (int)$map->usercontact == 1 && ($contactDoInsert == 1) && (!$dml_result))
			)
		{
			//$this->setError($db->getErrorMsg());
			$scripttext .= 'alert("Error (Insert New Marker or Update): " + "' . $db->escape($db->getErrorMsg()).'");';
		}
		else
		{
			//$scripttext .= 'alert("Complete, redirect");'."\n";
			$scripttext .= 'window.location = "'.JURI::current().'";'."\n";
			
			$new_id = $newRow->id;

		}
	}
	else if (isset($_POST['marker_action']) && $_POST['marker_action'] == "delete") 
	{

		$contactid = substr($_POST["contactid"], 0, 100);
		$markerid = substr($_POST["markerid"], 0, 100);
	
		if (isset($map->usercontact) && (int)$map->usercontact == 1) 
		{
		
			if ((int)$contactid != 0)
			{
				$query = $db->getQuery(true);

				$db->setQuery( 'DELETE FROM `#__contact_details` '.
				'WHERE `id`='.(int)$contactid);
				
				if (!$db->query()) {
					//$this->setError($db->getErrorMsg());
					$scripttext .= 'alert("Error (Delete Exist Marker Contact): " + "' . $db->escape($db->getErrorMsg()).'");';
				}
			}
		}


		$query = $db->getQuery(true);

		$db->setQuery( 'DELETE FROM `#__zhbaidumaps_markers` '.
		'WHERE `createdbyuser`='.$currentUserID.
		' and `id`='.$markerid);

		
		if (!$db->query()) {
			//$this->setError($db->getErrorMsg());
			$scripttext .= 'alert("Error (Delete Exist Marker): " + "' . $db->escape($db->getErrorMsg()).'");';
		}
		else
		{
			$scripttext .= 'window.location = "'.JURI::current().'";'."\n";
		}
	}
$scripttext .= '</script>';


	echo $scripttext;

}
else
{
// main part where not post data


if ($apiversion != "")
{
	if (($apiversion == '3') 
		||($apiversion == '3.exp'))
	{
		$feature4control = 2;
	}
	else
	{
		if (version_compare($apiversion, '3.22') >= 0)
		{
			$feature4control = 2;
		}
		else
		{
			$feature4control = 1;
		}
	}
}
else
{
	$feature4control = 2;
}	

$credits ='';

if ($licenseinfo == "")
{
  $licenseinfo = 8;
}

if ($compatiblemode == "")
{
  $compatiblemode = 0;
}
if ($compatiblemodersf == "")
{
  $compatiblemodersf = 0;
}

if (isset($placemarkTitleTag) && $placemarkTitleTag != "")
{
	if ($placemarkTitleTag == "h2"
	 || $placemarkTitleTag == "h3")
	{
		// it's OK. Do not change it
		//$placemarkTitleTag = $placemarkTitleTag;
	}
	else
	{
		$placemarkTitleTag ='h2';
	}
}
else
{
	$placemarkTitleTag ='h2';
}

if ($compatiblemodersf == 0)
{
	$imgpathIcons = JURI::root() .'administrator/components/com_zhbaidumap/assets/icons/';
	$imgpathUtils = JURI::root() .'administrator/components/com_zhbaidumap/assets/utils/';
	$directoryIcons = 'administrator/components/com_zhbaidumap/assets/icons/';

	$imgpath4size = JPATH_ADMINISTRATOR .'/components/com_zhbaidumap/assets/icons/';
}
else
{
	$imgpathIcons = JURI::root() .'components/com_zhbaidumap/assets/icons/';
	$imgpathUtils = JURI::root() .'components/com_zhbaidumap/assets/utils/';
	$directoryIcons = 'components/com_zhbaidumap/assets/icons/';

	$imgpath4size = JPATH_SITE .'/components/com_zhbaidumap/assets/icons/';
}


$currentPlacemarkCenter = "do not change";
$currentPlacemarkAction = "do not change";
$currentPlacemarkActionID = "do not change";

if ($centerplacemarkid != "")
{
	$currentPlacemarkCenter = $centerplacemarkid;
        
}

if ($centerplacemarkactionid != "")
{
	$currentPlacemarkActionID = $centerplacemarkactionid;
}

if ($centerplacemarkaction != "")
{
	$currentPlacemarkAction = str_replace(',', ';', $centerplacemarkaction);
}





$document->addStyleSheet(JURI::root() .'components/com_zhbaidumap/assets/css/common.css');


if (isset($map->css2load) && ($map->css2load != ""))
{
	$loadCSSList = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $map->css2load));


	for($i = 0; $i < count($loadCSSList); $i++) 
	{
		$currCSS = trim($loadCSSList[$i]);
		if ($currCSS != "")
		{
			$document->addStyleSheet($currCSS);
		}
	}
}

if (isset($map->js2load) && ($map->js2load != ""))
{
	$loadJSList = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $map->js2load));


	for($i = 0; $i < count($loadJSList); $i++) 
	{
		$currJS = trim($loadJSList[$i]);
		if ($currJS != "")
		{
			$document->addScript($currJS);
		}
	}
}


// Overrides - begin
if (isset($map->override_id) && (int)$map->override_id != 0) 
{
	$fv_override = comZhBaiduMapPlacemarksHelper::get_MapOverrides($map->override_id);
	if (isset($fv_override))
	{
		if ((isset($fv_override->placemark_list_title) && $fv_override->placemark_list_title != ""))
		{
			$fv_override_placemark_title = $fv_override->placemark_list_title;
		}
		else
		{
			$fv_override_placemark_title = JText::_( 'COM_ZHBAIDUMAP_MARKERLIST_SEARCH_FIELD');
		}
		if ((isset($fv_override->placemark_list_button_title) && $fv_override->placemark_list_button_title != ""))
		{
			$fv_override_placemark_button_title = $fv_override->placemark_list_button_title;
		}
		else
		{
			$fv_override_placemark_button_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
		}
		if ((isset($fv_override->placemark_list_button_hint) && $fv_override->placemark_list_button_hint != ""))
		{
			$fv_override_placemark_button_tooltip = $fv_override->placemark_list_button_hint;
		}
		else
		{
			$fv_override_placemark_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
		}
		
		// panel
		if ((isset($fv_override->panelcontrol_hint) && $fv_override->panelcontrol_hint != ""))
		{
			$fv_override_panel_button_tooltip = $fv_override->panelcontrol_hint;
		}
		else
		{
			$fv_override_panel_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PANELCONTROL_LABEL');
		}
		if ((isset($fv_override->panel_detail_title) && $fv_override->panel_detail_title != ""))
		{
			$fv_override_panel_detail_title = $fv_override->panel_detail_title;
		}
		else
		{
			$fv_override_panel_detail_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_DETAIL_TITLE');
		}
		if ((isset($fv_override->panel_placemarklist_title) && $fv_override->panel_placemarklist_title != ""))
		{
			$fv_override_panel_placemarklist_title = $fv_override->panel_placemarklist_title;
		}
		else
		{
			$fv_override_panel_placemarklist_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
		}
		if ((isset($fv_override->panel_route_title) && $fv_override->panel_route_title != ""))
		{
			$fv_override_panel_route_title = $fv_override->panel_route_title;
		}
		else
		{
			$fv_override_panel_route_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_ROUTE_TITLE');
		}		
		if ((isset($fv_override->panel_group_title) && $fv_override->panel_group_title != ""))
		{
			$fv_override_panel_group_title = $fv_override->panel_group_title;
		}
		else
		{
			$fv_override_panel_group_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_GROUP_TITLE');
		}

		if ((isset($fv_override->group_list_title) && $fv_override->group_list_title != ""))
		{
			$fv_override_group_title = $fv_override->group_list_title;
		}
		else
		{
			$fv_override_group_title = JText::_( 'COM_ZHBAIDUMAP_GROUPLIST_SEARCH_FIELD');
		}                              
                
 		if ((isset($fv_override->placemark_list_search) && $fv_override->placemark_list_search != ""))
		{
			$fv_override_placemark_list_search = (int)$fv_override->placemark_list_search;
		}
		else
		{
			$fv_override_placemark_list_search = 0;
		}               
                                
 		if ((isset($fv_override->placemark_list_mapping_type) && $fv_override->placemark_list_mapping_type != ""))
		{
			$fv_override_placemark_list_mapping_type = (int)$fv_override->placemark_list_mapping_type;
		}
		else
		{
			$fv_override_placemark_list_mapping_type = 0;
		}      

 		if ((isset($fv_override->placemark_list_accent_side) && $fv_override->placemark_list_accent_side != ""))
		{
			$fv_override_placemark_list_accent_side = (int)$fv_override->placemark_list_accent_side;
		}
		else
		{
			$fv_override_placemark_list_accent_side = 0;
		} 
                                             

 		if ((isset($fv_override->placemark_list_mapping) && $fv_override->placemark_list_mapping != ""))
		{
			$fv_override_placemark_list_mapping = $fv_override->placemark_list_mapping;
		}
		else
		{
			$fv_override_placemark_list_mapping = ""; 
		}  
 
  		if ((isset($fv_override->placemark_list_accent) && $fv_override->placemark_list_accent != ""))
		{
			$fv_override_placemark_list_accent = $fv_override->placemark_list_accent;
		}
		else
		{
			$fv_override_placemark_list_accent = ""; 
		}    
                
                //
 		if ((isset($fv_override->group_list_search) && $fv_override->group_list_search != ""))
		{
			$fv_override_group_list_search = (int)$fv_override->group_list_search;
		}
		else
		{
			$fv_override_group_list_search = 0;
		} 
                
                if ((isset($fv_override->group_list_mapping_type) && $fv_override->group_list_mapping_type != ""))
		{
			$fv_override_group_list_mapping_type = (int)$fv_override->group_list_mapping_type;
		}
		else
		{
			$fv_override_group_list_mapping_type = 0;
		}      

 		if ((isset($fv_override->group_list_accent_side) && $fv_override->group_list_accent_side != ""))
		{
			$fv_override_group_list_accent_side = (int)$fv_override->group_list_accent_side;
		}
		else
		{
			$fv_override_group_list_accent_side = 0;
		} 
                                             

 		if ((isset($fv_override->group_list_mapping) && $fv_override->group_list_mapping != ""))
		{
			$fv_override_group_list_mapping = $fv_override->group_list_mapping;
		}
		else
		{
			$fv_override_group_list_mapping = ""; 
		}  
 
  		if ((isset($fv_override->group_list_accent) && $fv_override->group_list_accent != ""))
		{
			$fv_override_group_list_accent = $fv_override->group_list_accent;
		}
		else
		{
			$fv_override_group_list_accent = ""; 
		}  
                //
           
                
                
	}
	else
	{
		$fv_override_placemark_title = JText::_( 'COM_ZHBAIDUMAP_MARKERLIST_SEARCH_FIELD');
		$fv_override_placemark_button_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
		$fv_override_placemark_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
		
		$fv_override_panel_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PANELCONTROL_LABEL');
		$fv_override_panel_detail_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_DETAIL_TITLE');
		$fv_override_panel_placemarklist_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
		$fv_override_panel_route_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_ROUTE_TITLE');
		$fv_override_panel_group_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_GROUP_TITLE');
                
                $fv_override_group_title = JText::_( 'COM_ZHBAIDUMAP_GROUPLIST_SEARCH_FIELD');
                                
                $fv_override_placemark_list_search = 0;
                $fv_override_placemark_list_mapping_type = 0;
                $fv_override_placemark_list_mapping = "";       
                $fv_override_placemark_list_accent = "";
                $fv_override_placemark_list_accent_side = 0;
                
                $fv_override_group_list_search = 0;
                $fv_override_group_list_mapping_type = 0;
                $fv_override_group_list_mapping = ""; 
                $fv_override_group_list_accent = ""; 
                $fv_override_group_list_accent_side = 0;
                
	}	
}
else
{
	$fv_override_placemark_title = JText::_( 'COM_ZHBAIDUMAP_MARKERLIST_SEARCH_FIELD');
	$fv_override_placemark_button_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
	$fv_override_placemark_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PLACEMARKLIST');
	
	$fv_override_panel_button_tooltip = JText::_( 'COM_ZHBAIDUMAP_MAP_PANELCONTROL_LABEL');
	$fv_override_panel_detail_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_DETAIL_TITLE');
	$fv_override_panel_placemarklist_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_PLACEMARKLIST_TITLE');
	$fv_override_panel_route_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_ROUTE_TITLE');
	$fv_override_panel_group_title = JText::_( 'COM_ZHBAIDUMAP_MAP_PANEL_GROUP_TITLE');
        
        $fv_override_group_title = JText::_( 'COM_ZHBAIDUMAP_GROUPLIST_SEARCH_FIELD');
                
        $fv_override_placemark_list_search = 0;
        $fv_override_placemark_list_mapping_type = 0;
        $fv_override_placemark_list_mapping = "";       
        $fv_override_placemark_list_accent = "";
        $fv_override_placemark_list_accent_side = 0;
        
        $fv_override_group_list_search = 0;
        $fv_override_group_list_mapping_type = 0;
        $fv_override_group_list_mapping = ""; 
        $fv_override_group_list_accent = ""; 
        $fv_override_group_list_accent_side = 0;        
        
}
// Overrides - end



if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{
	$placemarkSearch = (int)$map->markerlistsearch;	
}
else
{
	$placemarkSearch = 0;
}

if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{
	$groupSearch = (int)$map->markergroupsearch;	
}
else
{
	$groupSearch = 0;
}



$managePanelFeature = 0;

if ((isset($map->panelinfowin) && (int)$map->panelinfowin != 0))
{
	$managePanelInfowin = 1;
}
else
{
	$managePanelInfowin = 0;
}


if (($managePanelInfowin ==1)
||((isset($map->markerlistpos) && (int)$map->markerlistpos == 120))
||((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120))
)
{
	$managePanelFeature = 1;
}

if ((isset($map->panoramioenable) && (int)$map->panoramioenable > 1) 
 || (isset($map->trafficcontrol) && (int)$map->trafficcontrol > 1) 
 || (isset($map->transitcontrol) && (int)$map->transitcontrol > 1) 
 || (isset($map->bikecontrol) && (int)$map->bikecontrol > 1) 
 || (isset($map->mapcentercontrol) && (int)$map->mapcentercontrol != 0) 
 || (isset($map->markerlistpos) && (int)$map->markerlistpos != 0 && isset($map->markerlistbuttontype) && (int)$map->markerlistbuttontype != 0) 
 ||($managePanelFeature == 1)
)
{
	$layersButtons = 1;
}
else
{
	$layersButtons = 0;
}



$custMapTypeList = explode(";", $map->custommaptypelist);
if (count($custMapTypeList) != 0)
{
	$custMapTypeFirst = $custMapTypeList[0];
}
else
{
	$custMapTypeFirst = 0;
}

$needOverlayControl = 0;

if ((int)$map->overlayopacitycontrol != 0)
{
    if ($needOverlayControl == 0)
    {    
        if ((int)$map->custommaptype != 0)
        {
            foreach ($maptypes as $key => $currentmaptype) 	
            {               
                for ($i=0; $i < count($custMapTypeList); $i++)
                {
                    if ($currentmaptype->id == (int)$custMapTypeList[$i]
                    && $currentmaptype->gettileurl != "")
                    {                              
                        if ((int)$currentmaptype->layertype == 1)
                        {
                            if ((int)$currentmaptype->opacitymanage == 1)
                            {
                                $needOverlayControl = 1;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    
    if ($needOverlayControl == 0)
    {
	if (isset($paths) && !empty($paths)) 
	{
            foreach ($paths as $key => $currentpath) 
            {
                if ($currentpath->imgurl != ""
                    && $currentpath->imgbounds != "") 
                {
                    if ((int)$currentpath->imgopacitymanage == 1)
                    {
                        $needOverlayControl = 1;
                        break;
                    }
                }
            }
        }    
    }
    
}

    
if ($placemarkSearch != 0
    || $groupSearch != 0
    || $managePanelFeature != 0)
{
	$document->addStyleSheet(JURI::root() .'components/com_zhbaidumap/assets/jquery-ui/1.11.4/jquery-ui.min.css');
	$document->addScript(JURI::root() .'components/com_zhbaidumap/assets/jquery-ui/1.11.4/jquery-ui.min.js');
}

if (isset($map->mapbounds) && $map->mapbounds != "")
{
    if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
    {
            $this->area_restriction = 1;
    }
    else
    {
            $area_restriction = 1;
    }
}


if (isset($map->usermarkers) 
  && ((int)$map->usermarkers == 1
	  ||(int)$map->usermarkers == 2)) 
{

	$document->addStyleSheet(JURI::root() .'components/com_zhbaidumap/assets/css/usermarkers.css');	

}


// Extra checking - begin

	$featurePathElevation = 0;
	$featurePathElevationKML = 0;
	// Do you need Elevation feature
        /*
	if (isset($paths) && !empty($paths)) 
	{
		foreach ($paths as $key => $currentpath) 
		{
			if (($currentpath->path != ""
			 && (int)$currentpath->objecttype == 0
			 && (int)$currentpath->elevation != 0))
			{
				$featurePathElevation = 1;
				break;
			}
		}
		foreach ($paths as $key => $currentpath) 
		{
			if (($currentpath->kmllayer != ""
			 && (int)$currentpath->elevation != 0))
			{
				$featurePathElevationKML = 1;
				break;
			}
		}
	}
        */

// Extra checking - begin



$fullWidth = 0;
$fullHeight = 0;

// Size Value 
$currentMapWidth ="do not change";
$currentMapHeight ="do not change";

// Map Type Value 
//   add parameter to redefine (passed from plugin)
if (isset($currentMapType) && $currentMapType != "")
{
	$currentMapTypeValue ="";
}
else
{
	$currentMapType ="do not change";
	$currentMapTypeValue ="";
}
		
if ($mapMapWidth != "")
{
	$currentMapWidth = $mapMapWidth;
}

if ($mapMapHeight != "")
{
	$currentMapHeight = $mapMapHeight;
}

if ($map->headerhtml != "")
{
        $divmapheader .= '<div id="BDMapInfoHeader'.$mapDivSuffix.'" class="zhbdm-map-header">'.$map->headerhtml;
        if (isset($map->headersep) && (int)$map->headersep == 1) 
        {
            $divmapheader .= '<hr id="mapHeaderLine" />';
        }
        $divmapheader .= '</div>';
}

if ($map->footerhtml != "")
{
       $divmapfooter .= '<div id="BDMapInfoFooter'.$mapDivSuffix.'" class="zhbdm-map-footer">';
        if (isset($map->footersep) && (int)$map->footersep == 1) 
        {
            $divmapfooter .= '<hr id="mapFooterLine" />';
        }
       $divmapfooter .= $map->footerhtml.'</div>';
}

if ($currentMapWidth == "do not change")
{
	$currentMapWidthValue = (int)$map->width;
}
else
{
	$currentMapWidthValue = (int)$currentMapWidth;
}

if ($currentMapHeight == "do not change")
{
	$currentMapHeightValue = (int)$map->height;
}
else
{
	$currentMapHeightValue = (int)$currentMapHeight;
}


if ((!isset($currentMapWidthValue)) || (isset($currentMapWidthValue) && (int)$currentMapWidthValue < 1)) 
{
	$fullWidth = 1;
}
if ((!isset($currentMapHeightValue)) || (isset($currentMapHeightValue) && (int)$currentMapHeightValue < 1)) 
{
	$fullHeight = 1;
}



if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{

	$document->addStyleSheet(JURI::root() .'components/com_zhbaidumap/assets/css/markergroups.css');

	
	
	switch ((int)$map->markergroupcss) 
	{
		
		case 0:
			$markergroupcssstyle = '-simple';
		break;
		case 1:
			$markergroupcssstyle = '-advanced';
		break;
		case 2:
			$markergroupcssstyle = '-external';
		break;
		default:
			$markergroupcssstyle = '-simple';
		break;
	}


       	$divmarkergroup =  '<div id="BDMapsMenu'.$markergroupcssstyle.'" style="margin:0;padding:0;width=100%;">'."\n";
        if ($map->markergrouptitle != "")
        {
            $divmarkergroup .= '<div id="groupList"><h2 id="groupListHeadTitle" class="groupListHead">'.htmlspecialchars($map->markergrouptitle , ENT_QUOTES, 'UTF-8').'</h2></div>';
        }
        
        if ($map->markergroupdesc1 != "")
        {
            $divmarkergroup .= '<div id="groupListBodyTopContent" class="groupListBodyTop">'.htmlspecialchars($map->markergroupdesc1 , ENT_QUOTES, 'UTF-8').'</div>';
        }

        if (isset($map->markergroupsep1) && (int)$map->markergroupsep1 == 1) 
        {
            $divmarkergroup .= '<hr id="groupListLineTop" />';
        }

        
        $divmarkergroup .= '<ul id="zhbdm-menu'.$markergroupcssstyle.'" class="zhbdm-markergroup-group-ul-menu'.$markergroupcssstyle.'">'."\n";

		/* 19.02.2013 
		   for flexible support group management 
		   and have ability to set off placemarks from group managenent 
		   markergroups changed to mgrgrouplist
		   */
		
        if (isset($mgrgrouplist) && !empty($mgrgrouplist)) 
        {

                if (isset($map->markergroupshowiconall) && ((int)$map->markergroupshowiconall!= 100))
                {
                        $imgimg1 = $imgpathUtils.'checkbox1.png';
                        $imgimg0 = $imgpathUtils.'checkbox0.png';

                        switch ((int)$map->markergroupshowiconall) 
                        {

                                case 0:
                                        $divmarkergroup .= '<li id="li-all" class="zhbdm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-all" class="zhbdm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhbdm-markergroup-search'.$mapDivSuffix.'" class="zhbdm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="BDMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                case 1:
                                        $divmarkergroup .= '<li id="li-all" class="zhbdm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-all" class="zhbdm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-img-all" class="zhbdm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg1.'" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'" /></div><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-img-all" class="zhbdm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg0.'" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'" /></div><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";

                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhbdm-markergroup-search'.$mapDivSuffix.'" class="zhbdm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="BDMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }

                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                case 2:
                                        $divmarkergroup .= '<li id="li-all" class="zhbdm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-all" class="zhbdm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-img-all" class="zhbdm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg1.'" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'" /></div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-img-all" class="zhbdm-markergroup-img-all'.$markergroupcssstyle.'"><img src="'.$imgimg0.'" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'" /></div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhbdm-markergroup-search'.$mapDivSuffix.'" class="zhbdm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="BDMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                                default:
                                        $divmarkergroup .= '<li id="li-all" class="zhbdm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-all" class="zhbdm-markergroup-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callShowAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_SHOW').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-div-all" class="zhbdm-markergroup-div-all'.$markergroupcssstyle.'">'."\n";
                                        $divmarkergroup .= '<div id="zhbdm-markergroup-a-all" class="zhbdm-markergroup-a-all'.$markergroupcssstyle.'"><a id="a-all" href="#" onclick="callHideAllGroup'.$mapDivSuffix.'();return false;" class="zhbdm-markergroup-link-all'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-text-all" class="zhbdm-markergroup-text-all'.$markergroupcssstyle.'">'.JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_MARKERGROUPSHOWICONALL_HIDE').'</div></a></div></div>'."\n";
                                        $divmarkergroup .= '</div>'."\n";
                                        if ($groupSearch != 0)
                                        {
                                            $divmarkergroup .= '<div id="zhbdm-markergroup-search'.$mapDivSuffix.'" class="zhbdm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                                            $divmarkergroup .= '<input id="BDMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                                            $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                                            $divmarkergroup .='>';                                   
                                            $divmarkergroup .= '</div>'."\n";
                                        }                                                
                                        $divmarkergroup .= '</li>'."\n";
                                break;
                        }
                }
                else
                {
                    if ($groupSearch != 0)
                    {
                        $divmarkergroup .= '<li id="li-all" class="zhbdm-markergroup-group-li-all'.$markergroupcssstyle.'">'."\n";
                        $divmarkergroup .= '<div id="zhbdm-markergroup-search'.$mapDivSuffix.'" class="zhbdm-markergroup-search'.$markergroupcssstyle.'">'."\n";
                        $divmarkergroup .= '<input id="BDMapsGroupListSearchAutocomplete'.$mapDivSuffix.'"';
                        $divmarkergroup .= ' placeholder="'.$fv_override_group_title.'"';
                        $divmarkergroup .='>';                                   
                        $divmarkergroup .= '</div>'."\n";
                        $divmarkergroup .= '</li>'."\n";                    
                    }                                                
                }


                foreach ($mgrgrouplist as $key => $currentmarkergroup) 
                {
                        if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
                        {
                                $imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarkergroup->icontype).'.png';

                                $markergroupname ='';
                                $markergroupname = 'markergroup'. $currentmarkergroup->id;

                                $markergroupname_article = 'markergroup'.$mapDivSuffix.'_'. $currentmarkergroup->id;

                                if ((int)$currentmarkergroup->activeincluster == 1)
                                {
                                        $markergroupactive = ' active';
                                }
                                else
                                {
                                        $markergroupactive = '';
                                }



                                switch ((int)$map->markergroupshowicon) 
                                {

                                        case 0:
                                                $divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhbdm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-a-'.$markergroupname.'" class="zhbdm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhbdm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhbdm-markergroup-text-'.$markergroupname.'" class="zhbdm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
                                        break;
                                        case 1:
                                                $divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhbdm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-a-'.$markergroupname.'" class="zhbdm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhbdm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhbdm-markergroup-img-'.$markergroupname.'" class="zhbdm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" /></div><div id="zhbdm-markergroup-text-'.$markergroupname.'" class="zhbdm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
                                        break;
                                        case 2:
                                                $divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhbdm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-a-'.$markergroupname.'" class="zhbdm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhbdm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhbdm-markergroup-img-'.$markergroupname.'" class="zhbdm-markergroup-img'.$markergroupcssstyle.'"><img src="'.$imgimg.'" alt="" /></div></a></div></li>'."\n";
                                        break;
                                        default:
                                                $divmarkergroup .= '<li id="li-'.$markergroupname.'" class="zhbdm-markergroup-group-li'.$markergroupcssstyle.'"><div id="zhbdm-markergroup-a-'.$markergroupname.'" class="zhbdm-markergroup-a'.$markergroupcssstyle.'"><a id="a-'.$markergroupname_article.'" href="#" onclick="callToggleGroup'.$mapDivSuffix.'('.$currentmarkergroup->id.');return false;" class="zhbdm-markergroup-link'.$markergroupcssstyle.$markergroupactive.'"><div id="zhbdm-markergroup-text-'.$markergroupname.'" class="zhbdm-markergroup-text'.$markergroupcssstyle.'">'.htmlspecialchars($currentmarkergroup->title, ENT_QUOTES, 'UTF-8').'</div></a></div></li>'."\n";
                                        break;
                                }


                        }
                }
        }


        $divmarkergroup .= '</ul>'."\n";

        if (isset($map->markergroupsep2) && (int)$map->markergroupsep2 == 1) 
        {
            $divmarkergroup .= '<hr id="groupListLineBottom" />';
        }
        
        if ($map->markergroupdesc2 != "")
        {
            $divmarkergroup .= '<div id="groupListBodyBottomContent" class="groupListBodyBottom">'.htmlspecialchars($map->markergroupdesc2 , ENT_QUOTES, 'UTF-8').'</div>';
        }
        
        $divmarkergroup .= '</div>'."\n";

}




$zhbdmObjectManager = 0;
$ajaxLoadContent = 0;
$ajaxLoadScripts = 0;

$ajaxLoadObjects = (int)$map->useajaxobject;

$ajaxLoadObjectType = (int)$map->ajaxgetplacemark;

$featureSpider = 0; //(int)$map->markerspinner;

if (
 (isset($map->useajax) && ((int)$map->useajax !=0))
 || 
 (isset($map->placemark_rating) && ((int)$map->placemark_rating != 0))
)
{
	$ajaxLoadContent = 1;
}

if ($ajaxLoadObjects != 0)
{
	$ajaxLoadScripts = 1;
}

if (  ($ajaxLoadObjects != 0)
   || ($ajaxLoadContent != 0)
   || ($featureSpider != 0)
   || ($placemarkSearch != 0)
   || ($groupSearch != 0)
   || ($needOverlayControl != 0)
   || ($managePanelFeature != 0)
   || ($layersButtons != 0)
   || (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
   || (isset($map->markercluster) && (int)$map->markercluster == 1)
   || (isset($map->mapbounds) && $map->mapbounds != "")
   || (//((isset($map->elevation) && (int)$map->elevation == 1)) ||
   	   $featurePathElevation == 1 || $featurePathElevationKML == 1)
   || (isset($map->hovermarker) && ((int)$map->hovermarker !=0))
   )
{
	$zhbdmObjectManager = 1;
}





if (($ajaxLoadScripts != 0)
    ||  
   (isset($map->hovermarker) && ((int)$map->hovermarker == 2)))
{

		if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
		{
			$this->infobubble = 1;
		}
		else
		{
			$infobubble = 1;
		}
}
else
{
	if (isset($markers) && !empty($markers)) 
	{
		foreach ($markers as $key => $currentmarker) 
		{
			if ((int)$currentmarker->actionbyclick == 4)
			{

				if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
				{
					$this->infobubble = 1;
				}
				else
				{
					$infobubble = 1;
				}
				break; 
			}
		}
	}
}


if ($ajaxLoadScripts != 0) 
{
		if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
		{
			$this->featureMarkerWithLabel = 1;
		}
		else
		{
			$featureMarkerWithLabel = 1;
		}
}
else
{
	if (isset($markers) && !empty($markers)) 
	{
		foreach ($markers as $key => $currentmarker) 
		{
			if ((int)$currentmarker->baloon == 21
			 || (int)$currentmarker->baloon == 22
			 || (int)$currentmarker->baloon == 23
			 )
			{
				if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
				{
					$this->featureMarkerWithLabel = 1;
				}
				else
				{
					$featureMarkerWithLabel = 1;
				}

				break; 
			}
		}
	}
}

$document->addScript($current_custom_js_path.'common-min.js');
if ($zhbdmObjectManager != 0)
{
		if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
		{
			$this->use_object_manager = 1;
		}
		else
		{
			$use_object_manager = 1;
		}
}


$divmap = "";


if ((int)$map->routedriving == 2
   || ((int)$map->routewalking == 0 
	&& (int)$map->routetransit == 0
	&& (int)$map->routebicycling == 0))
{
	$routeSelectedDriving = ' selected="selected"';
	$routeSelectedWalking = '';
	$routeSelectedTransit = '';
	$routeSelectedBicycling = '';
}
else
{
	if ((int)$map->routedriving == 2)
	{
		$routeSelectedDriving = ' selected="selected"';
		$routeSelectedWalking = '';
		$routeSelectedTransit = '';
		$routeSelectedBicycling = '';
	}
	else if ((int)$map->routewalking == 2)
	{
		$routeSelectedDriving = '';
		$routeSelectedWalking = ' selected="selected"';
		$routeSelectedTransit = '';
		$routeSelectedBicycling = '';
	}
	else if ((int)$map->routetransit == 2)
	{
		$routeSelectedDriving = '';
		$routeSelectedWalking = '';
		$routeSelectedTransit = ' selected="selected"';
		$routeSelectedBicycling = '';
	}
	else if ((int)$map->routebicycling == 2)
	{
		$routeSelectedDriving = '';
		$routeSelectedWalking = '';
		$routeSelectedTransit = '';
		$routeSelectedBicycling = ' selected="selected"';
	}
	else
	{
		if ((int)$map->routedriving != 0)
		{
			$routeSelectedDriving = ' selected="selected"';
			$routeSelectedWalking = '';
			$routeSelectedTransit = '';
			$routeSelectedBicycling = '';
		}
		else if ((int)$map->routewalking != 0)
		{
			$routeSelectedDriving = '';
			$routeSelectedWalking = ' selected="selected"';
			$routeSelectedTransit = '';
			$routeSelectedBicycling = '';
		}
		else if ((int)$map->routetransit != 0)
		{
			$routeSelectedDriving = '';
			$routeSelectedWalking = '';
			$routeSelectedTransit = ' selected="selected"';
			$routeSelectedBicycling = '';
		}
		else if ((int)$map->routebicycling != 0)
		{
			$routeSelectedDriving = '';
			$routeSelectedWalking = '';
			$routeSelectedTransit = '';
			$routeSelectedBicycling = ' selected="selected"';
		}
		else
		{
			$routeSelectedDriving = '';
			$routeSelectedWalking = '';
			$routeSelectedTransit = '';
			$routeSelectedBicycling = '';
		}
	}
}


$doShowDivGeo = 0;

$divmapbefore = "";
$divmapafter = "";

$service_DoDirection = 0;

$doShowDivFind = 0;

	$divwrapmapstyle = '';
	$divtabcolmapstyle = '';
	
	if ($fullWidth == 1)
	{
		$divwrapmapstyle .= 'width:100%;';
	}
	if ($fullHeight == 1)
	{
		$divwrapmapstyle .= 'height:100%;';
		$divtabcolmapstyle .= 'height:100%;';
	}
	if ($divwrapmapstyle != "")
	{
		$divwrapmapstyle = 'style="'.$divwrapmapstyle.'"';
	}
	if ($divtabcolmapstyle != "")
	{
		$divtabcolmapstyle = 'style="'.$divtabcolmapstyle.'"';
	}

// adding markerlist (div)
$markerlistcssstyle = '';
if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{


	$document->addStyleSheet(JURI::root() .'components/com_zhbaidumap/assets/css/markerlists.css');
	
	
	switch ((int)$map->markerlist) 
	{
		
		case 0:
			$markerlistcssstyle = 'markerList-simple';
		break;
		case 1:
			$markerlistcssstyle = 'markerList-advanced';
		break;
		case 2:
			$markerlistcssstyle = 'markerList-external';
		break;
		default:
			$markerlistcssstyle = 'markerList-simple';
		break;
	}


	$markerlistAddStyle ='';
	
	if ($map->markerlistbgcolor != "")
	{
		$markerlistAddStyle .= ' background: '.$map->markerlistbgcolor.';';
	}
	
	if ((int)$map->markerlistwidth == 0)
	{
		if ((int)$map->markerlistpos == 113
		  ||(int)$map->markerlistpos == 114
		  ||(int)$map->markerlistpos == 120
		  ||(int)$map->markerlistpos == 121)
		{
			$divMarkerlistWidth = '100%';
		}
		else
		{
			$divMarkerlistWidth = '200px';
		}
	}
	else
	{
		$divMarkerlistWidth = $map->markerlistwidth;
		$divMarkerlistWidth = $divMarkerlistWidth. 'px';
	}


	if ((int)$map->markerlistpos == 111
	  ||(int)$map->markerlistpos == 112)
	{
		if ($fullHeight == 1)
		{
			$divMarkerlistHeight = '100%';
		}
		else
		{
			$divMarkerlistHeight = $currentMapHeightValue;
			$divMarkerlistHeight = $divMarkerlistHeight. 'px';
		}
	}
	else
	{
		if ((int)$map->markerlistheight == 0)
		{
			$divMarkerlistHeight = 200;
		}
		else
		{
			$divMarkerlistHeight = $map->markerlistheight;
		}
		$divMarkerlistHeight = $divMarkerlistHeight. 'px';
	}		

	
	if ((int)$map->markerlistcontent < 100) 
	{
		$markerlisttag = '<div id="BDMapsMarkerListMain" '.$mapDivSuffix.' class="zhbdm-listmain-ul-'.$markerlistcssstyle.'">';
		$markerlisttag .= '<ul id="BDMapsMarkerUL'.$mapDivSuffix.'" class="zhbdm-ul-'.$markerlistcssstyle.'"></ul>';
		$markerlisttag .= '</div>';
	}
	else 
	{
		$markerlisttag = '<div id="BDMapsMarkerListMain" '.$mapDivSuffix.' class="zhbdm-listmain-table-'.$markerlistcssstyle.'">';
		$markerlisttag .=  '<table id="BDMapsMarkerTABLE'.$mapDivSuffix.'" class="zhbdm-ul-table-'.$markerlistcssstyle.'" ';
		if (((int)$map->markerlistpos == 113) 
		|| ((int)$map->markerlistpos == 114) 
		|| ((int)$map->markerlistpos == 120) 
		|| ((int)$map->markerlistpos == 121))
		{
			if ($fullWidth == 1) 
			{
				$markerlisttag .= 'style="width:100%;" ';
			}
		}
		$markerlisttag .= '>';
		$markerlisttag .= '<tbody id="BDMapsMarkerTABLEBODY'.$mapDivSuffix.'" class="zhbdm-ul-tablebody-'.$markerlistcssstyle.'">';
		$markerlisttag .= '</tbody>';
		$markerlisttag .= '</table>';
		$markerlisttag .= '</div>';
	}

	if ($placemarkSearch != 0)
	{
		if ((int)$map->markerlistpos == 120)
		{
			$markerlistsearch = '<div id="BDMapsMarkerListSearch" '.$mapDivSuffix.' class="zhbdm-search-panel-'.$markerlistcssstyle.'"';
		}
		else
		{
			$markerlistsearch = '<div id="BDMapsMarkerListSearch" '.$mapDivSuffix.' class="zhbdm-search-'.$markerlistcssstyle.'"';
		}

		$markerlistsearch .='>';
		$markerlistsearch .= '<input id="BDMapsMarkerListSearchAutocomplete'.$mapDivSuffix.'"';
		$markerlistsearch .= ' placeholder="'.$fv_override_placemark_title.'"';
		$markerlistsearch .='>';
		$markerlistsearch .= '</div>';
	}
	else
	{
		$markerlistsearch = "";
	}
	
	// Add Placemark Search 
	$markerlisttag = $markerlistsearch . $markerlisttag;

	if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
	{
		$markerlistPanel = '';
	}	
	
	switch ((int)$map->markerlistpos) 
	{
		case 0:
			// None
		break;
		case 1:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 2:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 3:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 4:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 5:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 6:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 7:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 8:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 9:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 10:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 11:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 12:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 5px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 111:
			if ($fullWidth == 1) 
			{
				$divmap .= '<table id="BDMMapTable'.$mapDivSuffix.'" class="zhbdm-table-'.$markerlistcssstyle.'" style="width:100%;" >';
			}
			else
			{
				$divmap .= '<table id="BDMMapTable'.$mapDivSuffix.'" class="zhbdm-table-'.$markerlistcssstyle.'" >';
			}
			$divmap .= '<tbody>';
			$divmap .= '<tr>';
			$divmap .= '<td style="width:'.$divMarkerlistWidth.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' float: left; padding: 0; margin: 0 10px 0 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
			$divmap .= '</td>';
			$divmap .= '<td>';
		break;
		case 112:
			if ($fullWidth == 1) 
			{
				$divmap .= '<table id="BDMMapTable'.$mapDivSuffix.'" class="zhbdm-table-'.$markerlistcssstyle.'" style="width:100%;" >';
			}
			else
			{
				$divmap .= '<table id="BDMMapTable'.$mapDivSuffix.'" class="zhbdm-table-'.$markerlistcssstyle.'" >';
			}
			$divmap .= '<tbody>';
			$divmap .= '<tr>';
			$divmap .= '<td>';
		break;
		case 113:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'" >';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
		break;
		case 114:
			$divmap .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-'.$markerlistcssstyle.'" >';
		break;
		case 120:
		    // no height
			// new classes
			$markerlistPanel .= '<div id="BDMMapWrapper" '.$divwrapmapstyle.' class="zhbdm-wrap-panel-'.$markerlistcssstyle.'">';
			$markerlistPanel .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-panel-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0px; width: 100%;">'.$markerlisttag.'</div>';
			$markerlistPanel .='</div>';
		break;
		case 121:
		break;
		default:
		break;
	}

	
}



// SIZE - begin
$mainMapDivContentSize = '';
$mainPanelWrapDivContentSize = '';
$mainStreetViewDivContentSize = '';
$managePanelContentHeight = '';

if ($fullWidth == 1)
{
    if ($fullHeight == 1) 
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:70%;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:30%;';
                                break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:70%;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:30%;';
                                break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:100%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                    $mainPanelWrapDivContentSize = 'width:100%;height:100%;';
                    $managePanelContentHeight = '100%';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:100%;height:100%;';
                }
            }

    }
    else
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:'.((int)($currentMapHeightValue / 2)).'px;';                                
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:100%;height:'.((int)($currentMapHeightValue / 2)).'px;';
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                    $mainPanelWrapDivContentSize = 'width:100%;height:'.$currentMapHeightValue.'px;';
                    $managePanelContentHeight = $currentMapHeightValue.'px';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:100%;height:'.$currentMapHeightValue.'px;';
                }
            }

    }		
}
else
{
    if ($fullHeight == 1) 
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:70%;';	
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:30%;';
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:70%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:70%;';	
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:30%;';			
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:100%;';
                                    $managePanelContentHeight = '100%';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                                }                              
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:100%;';
                    $managePanelContentHeight = '100%';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:100%;';	
                }                    

            }

    }
    else
    {
            if (isset($map->streetview))
            {
                    switch ((int)$map->streetview) 
                    {
                            case 2:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.((int)($currentMapHeightValue / 2)).'px;';                                
                            break;
                            case 3:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                                $mainStreetViewDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.((int)($currentMapHeightValue / 2)).'px;';
                            break;
                            default:
                                if ($managePanelFeature == 1)
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                    $managePanelContentHeight = $currentMapHeightValue.'px';
                                }
                                else
                                {
                                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                                }
                            break;
                    }
            }
            else
            {
                if ($managePanelFeature == 1)
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                    $mainPanelWrapDivContentSize = 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                    $managePanelContentHeight = $currentMapHeightValue.'px';
                }
                else
                {
                    $mainMapDivContentSize .= 'width:'.$currentMapWidthValue.'px;height:'.$currentMapHeightValue.'px;';
                }
            }


    }		
}     
// SIZE - end


$mapDivCSSStyle = 'margin:0;padding:0;';
$mapDivCSSStyle0 = $mapDivCSSStyle;

$mapDivCSSClassName = ' class="zhbdm-map-default"';
$mapSVDivCSSClassName = ' class="zhbdm-map-streetview-default"';
$mapPANWDivCSSClassName = ' class="zhbdm-map-mainpanel-wrap-default"';
$mapPANDivCSSClassName = ' class="zhbdm-map-mainpanel-default"';

if (isset($map->cssclassname) && ($map->cssclassname != ""))
{
	$mapDivCSSClassName = ' class="'.$map->cssclassname . $cssClassSuffix . '"';
	$mapSVDivCSSClassName = ' class="'.$map->cssclassname.'-streetview'. $cssClassSuffix . '"';
}
else
{
	if (isset($cssClassSuffix) && ($cssClassSuffix != ""))
	{
		$mapDivCSSClassName = ' class="'. $cssClassSuffix . '"';
		$mapSVDivCSSClassName = ' class="'.'-streetview'. $cssClassSuffix . '"';
	}
}

$managePanelContent = '';

if ($managePanelFeature == 1)
{
	if (isset($map->panelwidth) && (int)$map->panelwidth != 0)
	{
		$managePanelContentWidth = (int)$map->panelwidth;
	}
	else
	{
		$managePanelContentWidth = '300';
	}
	


	//$managePanelContent = '<p>Hello world</p>';
	
	$managePanelContent .= '<div id="BDMapsPanel'.$mapDivSuffix.'" style="overflow:auto; height:'.$managePanelContentHeight.';">';
	$managePanelContent .= '  <ul>';
	if ($managePanelInfowin == 1)	
	{
		$managePanelContent .= '    <li><a href="#BDMapsPanel'.$mapDivSuffix.'tabs-1">'.$fv_override_panel_detail_title.'</a></li>';
	}
	

	if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
	{
		$managePanelContent .= '    <li><a href="#BDMapsPanel'.$mapDivSuffix.'tabs-2">'.$fv_override_panel_placemarklist_title.'</a></li>';
	}

	if (1==2) 
	{
		$managePanelContent .= '    <li><a href="#BDMapsPanel'.$mapDivSuffix.'tabs-3">'.$fv_override_panel_route_title.'</a></li>';
	}

	if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120) 
	{
		$managePanelContent .= '    <li><a href="#BDMapsPanel'.$mapDivSuffix.'tabs-5">'.$fv_override_panel_group_title.'</a></li>';
	}
	
	$managePanelContent .= '  </ul>';
	if ($managePanelInfowin == 1)	
	{
		$managePanelContent .= '  <div id="BDMapsPanel'.$mapDivSuffix.'tabs-1">';
		$managePanelContent .= '  </div>';
	}

	if (isset($map->markerlistpos) && (int)$map->markerlistpos == 120) 
	{
		$managePanelContent .= '  <div id="BDMapsPanel'.$mapDivSuffix.'tabs-2">';
		$managePanelContent .= $markerlistPanel;
		$managePanelContent .= '  </div>';
	}

	if (1==2) 
	{
		$routePanel ='';
		$managePanelContent .= '  <div id="BDMapsPanel'.$mapDivSuffix.'tabs-3">';
		$managePanelContent .= $routePanel;
		$managePanelContent .= '  </div>';
	}	
	
	if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 120) 
	{
		$managePanelContent .= '  <div id="BDMapsPanel'.$mapDivSuffix.'tabs-5">';
		$managePanelContent .= $divmarkergroup;
		$managePanelContent .= '  </div>';
	}
	
	$managePanelContent .= '</div>';

}
else
{
	$managePanelContentWidth = 0;
}

if ($managePanelFeature == 1)
{
	$managePanelWrapBegin = '<div id="BDMapsMainPanelWrap'.$mapDivSuffix.'" '.$mapPANWDivCSSClassName.' style="'.$mapDivCSSStyle;
        $managePanelWrapBegin .= $mainPanelWrapDivContentSize;
        $managePanelWrapBegin .= '">';
	$managePanelDiv = '';
	$managePanelWrapEnd = '</div>';
	$mapDivCSSStyle .= 'display:inline-block;';
}
else
{
	$managePanelWrapBegin = '';
	$managePanelDiv = '';
	$managePanelWrapEnd = '';
}

if ($fullWidth == 1) 
{
	if ($fullHeight == 1) 
	{
		if (isset($map->streetview))
		{
			switch ((int)$map->streetview) 
			{
				case 2:
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
				case 3:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
				break;
				default:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
			}
		}
		else
		{
			$divmap .= $managePanelWrapBegin;
			$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
			$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
									$managePanelFeature,
									$managePanelContentHeight,
									$managePanelContentWidth.'px',
									$mapDivSuffix,
									$mapPANDivCSSClassName,
									$managePanelContent);
									
			$divmap .= $managePanelDiv;
			$divmap .= $managePanelWrapEnd;
		}
		
	}
	else
	{
		if (isset($map->streetview))
		{
			switch ((int)$map->streetview) 
			{
				case 2:
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
				case 3:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';
				break;
				default:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
			}
		}
		else
		{
			$divmap .= $managePanelWrapBegin;
			$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';
					
			$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
									$managePanelFeature,
									$managePanelContentHeight,
									$managePanelContentWidth.'px',
									$mapDivSuffix,
									$mapPANDivCSSClassName,
									$managePanelContent);
									
			$divmap .= $managePanelDiv;
			$divmap .= $managePanelWrapEnd;
		}

	}		
}
else
{
	if ($fullHeight == 1) 
	{
		if (isset($map->streetview))
		{
			switch ((int)$map->streetview) 
			{
				case 2:
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';			
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';	
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;					
				break;
				case 3:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';			
				break;
				default:
				    $divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
			}
		}
		else
		{
			$divmap .= $managePanelWrapBegin;
			$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
			
			$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
									$managePanelFeature,
									$managePanelContentHeight,
									$managePanelContentWidth.'px',
									$mapDivSuffix,
									$mapPANDivCSSClassName,
									$managePanelContent);
											
			$divmap .= $managePanelDiv;
			$divmap .= $managePanelWrapEnd;
		}

	}
	else
	{
		if (isset($map->streetview))
		{
			switch ((int)$map->streetview) 
			{
				case 2:
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';			
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
				case 3:
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
					$divmap .= '<div id="BDMapStreetView'.$mapDivSuffix.'" '.$mapSVDivCSSClassName.' style="'.$mapDivCSSStyle0.$mainStreetViewDivContentSize.'"></div>';			
				break;
				default:
					$divmap .= $managePanelWrapBegin;
					$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
					$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
											$managePanelFeature,
											$managePanelContentHeight,
											$managePanelContentWidth.'px',
											$mapDivSuffix,
                                                                                        $mapPANDivCSSClassName,
                                                                                        $managePanelContent);
											
					$divmap .= $managePanelDiv;
					$divmap .= $managePanelWrapEnd;
				break;
			}
		}
		else
		{
			$divmap .= $managePanelWrapBegin;
			$divmap .= '<div id="BDMapsID'.$mapDivSuffix.'" '.$mapDivCSSClassName.' style="'.$mapDivCSSStyle.$mainMapDivContentSize.'"></div>';			
					
			$managePanelDiv = comZhBaiduMapDivsHelper::get_MapPanelDIV(
									$managePanelFeature,
									$managePanelContentHeight,
									$managePanelContentWidth.'px',
									$mapDivSuffix,
									$mapPANDivCSSClassName,
									$managePanelContent);
											
			$divmap .= $managePanelDiv;
			$divmap .= $managePanelWrapEnd;
		}

	}		
}

// adding markerlist (close div)
if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
{

	switch ((int)$map->markerlistpos) 
	{
		case 0:
			// None
		break;
		case 1:
			$divmap .='</div>';
		break;
		case 2:
			$divmap .='</div>';
		break;
		case 3:
			$divmap .='</div>';
		break;
		case 4:
			$divmap .='</div>';
		break;
		case 5:
			$divmap .='</div>';
		break;
		case 6:
			$divmap .='</div>';
		break;
		case 7:
			$divmap .='</div>';
		break;
		case 8:
			$divmap .='</div>';
		break;
		case 9:
			$divmap .='</div>';
		break;
		case 10:
			$divmap .='</div>';
		break;
		case 11:
			$divmap .='</div>';
		break;
		case 12:
			$divmap .='</div>';
		break;
		case 111:
			$divmap .= '</td>';
			$divmap .= '</tr>';
			$divmap .= '</tbody>';
			$divmap .='</table>';
		break;
		case 112:
			$divmap .= '</td>';
			$divmap .= '<td style="width:'.$divMarkerlistWidth.'">';
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' float: left; padding: 0; margin: 0 0 0 10px; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
			$divmap .= '</td>';
			$divmap .= '</tr>';
			$divmap .= '</tbody>';
			$divmap .='</table>';
		break;
		case 113:
			$divmap .='</div>';
		break;
		case 114:
			$divmap .='<div id="BDMapsMarkerList'.$mapDivSuffix.'" class="zhbdm-list-'.$markerlistcssstyle.'" style="'.$markerlistAddStyle.' display: none; float: left; padding: 0; margin: 0; width:'.$divMarkerlistWidth.'; height:'.$divMarkerlistHeight.';">'.$markerlisttag.'</div>';
			$divmap .='</div>';
		break;
		case 120:
		break;		
		case 121:
		break;
		default:
		break;
	}


}

        
$divmap .= '<div id="BDMapsCredit'.$mapDivSuffix.'" class="zhbdm-credit"></div>';

$divmap .= '<div id="BDMapsLoading'.$mapDivSuffix.'" style="display: none;" ><img class="zhbdm-image-loading" src="'.$imgpathUtils.'loading.gif" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_LOADING').'" /></div>';

$scripthead .= $divmapheader . $currentUserInfo;

// adding route panel in any case
$divmap4route = '<div id="BDMapsMainRoutePanel'.$mapDivSuffix.'" class="zhbdm-map-route-main"><div id="BDMapsMainRoutePanel_Total'.$mapDivSuffix.'" class="zhbdm-map-route-main-total"></div></div>';
$divmap4route .= '<div id="BDMapsRoutePanel'.$mapDivSuffix.'" class="zhbdm-map-route"><div id="BDMapsRoutePanel_Description'.$mapDivSuffix.'" class="zhbdm-map-route-description"></div><div id="BDMapsRoutePanel_Total'.$mapDivSuffix.'" class="zhbdm-map-route-total"></div></div>';

if ($featurePathElevation == 1 || $featurePathElevationKML == 1)
{
	$divmap4route .= '<div id="BDMapsPathPanel'.$mapDivSuffix.'" onmouseout="clearMarkerElevation'.$mapDivSuffix.'(); return false;" class="zhbdm-map-path"></div>';
}


// adding before and after sections
$divmap = $divmapbefore . $divmap . $divmapafter;


$divTabDivMain = '';

if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
{
	switch ((int)$map->markergroupcontrol) 
	{
		
		case 1:
		       if ($fullWidth == 1) 
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		          $divTabDivMain .=  '<tr align="left" >';
				  if ((int)$map->markergroupwidth != 0)
				  {
					  $divTabDivMain .=  '<td valign="top" width="'.(int)$map->markergroupwidth.'%">';
				  }
				  else
				  {
					  $divTabDivMain .=  '<td valign="top" width="20%">';
				  }
       	          $divTabDivMain .=  $divmarkergroup;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		          $divTabDivMain .=  $divmap;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '</tr>';
		       }
		       else
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
                  $divTabDivMain .=  '<tr>';
		          $divTabDivMain .=  '<td valign="top">';
        	      $divTabDivMain .=  $divmarkergroup;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		          $divTabDivMain .=  $divmap;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '</tr>';
                       }
		       $divTabDivMain .=  '</table>';
		break;
		case 2:
		       if ($fullWidth == 1) 
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		       }
		       else
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		       }
		       $divTabDivMain .=  '<tr>';
		       $divTabDivMain .=  '<td valign="top">';
		       $divTabDivMain .=  $divmarkergroup;
		       $divTabDivMain .=  '</td>';
		       $divTabDivMain .=  '</tr>';
		       $divTabDivMain .=  '<tr>';
		       $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		       $divTabDivMain .=  $divmap;
		       $divTabDivMain .=  '</td>';
		       $divTabDivMain .=  '</tr>';
		       $divTabDivMain .=  '</table>';

		break;
		case 3:
		       if ($fullWidth == 1) 
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'">';
		          $divTabDivMain .=  '<tr>';
		          $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		          $divTabDivMain .=  $divmap;
		          $divTabDivMain .=  '</td>';
				  if ((int)$map->markergroupwidth != 0)
				  {
					  $divTabDivMain .=  '<td valign="top" width="'.(int)$map->markergroupwidth.'%">';
				  }
				  else
				  {
					  $divTabDivMain .=  '<td valign="top" width="20%">';
				  }
		          $divTabDivMain .=  $divmarkergroup;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '</tr>';
		       }
		       else
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		          $divTabDivMain .=  '<tr>';
		          $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		          $divTabDivMain .=  $divmap;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '<td valign="top">';
		          $divTabDivMain .=  $divmarkergroup;
		          $divTabDivMain .=  '</td>';
		          $divTabDivMain .=  '</tr>';
		       }
		       $divTabDivMain .=  '</table>';

		break;
		case 4:
		       if ($fullWidth == 1) 
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		       }
		       else
		       {
		          $divTabDivMain .=  '<table class="zhbdm-group-manage" '.$divwrapmapstyle.'>';
		       }
		       $divTabDivMain .=  '<tr>';
		       $divTabDivMain .=  '<td '.$divtabcolmapstyle.'>';
		       $divTabDivMain .=  $divmap;
		       $divTabDivMain .=  '</td>';
		       $divTabDivMain .=  '</tr>';
		       $divTabDivMain .=  '<tr>';
		       $divTabDivMain .=  '<td valign="top">';
		       $divTabDivMain .=  $divmarkergroup;
		       $divTabDivMain .=  '</td>';
		       $divTabDivMain .=  '</tr>';
		       $divTabDivMain .=  '</table>';
		break;
		case 5:
		       $divTabDivMain .=  '<div id="zhbdm-wrapper" '.$divwrapmapstyle.'>';
		       $divTabDivMain .=  $divmarkergroup;
		       $divTabDivMain .=  $divmap;
		       $divTabDivMain .=  '</div>';
		break;
		case 6:
		       $divTabDivMain .=  '<div id="zhbdm-wrapper" '.$divwrapmapstyle.'>';
		       $divTabDivMain .=  $divmap;
		       $divTabDivMain .=  $divmarkergroup;
		       $divTabDivMain .=  '</div>';
		break;
		case 10:
		       $divTabDivMain .=  $divmap;
		break;
		case 120:
		       $divTabDivMain .=  $divmap;
		break;
		default:
			$divTabDivMain .=  $divmap;
		break;
	}


		$scripthead .= $divTabDivMain;
	
}
else
{
		$scripthead .= $divmap;
}



	$scripthead .= $divmapfooter. $divmap4route;

if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
	// all save at the end
}
else
{
	echo $scripthead;
}	
	
$scripttext = '';
$scripttextBegin = '';
$scripttextEnd = '';


//Script begin
$scripttextBegin .= '<script type="text/javascript" >/*<![CDATA[*/' ."\n";

	// Global variable scope (for access from all functions)
	
	$scripttext .= 'var map'.$mapDivSuffix.', infowindow'.$mapDivSuffix.';' ."\n";
	
	
	if ($zhbdmObjectManager != 0)
	{
		$scripttext .= 'var zhbdmObjMgr'.$mapDivSuffix.';' ."\n";
	}
	
    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
	{
		if ((int)$map->hovermarker == 1)
		{
			$scripttext .= 'var hoverinfowindow'.$mapDivSuffix.';' ."\n";
		}
		else if ((int)$map->hovermarker == 2)
		{
			$scripttext .= 'var hoverinfobubble'.$mapDivSuffix.';' ."\n";
		}
	}
	
	$scripttext .= 'var latlng'.$mapDivSuffix.', routeaddress'.$mapDivSuffix.';' ."\n";
	$scripttext .= 'var routedestination'.$mapDivSuffix.', routedirection'.$mapDivSuffix.';' ."\n";
	
	$scripttext .= 'var mapzoom'.$mapDivSuffix.';' ."\n";

	$scripttext .= 'var infobubblemarkers'.$mapDivSuffix.' = [];' ."\n";

	if (isset($map->streetview) && (int)$map->streetview != 0)
	{
		$scripttext .= 'var panorama'.$mapDivSuffix.';' ."\n";
	}	
	
	if ($externalmarkerlink == 1)
	{
		$scripttext .= 'var allPlacemarkArray = [];' ."\n";
	}
	

	if (isset($map->usercontactattributes) && $map->usercontactattributes != "")
	{
		$userContactAttrs = str_replace(";", ',',$map->usercontactattributes);
	}
	else
	{
		$userContactAttrs = str_replace(";", ',', 'name;position;address;phone;mobile;fax;email');
	}
	$scripttext .= 'var userContactAttrs = \''.$userContactAttrs.'\';' ."\n";
	

	$scripttext .= 'var icoIcon=\''.$imgpathIcons.'\';'."\n";
	$scripttext .= 'var icoUtils=\''.$imgpathUtils.'\';'."\n";
	$scripttext .= 'var icoDir=\''.$directoryIcons.'\';'."\n";

	
	if ($zhbdmObjectManager != 0)
	{
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.' = new zhbdmMapObjectManager();' ."\n";

		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMapID('.$map->id.');' ."\n";

                $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkList("'.$placemarklistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setExcludePlacemarkList("'.$explacemarklistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkGroupList("'.$grouplistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkCategotyList("'.$categorylistid.'");' ."\n";
                
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPathList("'.$pathlistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setExcludePathList("'.$expathlistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPathGroupList("'.$pathgrouplistid.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPathCategotyList("'.$pathcategorylistid.'");' ."\n";	
                
                $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setUserMarkersFilter("'.$usermarkersfilter.'");' ."\n";

		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMapLanguageTag("'.$main_lang.'");' ."\n";
		
		
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setServiceDirection('.$service_DoDirection.');' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setIcoIcon(icoIcon);' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setIcoUtils(icoUtils);' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setIcoDir(icoDir);' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setArticleID("'.$mapDivSuffix.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkRating('.(int)$map->placemark_rating.');' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkTitleTag("'.$placemarkTitleTag.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setRequestURL("'.JURI::root().'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkCreationInfo('.(int)$map->showcreateinfo.');' ."\n";

		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setFeature4Control('.$feature4control.');' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPanelInfowin('.$managePanelInfowin.');' ."\n";

		
		if ($ajaxLoadObjects != 0)
		{
                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizePlacemark('.(int)$map->ajaxbufferplacemark.');' ."\n";
                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizePath('.(int)$map->ajaxbufferpath.');' ."\n";
                    //$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setAjaxBufferSizeRoute('.(int)$map->ajaxbufferroute.');' ."\n";
		}

		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setContactAttrs("'.$userContactAttrs.'");' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setUserContact('.(int)$map->usercontact.');' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setUserUser('.(int)$map->useruser.');' ."\n";
		
		if ($needOverlayControl != 0)
		{
                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enableOpacityOverlayControl();' ."\n";
		}
                
		if ($compatiblemode != 0)
		{
                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setCompatibleMode('.(int)$compatiblemode.');' ."\n";
		
		}	
                
                // for centering placemarks
                if ($ajaxLoadObjects != 0) {
                    if ($currentPlacemarkCenter != "do not change") {
                        $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setCenterPlacemark('.(int)$currentPlacemarkCenter.');' ."\n";
    
                    }
                    
                    if ($currentPlacemarkActionID != "do not change")
                    {
                        $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setActionPlacemark('.(int)$currentPlacemarkActionID.');' ."\n";

                        if ($currentPlacemarkAction != "do not change")
                        {
                            $currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);

                            for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
                            {
                                switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
                                {
                                    case "":
                                       // null
                                    break;
                                    case "do not change":
                                            // do not change
                                    break;
                                    case "click":
                                        $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("click");' ."\n";
                                    break;
                                    case "bounce":
                                        $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("bounce");' ."\n";
                                    break;
                                    default:
                                        $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.addActionPlacemarkAction("'. trim($currentPlacemarkExecuteArray[$i]).'");'."\n";
                                    break;
                                }
                            }
                        }


                    }	                    
                }
		
        if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0)
		{
			if (isset($map->markerlistsync) && (int)$map->markerlistsync != 0)
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePlacemarkList();' ."\n";
			}
		}
		
		
	}
    
	if ($managePanelFeature == 1)
	{
            $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePanel();' ."\n";
            if ($fullHeight == 1) 
            {
                $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPanelHeightDeltaFix(5);' ."\n";
            }               
	}


        if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
        {
            foreach ($mgrgrouplist as $key => $currentmarkergroup) 
            {
                if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
                {
                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.addManagedGroup('.$currentmarkergroup->id.', "'.str_replace('"', '\\"',str_replace('\\', '\\\\',$currentmarkergroup->title)).'", "'.str_replace('"', '\\"',str_replace('\\', '\\\\',$currentmarkergroup->description)).'");' ."\n";
                }
            }
        }
	
	
    $scripttext .= 'function initialize'.$mapInitTag.'() {' ."\n";

		// MarkerGroups
		$placemarkGroupArray = array();
		
		if ($zhbdmObjectManager)
		{
			$scripttext .= 'var markerCluster0;' ."\n";

			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.GroupStateDefine(0, 1);' ."\n";
			   
		   if (isset($markergroups) && !empty($markergroups)) 
		   {
				foreach ($markergroups as $key => $currentmarkergroup) 
				{
					$scripttext .= 'var markerCluster'.$currentmarkergroup->id.';' ."\n";
					
					array_push($placemarkGroupArray, $currentmarkergroup->id);
					
					// 24.11.2015 - bugfix - unpublished groups caused error, because there is no link element
					if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
					{
						$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.GroupStateDefine('.$currentmarkergroup->id.', '.(int)$currentmarkergroup->activeincluster.');' ."\n";
					}
				}
		   }


			$scripttext .= 'var pathArray0 = [];' ."\n";
			if (isset($mgrgrouplist) && !empty($mgrgrouplist)) 
			{
				foreach ($mgrgrouplist as $key => $currentmarkergroup) 
				{
					if (!in_array($currentmarkergroup->id, $placemarkGroupArray))
					{
						$scripttext .= 'var markerCluster'.$currentmarkergroup->id.';' ."\n";
						
						// 24.11.2015 - bugfix - unpublished groups caused error, because there is no link element
						if (((int)$currentmarkergroup->published == 1) || ($allowUserMarker == 1))
						{
							$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.GroupStateDefine('.$currentmarkergroup->id.', '.(int)$currentmarkergroup->activeincluster.');' ."\n";
						}
					}
				}
			}
		}

		if (isset($map->useajax) && (int)$map->useajax != 0)
		{
                    $scripttext .= 'var ajaxmarkersLL'.$mapDivSuffix.' = [];' ."\n";
                    $scripttext .= 'var ajaxmarkersADR'.$mapDivSuffix.' = [];' ."\n";

                    $scripttext .= 'var ajaxpaths'.$mapDivSuffix.' = [];' ."\n";
                    $scripttext .= 'var ajaxpathsOVL'.$mapDivSuffix.' = [];' ."\n";

                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                    {
                            $scripttext .= 'var ajaxmarkersLLhover'.$mapDivSuffix.' = [];' ."\n";
                            $scripttext .= 'var ajaxmarkersADRhover'.$mapDivSuffix.' = [];' ."\n";

                    }

                    $scripttext .= 'var ajaxpathshover'.$mapDivSuffix.' = [];' ."\n";                      
			
		}
	 
	//
	$scripttext .= 'var toShowLoading = document.getElementById("BDMapsLoading'.$mapDivSuffix.'");'."\n";
	$scripttext .= '  toShowLoading.style.display = \'block\';'."\n";
	
	$scripttext .= 'routedirection'.$mapDivSuffix.' = 1;'."\n";
	$scripttext .= 'latlng'.$mapDivSuffix.' = new BMap.Point('.$map->longitude.', ' .$map->latitude.');' ."\n";
	
	$scripttext .= 'routeaddress'.$mapDivSuffix.' = "'.$map->routeaddress.'";' ."\n";

	if (isset($map->routeaddress) && $map->routeaddress != "")
	{
		$scripttext .= 'routedestination'.$mapDivSuffix.' = routeaddress'.$mapDivSuffix.';'."\n";
	}
	else
	{
		$scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
	}

	
	if (isset($mapzoom) && (int)$mapzoom != 0)
	{
		$scripttext .= 'mapzoom'.$mapDivSuffix.' = '.$mapzoom.';' ."\n";

		if (((int)$map->mapcentercontrol == 2)
 		  ||((int)$map->mapcentercontrol == 12))
		{
			$ctrl_zoom = $mapzoom;
		}
		else
		{
			$ctrl_zoom = 'do not change';
		}		
	}
	else
	{
		$scripttext .= 'mapzoom'.$mapDivSuffix.' = '.$map->zoom.';' ."\n";

		if (((int)$map->mapcentercontrol == 2)
 		  ||((int)$map->mapcentercontrol == 12))
		{
			$ctrl_zoom = $map->zoom;
		}
		else
		{
			$ctrl_zoom = 'do not change';
		}				
	}
	
	
	
	$scripttext .= 'var myOptions = {' ."\n";

	if (isset($map->minzoom) && (int)$map->minzoom != 0)
	{
		$scripttext .= '  minZoom: '.(int)$map->minzoom.',' ."\n";
	}
	if (isset($map->maxzoom) && (int)$map->maxzoom != 0)
	{
		$scripttext .= '  maxZoom: '.(int)$map->maxzoom.',' ."\n";
	}

	
	// Map type
	if (isset($currentMapType)) 
	{

		if ($currentMapType == "do not change")
		{
			$currentMapTypeValue = $map->maptype;
		}
		else
		{
			$currentMapTypeValue = $currentMapType;
		}

		switch ($currentMapTypeValue) 
		{
			
			case 1:
				$scripttext .= ' mapType: BMAP_NORMAL_MAP' ."\n";
			break;
			case 2:
				$scripttext .= ' mapType: BMAP_SATELLITE_MAP' ."\n";
			break;
			case 3:
				$scripttext .= ' mapType: BMAP_HYBRID_MAP' ."\n";
			break;
			case 4:
				$scripttext .= ' mapType: BMAP_PERSPECTIVE_MAP' ."\n";
			break;
			case 5: 
				// set it later (OSM, OpenStreetMap)
				$scripttext .= ' mapType: BMAP_NORMAL_MAP' ."\n";
			break;
			case 6: 
				// set it later (NZ Topomaps)
				$scripttext .= ' mapType: BMAP_NORMAL_MAP' ."\n";
			break;
			case 7: 
				// set it later (First custom map type)
				$scripttext .= ' mapType: BMAP_NORMAL_MAP' ."\n";
			break;
			case 8: 
				// set it later (OpenTopoMap)
				$scripttext .= ' mapType: BMAP_NORMAL_MAP' ."\n";
			break;
                    
			default:
				$scripttext .= '' ."\n";
			break;
		}
	}
        
	//end of options
	$scripttext .= '};' ."\n";
		

        $scripttext .= 'map'.$mapDivSuffix.' = new BMap.Map(document.getElementById("BDMapsID'.$mapDivSuffix.'"), myOptions);' ."\n";
	$scripttext .= 'infowindow'.$mapDivSuffix.' = new BMap.InfoWindow();' ."\n";
	
	// Map is created

        $scripttext .= 'map'.$mapDivSuffix.'.centerAndZoom(latlng'.$mapDivSuffix.', mapzoom'.$mapDivSuffix.');' ."\n";


	
	if (isset($map->draggable) && (int)$map->draggable == 1)
	{
		$scripttext .= 'map'.$mapDivSuffix.'.enableDragging();' ."\n";
	}
	else
	{
		$scripttext .= 'map'.$mapDivSuffix.'.disableDragging();' ."\n";
	}
	
	//Double Click Zoom
	if (isset($map->doubleclickzoom) && (int)$map->doubleclickzoom == 1) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.enableDoubleClickZoom();' ."\n";
	} 
	else 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.disableDoubleClickZoom();' ."\n";
	}

	//Scroll Wheel Zoom		
	if (isset($map->scrollwheelzoom) && (int)$map->scrollwheelzoom == 1) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.enableScrollWheelZoom();' ."\n";
	} 
	else 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.disableScrollWheelZoom();' ."\n";
	}

        if (isset($map->disableautopan) && ((int)$map->disableautopan == 1))	
	{
		$scripttext .= 'infowindow'.$mapDivSuffix.'.disableAutoPan();'."\n";
	}	
		
	if (isset($map->maptypecontrol) && (int)$map->maptypecontrol != 0) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.addControl(new BMap.MapTypeControl({';
                $ctrl_type = "";
		switch ((int)$map->maptypecontrol) 
		{
                    case 1:
                            $ctrl_type = "\n".'  type:  BMAP_MAPTYPE_CONTROL_HORIZONTAL';
                    break;
                    case 2:
                            $ctrl_type = "\n".'  type:  BMAP_MAPTYPE_CONTROL_DROPDOWN';
                    break;
                    case 3:
                            $ctrl_type = "\n".'  type:  BMAP_MAPTYPE_CONTROL_MAP';
                    break;
                    case 9:
                            $ctrl_type = '';
                    break;
                    default:
                            $ctrl_type = '';
                    break;
		}                
		$ctrl_opt = comZhBaiduMapMapsHelper::get_control_position_option(
							$map->maptypepos, 
							$map->maptypeofsx, 
							$map->maptypeofsy);
 		if ($ctrl_opt != "")
		{
                    if ($ctrl_type != "")
                    {
                        $scripttext .= $ctrl_type. ','. $ctrl_opt;
                    }
                    else
                    {
                        $scripttext .= $ctrl_opt;
                    }		
		}
                else
                {
                    $scripttext .= $ctrl_type;
                }               
		$scripttext .= "\n".'}));' ."\n";
	} 


	//Scale Control
	if (isset($map->scalecontrol) && (int)$map->scalecontrol == 1) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.addControl(new BMap.ScaleControl({'.
						comZhBaiduMapMapsHelper::get_control_position_option(
							$map->scalepos, 
							$map->scaleofsx, 
							$map->scaleofsy).
						"\n".'}));' ."\n";
	} 

	//Navigation Control
	if (isset($map->navigationcontrol) && (int)$map->navigationcontrol != 0) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.addControl(new BMap.NavigationControl({';
                $ctrl_type = "";
		switch ((int)$map->navigationcontrol) 
		{
                    case 1:
                            $ctrl_type = "\n".'  type:  BMAP_NAVIGATION_CONTROL_LARGE';
                    break;
                    case 2:
                            $ctrl_type = "\n".'  type:  BMAP_NAVIGATION_CONTROL_SMALL';
                    break;
                    case 3:
                            $ctrl_type = "\n".'  type:  BMAP_NAVIGATION_CONTROL_PAN';
                    break;
                    case 4:
                            $ctrl_type = "\n".'  type:  BMAP_NAVIGATION_CONTROL_ZOOM';
                    break;
                    default:
                            $ctrl_type = '';
                    break;
		}
		$ctrl_opt = comZhBaiduMapMapsHelper::get_control_position_option(
							$map->navigationpos, 
							$map->navigationofsx, 
							$map->navigationofsy);
		if ($ctrl_opt != "")
		{
                    if ($ctrl_type != "")
                    {
                        $scripttext .= $ctrl_type. ','. $ctrl_opt;
                    }
                    else
                    {
                        $scripttext .= $ctrl_opt;
                    }		
		}
                else
                {
                    $scripttext .= $ctrl_type;
                }
		$scripttext .= "\n".'}));' ."\n";
	} 

	
	if (isset($map->overviewmapcontrol) && (int)$map->overviewmapcontrol != 0) 
	{
		$scripttext .= 'map'.$mapDivSuffix.'.addControl(new BMap.OverviewMapControl({';
		switch ((int)$map->overviewmapcontrol) 
		{
			case 1:
				$scripttext .= "\n".'  isOpen:  false';
			break;
			case 2:
				$scripttext .= "\n".'  isOpen:  true';
			break;
			default:
				$scripttext .= '';
			break;
		}
		$scripttext .= "\n".'}));' ."\n";
	
	} 
	// Begin 2
	// Geo Location - begin
	if (isset($map->geolocationcontrol) && (int)$map->geolocationcontrol == 1) 
	{
	
		
		$scripttext .= ' function geoLocationButton'.$mapDivSuffix.'(anchor, ofx, ofy){' ."\n";  
		$scripttext .= '   if (anchor != "")' ."\n";  
		$scripttext .= '   {this.defaultAnchor = anchor;}' ."\n";  
		$scripttext .= '   else' ."\n";  
		$scripttext .= '   {this.defaultAnchor = BMAP_ANCHOR_TOP_LEFT;}' ."\n";  
		
		$scripttext .= '   if (ofx != 0 || ofy != 0)' ."\n";  
		$scripttext .= '   {this.defaultOffset = new BMap.Size(ofx, ofy);}' ."\n";  
		$scripttext .= '   else' ."\n";  
		$scripttext .= '   {this.defaultOffset = new BMap.Size(10, 10);}' ."\n";  
		$scripttext .= ' }' ."\n";  		
		$scripttext .= ' geoLocationButton'.$mapDivSuffix.'.prototype = new BMap.Control();' ."\n";  
		$scripttext .= ' geoLocationButton'.$mapDivSuffix.'.prototype.initialize = function(map'.$mapDivSuffix.'){' ."\n";  
		$scripttext .= '  var div = document.createElement("div");' ."\n";
		$scripttext .= '  div.id = "geoLocation";' ."\n";
                $scripttext .= '  div.className = "zhbdm-geolocation-main";'."\n";
		$scripttext .= '  div.innerHTML="';
		$scripttext .= '  <button id=\"geoLocationButton'.$mapDivSuffix.'\" class=\"zhbdm-geolocation-button\">';
		switch ((int)$map->geolocationbutton) 
		{
			
			case 1:
				$scripttext .= '<img src=\"'.$imgpathUtils.'geolocation.png\" alt=\"'.JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATIONBUTTON').'\" style=\"vertical-align: middle\" />';
			break;
			case 2:
				$scripttext .= JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATIONBUTTON');
			break;
			case 3:
				$scripttext .= '<img src=\"'.$imgpathUtils.'geolocation.png\" alt=\"'.JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATIONBUTTON').'\" style=\"vertical-align: middle\" />';
				$scripttext .= JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATIONBUTTON');
			break;
			default:
				$scripttext .= '<img src=\"'.$imgpathUtils.'geolocation.png\" alt=\"'.JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATIONBUTTON').'\" style=\"vertical-align: middle\" />';
			break;
		}
		$scripttext .= '</button>';
		$scripttext .= '";' ."\n";
		$scripttext .= '  div.onclick = function(e){' ."\n";
			if ((isset($map->findcontrol) && (int)$map->findcontrol == 1) )
			{
				if (isset($map->findcontrol) && (int)$map->findcontrol == 1)
				{
					if (isset($map->findroute) && (int)$map->findroute != 0)
					{
						$scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind'.$mapDivSuffix.', "findAddressTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
					}
					else
					{
						$scripttext .= 'findMyPosition'.$mapDivSuffix.'("Button", findRouteDirectionsDisplay'.$mapDivSuffix.', findRouteDirectionsService'.$mapDivSuffix.', markerFind'.$mapDivSuffix.', "findAddressTravelMode'.$mapDivSuffix.'", routedestination'.$mapDivSuffix.');' ."\n";
					}
				}
				else
				{
					$scripttext .= 'findMyPosition'.$mapDivSuffix.'("Other");' ."\n";					
				}
			}
			else
			{
				$scripttext .= 'findMyPosition'.$mapDivSuffix.'("Other");' ."\n";
			}
		$scripttext .= '  };' ."\n";
		$scripttext .= '  map'.$mapDivSuffix.'.getContainer().appendChild(div);' ."\n";
		$scripttext .= '  return div;' ."\n";
		$scripttext .= '  };' ."\n";

		$def_val = '';
		switch ((int)$map->geolocationpos) 
		{
			case 1:
					$def_val .= "\n".'  BMAP_ANCHOR_TOP_LEFT';
			break;
			case 2:
					$def_val .= "\n".'  BMAP_ANCHOR_TOP_RIGHT';
			break;
			case 3:
					$def_val .= "\n".'  BMAP_ANCHOR_BOTTOM_LEFT';
			break;
			case 4:
					$def_val .= "\n".'   BMAP_ANCHOR_BOTTOM_RIGHT';
			break;
			default:
				$def_val .= '\'\'';
			break;
		}
		
		$scripttext .= '  map'.$mapDivSuffix.'.addControl(new geoLocationButton'.$mapDivSuffix.'('.
							$def_val.', '.
							$map->geolocationofsx.', '.
							$map->geolocationofsy.')'.
						"\n".');' ."\n";
		
	}
	// Geo Location - end
	// End 2
	
	
	// Pushing controls - End
	
	$scripttext .= 'var geocoder'.$mapDivSuffix.' = new BMap.Geocoder();'."\n";
        $scripttext .= 'var convertor'.$mapDivSuffix.' = new BMap.Convertor();'."\n";

        if ($map->mapstyles != "")
	{
		$scripttext .= 'var mapStyles = '.$map->mapstyles.';'."\n";
		
		$scripttext .= 'map'.$mapDivSuffix.'.setMapStyle({styleJson: mapStyles});'."\n";
	}
	if ($managePanelInfowin == 1
	|| ((int)$map->markerlistpos != 0)
	)
	{
		$scripttext .='Map_Initialize_All(map'.$mapDivSuffix.');'."\n";
	}
	
        /*
        if (isset($map->disableautopan) && ((int)$map->disableautopan == 1))	
	{
		$scripttext .= 'infowindow'.$mapDivSuffix.'.setOptions({disableAutoPan: true});'."\n";
	}
        */
        
	if ($zhbdmObjectManager != 0)
	{
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMap(map'.$mapDivSuffix.');' ."\n";
		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setInfowin(infowindow'.$mapDivSuffix.');' ."\n";
	}        
	
	if (isset($map->mapbounds) && $map->mapbounds != "")
	{
		$mapBoundsArray = explode(";", str_replace(',',';',$map->mapbounds));
		if (count($mapBoundsArray) != 4)
		{
			$scripttext .= 'alert("'.JText::_('COM_ZHBAIDUMAP_MAP_ERROR_MAPBOUNDS').'");'."\n";
		}
		else
		{
		
			if ($zhbdmObjectManager != 0)
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMapBounds('.$mapBoundsArray[0].', '.$mapBoundsArray[1].','.$mapBoundsArray[2].', '.$mapBoundsArray[3].');' ."\n";
			}		

		}
	}	
        
	if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
	{
	
		if ($zhbdmObjectManager != 0)
		{
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMarkerListPos('.(int)$map->markerlistpos.');' ."\n";
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMarkerListContent('.(int)$map->markerlistcontent.');' ."\n";
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMarkerListAction('.(int)$map->markerlistaction.');' ."\n";
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setMarkerListCSSStyle("'.$markerlistcssstyle.'");' ."\n";
			
		}

		if ((int)$map->markerlistpos == 111
		  ||(int)$map->markerlistpos == 112
		  ||(int)$map->markerlistpos == 121
		  ||(int)$map->markerlistpos == 120 // panel
		  ) 
		{
			// Do not create button when table or external
		}
		else
		{
			if ((int)$map->markerlistbuttontype == 0)
			{
				// Skip creation for non-button
			}
			else
			{

				$scripttext .= '  var placemarklistControl = new zhbdmPlacemarkListButtonControl('.
					'"BDMapsMarkerList'.$mapDivSuffix.'",'.
					'map'.$mapDivSuffix.','. 
					$feature4control.','. 
					(int)$map->markerlistbuttontype.','. 
					(int)$map->markerlistbuttonpos.','. 
					'"placemarklist",'. 
					'"'.$fv_override_placemark_button_tooltip.'",'.
					'16,'. 
					'16,'. 
					'"'.$imgpathUtils.'star.png"'.
					');'."\n";				
				
			}
		}
	
	}
	
	if ($managePanelFeature == 1)
	{
		
			$scripttext .= '  var panelControl = new zhbdmPanelButtonControl('.
				'"BDMapsMainPanel'.$mapDivSuffix.'","BDMapsID'.$mapDivSuffix.'","BDMapsPanelAccordion'.$mapDivSuffix.'",'.$managePanelContentWidth.','.
				'map'.$mapDivSuffix.','. 'zhbdmObjMgr'.$mapDivSuffix.','.
				$feature4control.','. 
				(int)$map->panelstate.','. 
				'7,'. 
				'"panel",'. 
				'"'.$fv_override_panel_button_tooltip.'",'.
				'18,'. 
				'23,'. 
				'"'.$imgpathUtils.'panel_left.png"'.
				');'."\n";			
	}

	// Create Placemark for Insert Users Placemarks - Begin
        // 
	//UserMarker - begin
	if ($allowUserMarker == 1
	 && (((int)$map->usermarkersinsert == 1) || (int)$map->usermarkersupdate == 1))
	{		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		
		$query->select('h.title as text, h.id as value ');
		$query->from('#__zhbaidumaps_markergroups as h');
		$query->leftJoin('#__categories as c ON h.catid=c.id');
		$query->where('1=1');
		// get all groups, because you can add marker and disable group
		//$query->where('h.published=1');
		$query->order('h.title');
		
		$db->setQuery($query);    

		if (!$db->query())
		{
			$scripttext .= 'alert("Error (Load Group List Item): " + "' . $db->escape($db->getErrorMsg()).'");';
		}
		else
		{
			$newMarkerGroupList = $db->loadObjectList();
		}

		// icon type
		$scripttext .= 'var contentInsertPlacemarkIcon = "" +' ."\n";
		if (isset($map->usermarkersicon) && (int)$map->usermarkersicon == 1) 
		{
			$iconTypeJS = " onchange=\"javascript: ";
			$iconTypeJS .= " if (document.forms.insertPlacemarkForm.markerimage.options[selectedIndex].value!=\'\') ";
			$iconTypeJS .= " {document.markericonimage.src=\'".$imgpathIcons."\' + document.forms.insertPlacemarkForm.markerimage.options[selectedIndex].value.replace(/#/g,\'%23\') + \'.png\'}";
			$iconTypeJS .= " else ";
			$iconTypeJS .= " {document.markericonimage.src=\'\'}\"";
			
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_ICON_TYPE' ).' \'+' ."\n";
			$scripttext .= ' \'';
			$scripttext .= '<img name="markericonimage" src="" alt="" />';
			$scripttext .= '\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= ' \'';
			$scripttext .= str_replace('.png<', '<', 
								str_replace('.png"', '"', 
									str_replace('JOPTION_SELECT_IMAGE', JText::_('COM_ZHBAIDUMAP_MAP_USER_IMAGESELECT'),
										str_replace(array("\r", "\r\n", "\n"),'', JHTML::_('list.images',  'markerimage', $active =  "", $iconTypeJS, $directoryIcons, $extensions =  "png")))));
			$scripttext .= '\'+' ."\n";

			$scripttext .= '    \'<br />\';' ."\n";
		}
		else
		{
			$scripttext .= '    \'<input name="markerimage" type="hidden" value="default#" />\'+' ."\n";	
			$scripttext .= '    \'\';' ."\n";
		}

	}

	if ($allowUserMarker == 1 && (int)$map->usermarkersinsert == 1)
	{		
		$scripttext .= 'var  latlngInsertPlacemark;' ."\n";
		$scripttext .= 'var  insertPlacemark = new BMap.Marker({' ."\n";
		$scripttext .= '  });'."\n";

		$scripttext .= 'insertPlacemark.enableDragging();'."\n";
		$scripttext .= 'insertPlacemark.setAnimation(BMAP_ANIMATION_DROP);'."\n";
		
		$scripttext .= 'map'.$mapDivSuffix.'.addOverlay(insertPlacemark);' ."\n";

		$scripttext .= 'insertPlacemark.title = "'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NEWMARKER' ).'";' ."\n";
		$scripttext .= 'insertPlacemark.description = "'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NEWMARKER_DESC' ).'";' ."\n";

		$scripttext .= 'var contentInsertPlacemarkPart1 = \'<div id="contentInsertPlacemark">\' +' ."\n";
		$scripttext .= '\'<'.$placemarkTitleTag.' id="headContentInsertPlacemark" class="insertPlacemarkHead">'.
			'<img src="'.$imgpathUtils.'published'.(int)$map->usermarkerspublished.'.png" alt="" /> '.
			JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NEWMARKER' ).'</'.$placemarkTitleTag.'>\'+' ."\n";
		$scripttext .= '\'<div id="bodyContentInsertPlacemark"  class="insertPlacemarkBody">\'+'."\n";
		//$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LNG' ).' \'+current.lng + ' ."\n";
		//$scripttext .= '    \'<br />'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LAT' ).' \'+current.lat + ' ."\n";
		$scripttext .= '    \'<form id="insertPlacemarkForm" action="'.JURI::current().'" method="post">\'+'."\n";

		// Begin Placemark Properties
		$scripttext .= '\'<div id="bodyInsertPlacemarkDivA"  class="bodyInsertProperties">\'+'."\n";
		$scripttext .= '\'<a id="bodyInsertPlacemarkA" href="javascript:showonlyone(\\\'Placemark\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'</a>\'+'."\n";
		$scripttext .= '\'</div>\'+'."\n";
		$scripttext .= '\'<div id="bodyInsertPlacemark"  class="bodyInsertPlacemarkProperties">\'+'."\n";
		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NAME' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \'<input name="markername" type="text" maxlength="250" size="50" />\'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_DESCRIPTION' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \'<input name="markerdescription" type="text" maxlength="250" size="50" />\'+' ."\n";
		$scripttext .= '    \'<br />\';' ."\n";


		$scripttext .= 'var contentInsertPlacemarkPart2 = "" +' ."\n";
		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BALOON' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		
		$scripttext .= '    \' <select name="markerbaloon" > \'+' ."\n";
		$scripttext .= '    \' <option value="1" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_DROP').'</option> \'+' ."\n";
		$scripttext .= '    \' <option value="2" >'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_BOUNCE').'</option> \'+' ."\n";
		$scripttext .= '    \' <option value="3" >'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_SIMPLE').'</option> \'+' ."\n";
		$scripttext .= '    \' </select> \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";

		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_USER_MARKERCONTENT' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		
		$scripttext .= '    \' <select name="markermarkercontent" > \'+' ."\n";
		$scripttext .= '    \' <option value="0" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE_DESC').'</option> \'+' ."\n";
		$scripttext .= '    \' <option value="1" >'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE').'</option> \'+' ."\n";
		$scripttext .= '    \' <option value="2" >'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_DESCRIPTION').'</option> \'+' ."\n";
		$scripttext .= '    \' <option value="100" >'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_NONE').'</option> \'+' ."\n";
		$scripttext .= '    \' </select> \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
				
		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_HREFIMAGE_LABEL' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \'<input name="markerhrefimage" type="text" maxlength="500" size="50" value="" />\'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";

		$scripttext .= '    \'<br />\'+' ."\n";
		
		$scripttext .= '\'</div>\'+'."\n";
		// End Placemark Properties

		// Begin Placemark Group Properties
		$scripttext .= '\'<div id="bodyInsertPlacemarkGrpDivA"  class="bodyInsertProperties">\'+'."\n";
		$scripttext .= '\'<a id="bodyInsertPlacemarkGrpA" href="javascript:showonlyone(\\\'PlacemarkGroup\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'</a>\'+'."\n";
		$scripttext .= '\'</div>\'+'."\n";
		$scripttext .= '\'<div id="bodyInsertPlacemarkGrp"  class="bodyInsertPlacemarkGrpProperties">\'+'."\n";
		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_GROUP' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		
		$scripttext .= '    \' <select name="markergroup" > \'+' ."\n";
		$scripttext .= '    \' <option value="" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_FILTER_PLACEMARK_GROUP').'</option> \'+' ."\n";
		foreach ($newMarkerGroupList as $key => $newGrp) 
		{
			$scripttext .= '    \' <option value="'.$newGrp->value.'">'.$newGrp->text.'</option> \'+' ."\n";
		}
		$scripttext .= '    \' </select> \'+' ."\n";
		
		$scripttext .= '    \'<br />\'+' ."\n";

		$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CATEGORY' ).' \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \' <select name="markercatid" > \'+' ."\n";
		$scripttext .= '    \' <option value="" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAP_FILTER_CATEGORY').'</option> \'+' ."\n";
		$scripttext .= '    \''.str_replace(array("\r", "\r\n", "\n"),'', 
		                       JHtml::_('select.options', JHtml::_('category.options', 'com_zhbaidumap'), 'value', 'text', '')) .
							   '\'+' ."\n";
		$scripttext .= '    \' </select> \'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '    \'<br />\'+' ."\n";
		$scripttext .= '\'</div>\'+'."\n";
		// End Placemark Group Properties
		
		// Begin Contact Properties
		if (isset($map->usercontact) && (int)$map->usercontact == 1) 
		{

			$scripttext .= '\'<div id="bodyInsertContactDivA"  class="bodyInsertProperties">\'+'."\n";
			$scripttext .= '\'<a id="bodyInsertContactA" href="javascript:showonlyone(\\\'Contact\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'</a>\'+'."\n";
			$scripttext .= '\'</div>\'+'."\n";
			$scripttext .= '\'<div id="bodyInsertContact"  class="bodyInsertContactProperties">\'+'."\n";
			$scripttext .= '\'<img src="'.$imgpathUtils.'published'.(int)$map->usercontactpublished.'.png" alt="" /> \'+'."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_NAME' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactname" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_POSITION' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactposition" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PHONE' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactphone" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_MOBILE' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactmobile" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_FAX' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactfax" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_EMAIL' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactemail" type="text" maxlength="250" size="50" />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<input name="contactid" type="hidden" value="" />\'+' ."\n";
			$scripttext .= '\'</div>\'+'."\n";
			// Contact Address
			$scripttext .= '\'<div id="bodyInsertContactAdrDivA"  class="bodyInsertProperties">\'+'."\n";
			$scripttext .= '\'<a id="bodyInsertContactAdrA" href="javascript:showonlyone(\\\'ContactAddress\\\',\\\'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'</a>\'+'."\n";
			$scripttext .= '\'</div>\'+'."\n";
			$scripttext .= '\'<div id="bodyInsertContactAdr"  class="bodyInsertContactAdrProperties">\'+'."\n";
			$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS' ).' \'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<textarea name="contactaddress" cols="35" rows="4"></textarea>\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '    \'<br />\'+' ."\n";
			$scripttext .= '\'</div>\'+'."\n";
		}
		// End Contact Properties
		
		$scripttext .= '\'\';'."\n";


		$scripttext .= '    insertPlacemark.addEventListener( \'dragend\', function(event) {' ."\n";
		$scripttext .= '      map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
		$scripttext .= '      latlngInsertPlacemark = event.point;' ."\n";

		$scripttext .= '    });' ."\n";

		$scripttext .= '    insertPlacemark.addEventListener( \'click\', function(event) {' ."\n";
		$scripttext .= '        latlngInsertPlacemark = event.point;' ."\n";

		$scripttext .= '  contentInsertPlacemarkButtons = \'<div id="contentInsertPlacemarkButtons">\' +' ."\n";
		$scripttext .= '    \'<hr />\'+' ."\n";					
		$scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlngInsertPlacemark.lat + \'" />\'+' ."\n";
		$scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlngInsertPlacemark.lng + \'" />\'+' ."\n";
		$scripttext .= '    \'<input name="marker_action" type="hidden" value="insert" />\'+' ."\n";	
		$scripttext .= '    \'<input name="markersubmit" type="submit" value="'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BUTTON_ADD' ).'" />\'+' ."\n";
		$scripttext .= '    \'</form>\'+' ."\n";		
		$scripttext .= '\'</div>\'+'."\n";
		$scripttext .= '\'</div>\';'."\n";
		
		$scripttext .= '  		infowindow'.$mapDivSuffix.'.setContent(contentInsertPlacemarkPart1+';
		$scripttext .= 'contentInsertPlacemarkIcon+';
		//$scripttext .= 'contentInsertPlacemarkIcon.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", "default#").'.png"\')+';
		$scripttext .= 'contentInsertPlacemarkPart2+';
		$scripttext .= 'contentInsertPlacemarkButtons);' ."\n";
		//$scripttext .= '      infowindow'.$mapDivSuffix.'.setPosition(latlngInsertPlacemark);' ."\n";
		$scripttext .= '      map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', latlngInsertPlacemark);' ."\n";
		$scripttext .= '    });' ."\n";
		
		$scripttext .= '  map'.$mapDivSuffix.'.addEventListener( \'click\', function(event) {' ."\n";
		$scripttext .= '  if(event.overlay!=null)' ."\n";
		$scripttext .= '  {' ."\n";
		$scripttext .= '  	return;' ."\n";
		$scripttext .= '  }' ."\n";
		$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
		$scripttext .= '  latlngInsertPlacemark = event.point;' ."\n";
		$scripttext .= '  insertPlacemark.setPosition(latlngInsertPlacemark);' ."\n";

		$scripttext .= '    });' ."\n";
		
	}
	// New Marker - End



	// Create Placemark for Insert Users Placemarks - End
	
    if (isset($map->balloon)) 
	{

		$scripttext .= 'var contentString'.$mapDivSuffix.' = \'<div id="placemarkContent">\' +' ."\n";
		$scripttext .= '\'<'.$placemarkTitleTag.' id="headContent" class="placemarkHead">'.htmlspecialchars(str_replace('\\', '/', $map->title), ENT_QUOTES, 'UTF-8').'</'.$placemarkTitleTag.'>\'+' ."\n";
		$scripttext .= '\'<div id="bodyContent"  class="placemarkBody">\'+'."\n";
		$scripttext .= '\''.htmlspecialchars(str_replace('\\', '/', $map->description) , ENT_QUOTES, 'UTF-8').'\'+'."\n";
		$scripttext .= '\'</div>\'+'."\n";
		$scripttext .= '\'</div>\';'."\n";


		if ((int)$map->balloon != 0) 
		{
			$scripttext .= '  var marker'.$mapDivSuffix.' = new BMap.Marker(' ."\n";
			$scripttext .= '   latlng'.$mapDivSuffix.', { ' ."\n";
			// Replace to new, because all charters are shown
			//$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $map->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";
			$scripttext .= '   title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $map->title)).'"' ."\n";
			$scripttext .= '});'."\n";

			if ((isset($map->markercluster) && (int)$map->markercluster == 0))
			{
					$scripttext .= 'map'.$mapDivSuffix.'.addOverlay(marker'.$mapDivSuffix.');' ."\n";
			}

			switch ($map->balloon) 
			{
                            case 1:
                                    $scripttext .= 'marker'.$mapDivSuffix.'.setAnimation(BMAP_ANIMATION_DROP);' ."\n";
                            break;
                            case 2:
                                    $scripttext .= 'marker'.$mapDivSuffix.'.setAnimation(BMAP_ANIMATION_BOUNCE);' ."\n";
                            break;
                            case 3:
                                    $scripttext .= '' ."\n";
                            break;
                            default:
                                    $scripttext .= '' ."\n";
                            break;
			}
                        
			
			
			$scripttext .= '  marker'.$mapDivSuffix.'.addEventListener(\'click\', function(event) {' ."\n";
			$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'.$mapDivSuffix.');' ."\n";
			$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', latlng'.$mapDivSuffix.');' ."\n";
			$scripttext .= ' });' ."\n";

			

			if ($zhbdmObjectManager != 0)
			{
				$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAdd(0, 0, marker'.$mapDivSuffix.', null);'."\n";
			}

            
                }


                // Overrides map center
                if ($ajaxLoadObjects != 0) {
                    if ($currentPlacemarkCenter != "do not change") {
                        $curcenterLatLng = comZhBaiduMapPlacemarksHelper::get_placemark_coordinates((int)$currentPlacemarkCenter);

                        if ($curcenterLatLng != "") {
                            if ($curcenterLatLng != "geocode") {
                                $scripttext .= 'latlng'.$mapDivSuffix.' = '.$curcenterLatLng.';'."\n";
                                $scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.$mapDivSuffix.');'."\n";
                            }   
                        }

                    }
                }
        
                
                if ((int)$map->openballoon == 1)
                {
                    if ((int)$map->balloon != 0)
                    {
                        $scripttext .= '  marker'.$mapDivSuffix.'.dispatchEvent("click");' ."\n";

                    }
                    else
                    {
                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'.$mapDivSuffix.');' ."\n";
                        $scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', latlng'.$mapDivSuffix.');' ."\n";
                    }
                }
            
	}

	// Creating Clusters in the beginning for using in geocoding
	if ((isset($map->markercluster) && (int)$map->markercluster == 1))
	{      

		if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
		{
			$this->markercluster = 1;
		}
		else
		{
			$markercluster = 1;
		}


		$clustererOptions = 'imagePath: icoUtils+\'m\'' ."\n";

		if ((int)$map->clusterzoom == 0)
		{
			$scripttext .= 'markerCluster0 = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.');' ."\n";
		}
		else
		{
			$scripttext .= 'markerCluster0 = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.', { maxZoom: '.$map->clusterzoom.'});' ."\n";
		}

		if ($zhbdmObjectManager != 0)
		{
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.ClusterAdd(0, markerCluster0);' ."\n";
		}
		
                if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
		{

			if (isset($markergroups) && !empty($markergroups)) 
			{
				foreach ($markergroups as $key => $currentmarkergroup) 
				{
					$clustererOptions = 'imagePath: icoUtils+\'m\'' ."\n";
					
					if ((int)$map->clusterzoom == 0)
					{
						if ((int)$currentmarkergroup->overridegroupicon == 1)
						{
							$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.');' ."\n";
						}
						else
						{
							$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.');' ."\n";
							//$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.');' ."\n";
						}
					}
					else
					{
						if ((int)$currentmarkergroup->overridegroupicon == 1)
						{
							$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.', { maxZoom: '.$map->clusterzoom.'});' ."\n";
						}
						else
						{
							$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new BMapLib.MarkerClusterer(map'.$mapDivSuffix.', { maxZoom: '.$map->clusterzoom.'});' ."\n";
							//$scripttext .= 'markerCluster'.$currentmarkergroup->id.' = new MarkerClusterer(map'.$mapDivSuffix.', { maxZoom: '.$map->clusterzoom.'});' ."\n";
						}
					}
					
					// That way to add styles
					if ((int)$currentmarkergroup->overridegroupicon == 1)
					{
						$imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarkergroup->icontype).'.png';
						$imgimg4size = $imgpath4size.$currentmarkergroup->icontype.'.png';
						
						list ($imgwidth, $imgheight) = getimagesize($imgimg4size);
						$scripttext .= 'var stylesMC'.$currentmarkergroup->id.' = [{'."\n";
						$scripttext .='size: new BMap.Size('.$imgwidth.', '.$imgheight.'),' ."\n";
						// Offset - Anchor doesn't work (anchor, opt_anchor...)?
						//$scripttext .='opt_anchor: ['.$currentmarkergroup->iconofsetx.', '.$currentmarkergroup->iconofsety.'],' ."\n";
						$scripttext .='url: "'.$imgimg.'"' ."\n";
						$scripttext .='}];' ."\n";

						$scripttext .= 'markerCluster'.$currentmarkergroup->id.'.setStyles(stylesMC'.$currentmarkergroup->id.');'."\n";
					}
                                        
					if ($zhbdmObjectManager != 0)
					{
						$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.ClusterAdd('.$currentmarkergroup->id.', markerCluster'.$currentmarkergroup->id.');' ."\n";
					}
				}
			}

		}
		
	}

  	if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
	{
		if ((int)$map->markerlistcontent < 100) 
		{
			$tmp_str_message = JText::_('COM_ZHBAIDUMAP_MAP_MARKERUL_NOTFIND');
			if ($mapDivSuffix != "")
			{
				$tmp_str4replace = 'BDMapsMarkerUL'.$mapDivSuffix;
				$tmp_str2replace = 'BDMapsMarkerUL';
				$tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);
			}

			$scripttext .= 'var markerUL = document.getElementById("BDMapsMarkerUL'.$mapDivSuffix.'");'."\n";
			$scripttext .= 'if (!markerUL)'."\n";
			$scripttext .= '{'."\n";
			$scripttext .= ' alert("'.$tmp_str_message.'");'."\n";
			$scripttext .= '}'."\n";
		}
		else
		{
			$tmp_str_message = JText::_('COM_ZHBAIDUMAP_MAP_MARKERTABLE_NOTFIND');
			if ($mapDivSuffix != "")
			{
				$tmp_str4replace = 'BDMapsMarkerTABLEBODY'.$mapDivSuffix;
				$tmp_str2replace = 'BDMapsMarkerTABLEBODY';
				$tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);
			}
			
			
			$scripttext .= 'var markerUL = document.getElementById("BDMapsMarkerTABLEBODY'.$mapDivSuffix.'");'."\n";
			$scripttext .= 'if (!markerUL)'."\n";
			$scripttext .= '{'."\n";
			$scripttext .= ' alert("'.$tmp_str_message.'");'."\n";
			$scripttext .= '}'."\n";
		}
		
	}
		
	// External Group Control
	if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol == 10) 
	{
			$tmp_str_message = JText::_('COM_ZHBAIDUMAP_MAP_GROUPDIV_NOTFIND');

			if ($mapDivSuffix != "")
			{

				$tmp_str4replace = 'BDMapsGroupDIV'.$mapDivSuffix;
				$tmp_str2replace = 'BDMapsGroupDIV';
				$tmp_str_message = str_replace($tmp_str2replace, $tmp_str4replace, $tmp_str_message);

			}

			$scripttext .= 'var groupDivTag = document.getElementById("BDMapsGroupDIV'.$mapDivSuffix.'");'."\n";
			$scripttext .= 'if (!groupDivTag)'."\n";
			$scripttext .= '{'."\n";
			$scripttext .= ' alert("'.$tmp_str_message.'");'."\n";
			$scripttext .= '}'."\n";
			$scripttext .= 'else'."\n";
			$scripttext .= '{'."\n";
			$scripttext .= ' groupDivTag.innerHTML = \''.str_replace('\'', '\\\'', str_replace(array("\r", "\r\n", "\n"),'', $divmarkergroup)).'\';'."\n";
			$scripttext .= '}'."\n";
	}
		

	// Markers
	
	
	if (isset($markers) && !empty($markers)) 
	{
		//$scripttext .= '    alert("$map->markercluster='. $map->markercluster.'");'."\n";
		//$scripttext .= '    alert("$map->markerclustergroup='. $map->markerclustergroup.'");'."\n";
		//$scripttext .= '    alert("$map->markergroupcontrol='. $map->markergroupcontrol.'");'."\n";
			
		// Main loop
		foreach ($markers as $key => $currentmarker) 
		{

			//$scripttext .= '    alert("try marker '. $currentmarker->id.'");'."\n";
			//$scripttext .= '    alert("$currentmarker->publishedgroup='. $currentmarker->publishedgroup.'");'."\n";

		// Begin restriction 
			if (
				((($currentmarker->markergroup != 0)
					&& ((int)$currentmarker->published == 1)
					&& ((int)$currentmarker->publishedgroup == 1)) || ($allowUserMarker == 1)
				) || 
				((($currentmarker->markergroup == 0)
					&& ((int)$currentmarker->published == 1)) || ($allowUserMarker == 1)
				) 
 			)
			{

   				//$scripttext .= '    alert("Work on marker '. $currentmarker->id.'");'."\n";
				$scripttext .= 'var titlePlacemark'. $currentmarker->id.' = "'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'";'."\n";
			    if (($currentmarker->latitude != "" && $currentmarker->longitude != "")
				   ||($currentmarker->addresstext != ""))
				{
					if ($currentmarker->latitude != "" && $currentmarker->longitude != "")
					{
						$scripttext .= 'var latlng'. $currentmarker->id.' = new BMap.Point('.$currentmarker->longitude.', ' .$currentmarker->latitude.');' ."\n";

						// Begin marker creation with lat,lng
						// contentString - Begin
						$scripttext .= 'var contentString'. $currentmarker->id.' = "";'."\n";
						if (($allowUserMarker == 0)
						 || ((int)$map->usermarkersupdate == 0)
						 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
						 || ($currentUserID == 0)
						 || (isset($currentmarker->createdbyuser) 
						    && (((int)$currentmarker->createdbyuser != $currentUserID )
							   || ((int)$currentmarker->createdbyuser == 0)))
						 )
						{
							if (isset($map->useajax) && (int)$map->useajax != 0)
							{
								// do not create content string, create by loop only in the end
							}
							else
							{
								if (((int)$currentmarker->actionbyclick == 1)
									||
									(((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
									||  (($managePanelInfowin == 1) && (((int)$currentmarker->actionbyclick == 1) || (int)$currentmarker->actionbyclick == 4))
									)
								{
									if ($managePanelInfowin == 1)					
									{
										if ((int)$currentmarker->actionbyclick == 1)
										{										
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
													$mapDivSuffix, 
													$currentmarker, $map->usercontact, $map->useruser,
													$userContactAttrs, $service_DoDirection,
													$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo);											
											$scripttext .= ';'."\n";
										}
										else if ((int)$currentmarker->actionbyclick == 4)
										{
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_tabs_content_string(
													$mapDivSuffix, $currentmarker,
													comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
														$mapDivSuffix, 
														$currentmarker, $map->usercontact, $map->useruser,
														$userContactAttrs, $service_DoDirection,
														$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo),
													$imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang);											
											$scripttext .= ';'."\n";
										}
									}
									else
									{
										if (((int)$currentmarker->actionbyclick == 1)
										||
										(((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
										)
										{
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
													$mapDivSuffix, 
													$currentmarker, $map->usercontact, $map->useruser,
													$userContactAttrs, $service_DoDirection,
													$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo);											
											$scripttext .= ';'."\n";
										}
										
									}									

 
								}
							}
						}
						else
						{
							// contentString - User Placemark can Update - Begin
							$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemark_content_update_string(
													$map->usermarkersicon, 
													$map->usercontact, 
													$currentmarker,
													$imgpathIcons, $imgpathUtils, $directoryIcons,
													$newMarkerGroupList
													);
							// contentString - User Placemark can Update - End
						}

						if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
						{
							if (isset($map->useajax) && (int)$map->useajax != 0)
							{
								// do not create content string, create by loop only in the end
							}
							else
							{
								if ((int)$map->hovermarker == 1
								  ||(int)$map->hovermarker == 2)
								{
									if ($currentmarker->hoverhtml != "")
									{
										$scripttext .= 'var hoverString'. $currentmarker->id.' = '.
											comZhBaiduMapPlacemarksHelper::get_placemark_hover_string(
												$currentmarker);									
									}
								}
							}
						}
						
						if ((int)$currentmarker->baloon != 0) 
						{
								  

                                                        $scripttext .= 'var marker'. $currentmarker->id.' = new BMap.Marker(' ."\n";
							
							$scripttext .= '      latlng'. $currentmarker->id.', {' ."\n";
                                                        // Replace to new, because all charters are shown
							//$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'"' ."\n";
							if (isset($currentmarker->markercontent) &&
								(((int)$currentmarker->markercontent == 0) ||
								 ((int)$currentmarker->markercontent == 1) ||
								 ((int)$currentmarker->markercontent == 9))
								)
							{
								$scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'"' ."\n";
							}
							else
							{
								$scripttext .= '      title:""' ."\n";
							}
							$scripttext .= '});'."\n";

                                                        if ((int)$currentmarker->baloon == 21
							 || (int)$currentmarker->baloon == 22
							 || (int)$currentmarker->baloon == 23
							 )
							{
                                                            if ($currentmarker->labelcontent != "")
                                                            {
                                                                
                                                                $scripttext .= ' var label'. $currentmarker->id.' = new BMap.Label("'.str_replace(array("\r", "\r\n", "\n"), '', str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->labelcontent))).'"';
                                                                if ((int)$currentmarker->labelanchorx != 0
                                                                || ((int)$currentmarker->labelanchory != 0))
                                                                {
                                                                        $scripttext .= ', {offset: new BMap.Size('. (int)$currentmarker->labelanchorx .', '.(int)$currentmarker->labelanchory .')}';
                                                                }
                                                                 $scripttext .=');' ."\n";
                                                                 
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setLabel(label'. $currentmarker->id.');' ."\n";
                                                            }
                                                        }
							if ((isset($map->markercluster) && (int)$map->markercluster == 0))
							{
                                                            $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(marker'. $currentmarker->id.');' ."\n";
							}

							$scripttext .=  comZhBaiduMapPlacemarksHelper::get_placemark_icon_definition(
												$imgpathIcons,
												$imgpath4size,
												$currentmarker);
						

							switch ($currentmarker->baloon) 
							{
                                                            case 1:
                                                                    $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_DROP);' ."\n";
                                                            break;
                                                            case 2:
                                                                    $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_BOUNCE);' ."\n";
                                                            break;
                                                            case 3:
                                                                    $scripttext .= '' ."\n";
                                                            break;
                                                            case 21:
                                                                    $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_DROP);' ."\n";
                                                            break;
                                                            case 22:
                                                                    $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_BOUNCE);' ."\n";
                                                            break;
                                                            default:
                                                                    $scripttext .= '' ."\n";
                                                            break;
							}

							if (($allowUserMarker == 0)
							 || ((int)$map->usermarkersupdate == 0)
							 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
							 || ($currentUserID == 0)
							 || (isset($currentmarker->createdbyuser) 
								&& (((int)$currentmarker->createdbyuser != $currentUserID )
								   || ((int)$currentmarker->createdbyuser == 0))))
							{
									$scripttext .= 'marker'. $currentmarker->id.'.disableDragging();' ."\n";
							}
							else
							{
									$scripttext .= 'marker'. $currentmarker->id.'.enableDragging();' ."\n";
							}
							
		
							

							if ($externalmarkerlink == 1)
							{
								$scripttext .= 'PlacemarkByIDAdd('. $currentmarker->id.
																', '.$currentmarker->latitude.
																', '.$currentmarker->longitude.
																', marker'. $currentmarker->id.
																', latlng'. $currentmarker->id.
																', '.$currentmarker->rating_value.
																');'."\n";
							}
							
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmRating = '.$currentmarker->rating_value.';' ."\n";							
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmPlacemarkID = '.$currentmarker->id.';' ."\n";							
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmContactAttrs = userContactAttrs;' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmUserContact = "'.str_replace(';', ',', $map->usercontact).'";' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmUserUser = "'.str_replace(';', ',', $map->useruser).'";' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmOriginalPosition = latlng'.$currentmarker->id.';' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmInfowinContent = contentString'. $currentmarker->id.';' ."\n";	
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'";' ."\n";
                                                        $scripttext .= '  marker'. $currentmarker->id.'.zhbdmIncludeInList = '.$currentmarker->includeinlist.';' ."\n";							
                                                        if ($fv_override_placemark_list_search == 1)
                                                        {
                                                            $scripttext .= '  marker'. $currentmarker->id.'.zhbdmPlacemarkDescription = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->description)).'";' ."\n";														                                                            
                                                        }
		
							if (($featureSpider != 0)
							|| ($placemarkSearch != 0))					
							{
								$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.allObjectsAddPlacemark('. $currentmarker->id.', marker'. $currentmarker->id.');'."\n";
							}
							
							if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
							{
								if ($currentmarker->hoverhtml != "")
								{
									if (isset($map->useajax) && (int)$map->useajax != 0)
									{
										// do not create listeners, create by loop only in the end
										$scripttext .= '  ajaxmarkersLLhover'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
									}
									else
									{
										if ((int)$map->hovermarker == 1)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
											//$scripttext .= '  this.zhbdmZIndex = this.getZIndex();' ."\n";
											//$scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
											//$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
											$scripttext .= '  });' ."\n";
											
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
											//$scripttext .= '    this.setZIndex(this.zhbdmZIndex);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
											$scripttext .= '  });' ."\n";
										}
										else if ((int)$map->hovermarker == 2)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
											//$scripttext .= '  this.zhbdmZIndex = this.getZIndex();' ."\n";
											//$scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
											$scripttext .= '  });' ."\n";
											
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
											//$scripttext .= '    this.setZIndex(this.zhbdmZIndex);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
											$scripttext .= '  });' ."\n";
										}
									}
								}
							}
							
							
							//  If user can change placemark - override content string - begin
							//  override content string
							if (($allowUserMarker == 0)
							 || ((int)$map->usermarkersupdate == 0)
							 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
							 || ($currentUserID == 0)
							 || (isset($currentmarker->createdbyuser) 
								&& (((int)$currentmarker->createdbyuser != $currentUserID )
								   || ((int)$currentmarker->createdbyuser == 0)))
							)
							{
								if (isset($map->useajax) && (int)$map->useajax != 0)
								{
									// do not create listeners, create by loop only in the end
									$scripttext .= '  ajaxmarkersLL'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
								}
								else
								{
								// Action By Click - Begin		
								
								switch ((int)$currentmarker->actionbyclick)
								{
									// None
									case 0:
										if ((int)$currentmarker->zoombyclick != 100)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
											$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
									break;
									// Info
									case 1:
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												//$scripttext .= '  alert("Here I CAN\'T!");' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
												//$scripttext .= '  alert("Here I can!");' ."\n";
											}
											
												if ((int)$currentmarker->zoombyclick != 100)
												{
													$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
													$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
												}
											
												$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
												// Close the other infobubbles
												$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
												$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
												$scripttext .= '  }' ."\n";
												// Hide hover window when feature enabled
												if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
												{
													if ((int)$map->hovermarker == 1)
													{
														$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
													}
													else if ((int)$map->hovermarker == 2)
													{
														$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
													}
													
												}
												// Open Infowin
												$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkTitle = titlePlacemark'. $currentmarker->id.';' ."\n";
												$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";
												if ((int)$map->markerlistpos != 0)
												{
													$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";	
												}
												if ($managePanelInfowin == 1)
												{																									
													$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContent(this.zhbdmInfowinContent);' ."\n";
												}	
												else
												{											
													$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
													//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
													$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', this.getPosition());' ."\n";
												}
												if (isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))
												{
													$scripttext .= '  PlacemarkRateDivOut'.$mapDivSuffix.'('. $currentmarker->id.', 5);' ."\n";
												}

											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
												
											$scripttext .= '  });' ."\n";
									break;
									// Link
									case 2:
										if ($currentmarker->hrefsite != "")
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";

											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}

											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  window.open("'.$currentmarker->hrefsite.'");' ."\n";
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											
											$scripttext .= '  });' ."\n";
										}
										else
										{
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
													$scripttext .= '}' ."\n";
													$scripttext .= 'else {' ."\n";
												}
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= '};' ."\n";
												}
												$scripttext .= '  });' ."\n";
											}
										}
									break;
									// Link in self
									case 3:
										if ($currentmarker->hrefsite != "")
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  window.location = "'.$currentmarker->hrefsite.'";' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
										else
										{
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
													$scripttext .= '}' ."\n";
													$scripttext .= 'else {' ."\n";
												}
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= '};' ."\n";
												}
												$scripttext .= '  });' ."\n";
											}
										}
									break;
									// InfoBubble
									case 4:
										if ($managePanelInfowin == 0)
										{
											
											// InfoBubble Create - Begin
											$scripttext .= '  infoBubble'. $currentmarker->id.' = new InfoBubble('."\n";
											$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemark_infobubble_style_string($currentmarker, '');
											$scripttext .= '  );'."\n";
											
											$scripttext .= '  infobubblemarkers'.$mapDivSuffix.'.push(infoBubble'. $currentmarker->id.');'."\n";

										
											if ((int)$currentmarker->tab_info == 1)
											{					
												$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
												str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
											}
											
											if ((int)$currentmarker->tab_info == 9)
											{	
													$scripttext .= '  infoBubble'. $currentmarker->id.'.setContent(contentString'. $currentmarker->id.');'."\n";
											}
											else
											{
												
												if ($currentmarker->tab1 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1)).'\');'."\n";
												}
												if ($currentmarker->tab2 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2)).'\');'."\n";
												}
												if ($currentmarker->tab3 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3)).'\');'."\n";
												}
												if ($currentmarker->tab4 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4)).'\');'."\n";
												}
												if ($currentmarker->tab5 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5)).'\');'."\n";
												}
												if ($currentmarker->tab6 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6)).'\');'."\n";
												}
												if ($currentmarker->tab7 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7)).'\');'."\n";
												}
												if ($currentmarker->tab8 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8)).'\');'."\n";
												}
												if ($currentmarker->tab9 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9)).'\');'."\n";
												}
												if ($currentmarker->tab10 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10)).'\');'."\n";
												}
												if ($currentmarker->tab11 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11)).'\');'."\n";
												}
												if ($currentmarker->tab12 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12)).'\');'."\n";
												}
												if ($currentmarker->tab13 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13)).'\');'."\n";
												}
												if ($currentmarker->tab14 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14)).'\');'."\n";
												}
												if ($currentmarker->tab15 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15)).'\');'."\n";
												}
												if ($currentmarker->tab16 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16)).'\');'."\n";
												}
												if ($currentmarker->tab17 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17)).'\');'."\n";
												}
												if ($currentmarker->tab18 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18)).'\');'."\n";
												}
												if ($currentmarker->tab19 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19)).'\');'."\n";
												}
											}
											
											
											if ((int)$currentmarker->tab_info == 2)
											{					
												$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
												str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
											}
											
											// InfoBubble Create - End
										}
										$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
										if ($featureSpider != 0)
										{
											$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
											$scripttext .= '}' ."\n";
											$scripttext .= 'else {' ."\n";
										}
										if ((int)$currentmarker->zoombyclick != 100)
										{
											$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
											$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
										}
										// Close the other infowin and infobubbles
										$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
										$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
										$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
										$scripttext .= '  }' ."\n";
										// Hide hover window when feature enabled
										if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
										{
											if ((int)$map->hovermarker == 1)
											{
												$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
											}
											else if ((int)$map->hovermarker == 2)
											{
												$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
											}
										}		
										$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkTitle = titlePlacemark'. $currentmarker->id.';' ."\n";
										$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";
										if ((int)$map->markerlistpos != 0)
										{
											$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";	
										}
										if ($managePanelInfowin == 1)
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContentTabs(this.zhbdmInfowinContent);' ."\n";
										}	
										else
										{											
											// Open infobubble										
											$scripttext .= '  if (!infoBubble'. $currentmarker->id.'.isOpen())'."\n";
											$scripttext .= '  {'."\n";		
											$scripttext .= '  	infoBubble'. $currentmarker->id.'.open(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
											$scripttext .= '  }'."\n";
										}										

										if ($featureSpider != 0)
										{
											$scripttext .= '};' ."\n";
										}
										$scripttext .= '  });' ."\n";
									break;
									// Open Street View
									case 5:
										if (isset($map->streetview) && (int)$map->streetview != 0) 
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  panorama'.$mapDivSuffix.'.setPosition(latlng'. $currentmarker->id.');' ."\n";

											$mapSV = comZhBaiduMapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
											if ($mapSV != "")
											{
												$scripttext .= '  panorama'.$mapDivSuffix.'.setPov('.$mapSV.');'."\n";
											}
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
										else
										{
											$scripttext .= 'marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";												
											}
											
											$mapSV = comZhBaiduMapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
											$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
											$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
											$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
											$scripttext .= '  }' ."\n";
											// Hide hover window when feature enabled
											if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
											{
												if ((int)$map->hovermarker == 1)
												{
													$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
												}
												else if ((int)$map->hovermarker == 2)
												{
													$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
												}
											}
											//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";
											if ((int)$map->markerlistpos != 0)
											{
												$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
											}
											
											if ($mapSV == "")
											{
												$scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', \'\');'."\n";
											}
											else
											{
												$scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', '.$mapSV.');'."\n";
											}
											

											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
 											$scripttext .= '});' ."\n";
										}
									break;
									default:
										$scripttext .= '' ."\n";
									break;
								}
								
								// Action By Click - End
								}
							}
							else
							{
								// Action By click for update placemark = Open InfoWin
									$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";

									$scripttext .= 'var contentStringButtons'.$currentmarker->id.' = "" +' ."\n";
									$scripttext .= '    \'<hr />\'+' ."\n";					
									$scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlng'. $currentmarker->id.'.lat + \'" />\'+' ."\n";
									$scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlng'.$currentmarker->id.'.lng + \'" />\'+' ."\n";
									$scripttext .= '    \'<input name="marker_action" type="hidden" value="update" />\'+' ."\n";
									$scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
									$scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
									$scripttext .= '    \'<input name="markersubmit" type="submit" value="'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BUTTON_UPDATE' ).'" />\'+' ."\n";
									$scripttext .= '    \'</form>\'+' ."\n";		
									$scripttext .= '\'</div>\'+'."\n";
                                                                        $scripttext .= '\'</div>\'+'."\n";
		
									// Form Delete
									if ((int)$map->usermarkersdelete == 1)
									{
										$scripttext .= '\'<div id="contentDeletePlacemark">\'+'."\n";
										$scripttext .= '    \'<form id="deletePlacemarkForm'.$currentmarker->id.'" action="'.JURI::current().'" method="post">\'+'."\n";
										$scripttext .= '    \'<input name="marker_action" type="hidden" value="delete" />\'+' ."\n";
										$scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
										$scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
										$scripttext .= '    \'<input name="markersubmit" type="submit" value="'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BUTTON_DELETE' ).'" />\'+' ."\n";
										$scripttext .= '    \'</form>\'+' ."\n";		
										$scripttext .= '\'</div>\';'."\n";
									}
									else
									{
										$scripttext .= '\'\';'."\n";
									}
									
									$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentStringPart1'.$currentmarker->id.'+';
									$scripttext .= 'contentInsertPlacemarkIcon.replace(/insertPlacemarkForm/g,"updatePlacemarkForm'. $currentmarker->id.'")';
									$scripttext .= '.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", $currentmarker->icontype).'.png"\')';
									$scripttext .= '.replace(\'<option value="'.$currentmarker->icontype.'">'.$currentmarker->icontype.'</option>\', \'<option value="'.$currentmarker->icontype.'" selected="selected">'.$currentmarker->icontype.'</option>\')';
									$scripttext .= '+';
									$scripttext .= 'contentStringPart2'.$currentmarker->id.'+';
									$scripttext .= 'contentStringButtons'.$currentmarker->id;
									$scripttext .= ');' ."\n";
									//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
									$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', this.getPosition());' ."\n";
									
									$scripttext .= '  });' ."\n";

									$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'drag\', function(event) {' ."\n";

									$scripttext .= '        map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
									$scripttext .= '	latlng'. $currentmarker->id.' = event.point;';
									
									$scripttext .= '  });' ."\n";


							}
							
							// If user can change placemark - override content string - end
							
							if ($zhbdmObjectManager != 0)
							{
								// fix for 19.02.2013
								//  if not managed placemarks (not enabled)
								if ((isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker != 0))
								{
									$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
								}
								else
								{									
									// 22.08.2014 placemarks in clusters, therefore not only 0-cluster
									if ((isset($map->markercluster) && (int)$map->markercluster == 1))
									{
										if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
										}
										else
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAdd(0, '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
										}
									}
									else
									{
										if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAdd('.$currentmarker->markergroup.', '. $currentmarker->id.', marker'. $currentmarker->id.', null);'."\n";
										}
									}
									// /////
								}
							}
							
																		
							//
							// Generate list elements for each marker.
							$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemarklist_string(
												0,
												$mapDivSuffix, 
												$currentmarker, 
												$markerlistcssstyle,
												$map->markerlistpos,
												$map->markerlistcontent,
												$map->markerlistaction,
												$imgpathIcons);
							// Generating Placemark List - End
						}
										
						// Change Map center and set Center Placemark Action
						if ($currentPlacemarkCenter != "do not change")
						{
							if ((int)$currentPlacemarkCenter == $currentmarker->id)
							{
								$scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.(int)$currentPlacemarkCenter.');'."\n";
								$scripttext .= 'latlng'.$mapDivSuffix.' = latlng'.(int)$currentPlacemarkCenter.';'."\n";
								$scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
							}
						}
						
						if ($currentPlacemarkActionID != "do not change")
						{
							if ((int)$currentPlacemarkActionID == $currentmarker->id)
							{
						
								if ($currentPlacemarkAction != "")
								{
									$currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);
									
									for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
									{
										switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
										{
											case "":
											   // null
											break;
											case "do not change":
												// do not change
											break;
											case "click":
                                                                                                $scripttext .= '  marker'. (int)$currentPlacemarkActionID.'.dispatchEvent("click");' ."\n";
											break;
											case "bounce":
												$scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setAnimation(BMAP_ANIMATION_BOUNCE);'."\n";
											break;
											default:
												$imgimg = $imgpathIcons.str_replace("#", "%23", $currentPlacemarkExecuteArray[$i]).'.png';
                                                                                                $imgimg4size = $imgpath4size.$currentPlacemarkExecuteArray[$i].'.png';

                                                                                                list ($imgwidth, $imgheight) = getimagesize($imgimg4size);

                                                                                                $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setIcon(new BMap.Icon("'.$imgimg.'", new BMap.Size('.$imgwidth.','.$imgheight.')));'."\n";
                                                                                        break;
										}
									}
								}
							}
									
						}					
										
						if ((int)$currentmarker->openbaloon == 1)
						{
							$lastmarker2open = $currentmarker;
						}
						
						// End marker creation with lat,lng
					}
					else
					{
						// Begin marker creation with address by geocoding
						$scripttext .= '  geocoder'.$mapDivSuffix.'.getPoint("'.$currentmarker->addresstext.'", function(point) {'."\n";
                                                $scripttext .= '  if (point) {'."\n";
						$scripttext .= '    var latlng'. $currentmarker->id.' = new BMap.Point(point.lng, point.lat);' ."\n";

                                                //$scripttext .= '    alert("Geocode was successful");'."\n";
                                                //$scripttext .= '    alert("latlng="+latlng'. $currentmarker->id.');'."\n";

						// contentString - Begin
						$scripttext .= 'var contentString'. $currentmarker->id.' = "";'."\n";
						
						if (($allowUserMarker == 0)
						 || ((int)$map->usermarkersupdate == 0)
						 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
						 || ($currentUserID == 0)
						 || (isset($currentmarker->createdbyuser) 
						    && (((int)$currentmarker->createdbyuser != $currentUserID )
							   || ((int)$currentmarker->createdbyuser == 0)))
						 )
						{
							if (isset($map->useajax) && (int)$map->useajax != 0)
							{
								// do not create content string, create by loop only in the end
							}
							else
							{
								if (((int)$currentmarker->actionbyclick == 1)
									||
									(((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
									||  (($managePanelInfowin == 1) && (((int)$currentmarker->actionbyclick == 1) || (int)$currentmarker->actionbyclick == 4))
									)
								{
									if ($managePanelInfowin == 1)					
									{
										if ((int)$currentmarker->actionbyclick == 1)
										{										
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
													$mapDivSuffix, 
													$currentmarker, $map->usercontact, $map->useruser,
													$userContactAttrs, $service_DoDirection,
													$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo);											
											$scripttext .= ';'."\n";
										}
										else if ((int)$currentmarker->actionbyclick == 4)
										{
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_tabs_content_string(
													$mapDivSuffix, $currentmarker,
													comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
														$mapDivSuffix, 
														$currentmarker, $map->usercontact, $map->useruser,
														$userContactAttrs, $service_DoDirection,
														$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo),
													$imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang);											
											$scripttext .= ';'."\n";
										}
									}
									else
									{
										if (((int)$currentmarker->actionbyclick == 1)
										||
										(((int)$currentmarker->actionbyclick == 4) && ((int)$currentmarker->tab_info != 0))
										)
										{
											$scripttext .= 'contentString'. $currentmarker->id.' = '.
												comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
													$mapDivSuffix, 
													$currentmarker, $map->usercontact, $map->useruser,
													$userContactAttrs, $service_DoDirection,
													$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo);											
											$scripttext .= ';'."\n";
										}
										
									}									

 
								}
							}
						}
						else
						{
							// contentString - User Placemark can Update - Begin
							$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemark_content_update_string(
													$map->usermarkersicon, 
													$map->usercontact, 
													$currentmarker,
													$imgpathIcons, $imgpathUtils, $directoryIcons,
													$newMarkerGroupList
													);
							// contentString - User Placemark can Update - End
						}

						if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
						{
							if (isset($map->useajax) && (int)$map->useajax != 0)
							{
								// do not create content string, create by loop only in the end
							}
							else
							{
								if ((int)$map->hovermarker == 1
								  ||(int)$map->hovermarker == 2)
								{
									if ($currentmarker->hoverhtml != "")
									{
										$scripttext .= 'var hoverString'. $currentmarker->id.' = '.
											comZhBaiduMapPlacemarksHelper::get_placemark_hover_string(
												$currentmarker);									
									}

								}
							}
						}
						
						
						if ((int)$currentmarker->baloon != 0) 
						{

							
                                                        $scripttext .= 'var marker'. $currentmarker->id.' = new BMap.Marker(' ."\n";
							
							$scripttext .= '      latlng'. $currentmarker->id.', {' ."\n";

                                                        
							// Replace to new, because all charters are shown
							//$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'"' ."\n";
							if (isset($currentmarker->markercontent) &&
								(((int)$currentmarker->markercontent == 0) ||
								 ((int)$currentmarker->markercontent == 1) ||
								 ((int)$currentmarker->markercontent == 9))
								)
							{
								$scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'"' ."\n";
							}
							else
							{
								$scripttext .= '      title:""' ."\n";
							}
							$scripttext .= '});'."\n";
                                                        
                                                        if ((int)$currentmarker->baloon == 21
							 || (int)$currentmarker->baloon == 22
							 || (int)$currentmarker->baloon == 23
							 )
							{
                                                            if ($currentmarker->labelcontent != "")
                                                            {
                                                                
                                                                $scripttext .= ' var label'. $currentmarker->id.' = new BMap.Label("'.str_replace(array("\r", "\r\n", "\n"), '', str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->labelcontent))).'"';
                                                                if ((int)$currentmarker->labelanchorx != 0
                                                                || ((int)$currentmarker->labelanchory != 0))
                                                                {
                                                                        $scripttext .= ', { offset: new BMap.Size('. (int)$currentmarker->labelanchorx .', '.(int)$currentmarker->labelanchory .')}';
                                                                }
                                                                 $scripttext .=');'."\n";
                                                                 
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setLabel(label'. $currentmarker->id.');' ."\n";
                                                            }
                                                        }                                                        

							if ((isset($map->markercluster) && (int)$map->markercluster == 0))
							{
                                                            $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(marker'. $currentmarker->id.');' ."\n";
							}                                                        
							$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemark_icon_definition(
												$imgpathIcons,
												$imgpath4size,
												$currentmarker);

							switch ($currentmarker->baloon) 
							{
							case 1:
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_DROP);' ."\n";
                                                        break;
                                                        case 2:
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_BOUNCE);' ."\n";
                                                        break;
							case 3:
									$scripttext .= '' ."\n";
							break;					
							case 21:
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_DROP);' ."\n";
                                                        break;
                                                        case 22:
                                                                $scripttext .= 'marker'. $currentmarker->id.'.setAnimation(BMAP_ANIMATION_BOUNCE);' ."\n";
                                                        break;
                                                        default:
									$scripttext .= '' ."\n";
							break;
							}

							if (($allowUserMarker == 0)
							 || ((int)$map->usermarkersupdate == 0)
							 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
							 || ($currentUserID == 0)
							 || (isset($currentmarker->createdbyuser) 
								&& (((int)$currentmarker->createdbyuser != $currentUserID )
								   || ((int)$currentmarker->createdbyuser == 0))))
							{
                                                            $scripttext .= 'marker'. $currentmarker->id.'.disableDragging();' ."\n";
							}
							else
							{
                                                            $scripttext .= 'marker'. $currentmarker->id.'.enableDragging();' ."\n";
							}
							
							
							if ($externalmarkerlink == 1)
							{
								$scripttext .= 'PlacemarkByIDAdd('. $currentmarker->id.
								                                ', point.lat'.
                                                                                                ', point.lng'.
                                                                                                ', marker'. $currentmarker->id.
                                                                                                ', latlng'. $currentmarker->id.
                                                                                                ', '.$currentmarker->rating_value.
                                                                                                ');'."\n";
							}
							
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmRating = '.$currentmarker->rating_value.';' ."\n";							
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmPlacemarkID = '.$currentmarker->id.';' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmContactAttrs = userContactAttrs;' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmUserContact = "'.str_replace(';', ',', $map->usercontact).'";' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmUserUser = "'.str_replace(';', ',', $map->useruser).'";' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmOriginalPosition = latlng'.$currentmarker->id.';' ."\n";
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmInfowinContent = contentString'. $currentmarker->id.';' ."\n";	
							$scripttext .= '  marker'. $currentmarker->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->title)).'";' ."\n";	
                                                        $scripttext .= '  marker'. $currentmarker->id.'.zhbdmIncludeInList = '.$currentmarker->includeinlist.';' ."\n";							
                                                        if ($fv_override_placemark_list_search == 1)
                                                        {
                                                            $scripttext .= '  marker'. $currentmarker->id.'.zhbdmPlacemarkDescription = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentmarker->description)).'";' ."\n";														                                                            
                                                        }
							
							if (($featureSpider != 0)
							|| ($placemarkSearch != 0))
							{
								$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.allObjectsAddPlacemark('. $currentmarker->id.', marker'. $currentmarker->id.');'."\n";								
							}

							if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
							{
								if ($currentmarker->hoverhtml != "")
								{
									if (isset($map->useajax) && (int)$map->useajax != 0)
									{
										$scripttext .= '  ajaxmarkersADRhover'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";
										if ((int)$map->useajax == 1)
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("mootools", marker'. $currentmarker->id.');' ."\n";
										}
										else
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("jquery", marker'. $currentmarker->id.');' ."\n";
										}
									}
									else
									{
										if ((int)$map->hovermarker == 1)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
											//$scripttext .= '  this.zhbdmZIndex = this.getZIndex();' ."\n";
											//$scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
											//$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
											$scripttext .= '  });' ."\n";
											
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
											//$scripttext .= '    this.setZIndex(this.zhbdmZIndex);' ."\n";
											$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
											$scripttext .= '  });' ."\n";
										}
										else if ((int)$map->hovermarker == 2)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
											//$scripttext .= '  this.zhbdmZIndex = this.getZIndex();' ."\n";
											//$scripttext .= '  this.setZIndex(google.maps.Marker.MAX_ZINDEX);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverString'. $currentmarker->id.');' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  var anchor = new Hover_Anchor("placemark", this, event);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
											$scripttext .= '  });' ."\n";
											
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
											//$scripttext .= '    this.setZIndex(this.zhbdmZIndex);' ."\n";
											$scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
											$scripttext .= '  });' ."\n";
										}
									}
								}
							}
							
							//  If user can change placemark - override content string - begin
							//  override content string
							if (($allowUserMarker == 0)
							 || ((int)$map->usermarkersupdate == 0)
							 || (isset($currentmarker->userprotection) && (int)$currentmarker->userprotection == 1)
							 || ($currentUserID == 0)
							 || (isset($currentmarker->createdbyuser) 
								&& (((int)$currentmarker->createdbyuser != $currentUserID )
								   || ((int)$currentmarker->createdbyuser == 0)))
							 )
							{
								if (isset($map->useajax) && (int)$map->useajax != 0)
								{
									$scripttext .= '  ajaxmarkersADR'.$mapDivSuffix.'.push(marker'. $currentmarker->id.');'."\n";

									if ((int)$map->useajax == 1)
									{
										$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("mootools", marker'. $currentmarker->id.');' ."\n";
									}
									else
									{
										$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("jquery", marker'. $currentmarker->id.');' ."\n";
									}
								}
								else
								{
								// Action By Click - Begin
								switch ((int)$currentmarker->actionbyclick)
								{
									// None
									case 0:
										if ((int)$currentmarker->zoombyclick != 100)
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
											$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
									break;
									// Info
									case 1:
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
												if ((int)$currentmarker->zoombyclick != 100)
												{
													$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
													$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
												}
												$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
												// Close the other infobubbles
												$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
												$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
												$scripttext .= '  }' ."\n";
												// Hide hover window when feature enabled
												if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
												{
													if ((int)$map->hovermarker == 1)
													{
														$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
													}
													else if ((int)$map->hovermarker == 2)
													{
														$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
													}
												}
												// Open InfoWin
												$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkTitle = titlePlacemark'. $currentmarker->id.';' ."\n";
												$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";
												if ((int)$map->markerlistpos != 0)
												{
													$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";	
												}
												if ($managePanelInfowin == 1)
												{
													$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContent(this.zhbdmInfowinContent);' ."\n";
												}	
												else
												{
													$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
													//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
													$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', this.getPosition());' ."\n";
												}
												if (isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))
												{
													$scripttext .= '  PlacemarkRateDivOut'.$mapDivSuffix.'('. $currentmarker->id.', 5);' ."\n";
												}
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
									break;
									// Link
									case 2:
										if ($currentmarker->hrefsite != "")
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  window.open("'.$currentmarker->hrefsite.'");' ."\n";
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
										else
										{
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
													$scripttext .= '}' ."\n";
													$scripttext .= 'else {' ."\n";
												}
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											
												if ($featureSpider != 0)
												{
													$scripttext .= '};' ."\n";
												}
												$scripttext .= '  });' ."\n";
											}
										}
									break;
									// Link in self
									case 3:
										if ($currentmarker->hrefsite != "")
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  window.location = "'.$currentmarker->hrefsite.'";' ."\n";
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
										else
										{
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
												if ($featureSpider != 0)
												{
													$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
													$scripttext .= '}' ."\n";
													$scripttext .= 'else {' ."\n";
												}
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											
												if ($featureSpider != 0)
												{
													$scripttext .= '};' ."\n";
												}
												$scripttext .= '  });' ."\n";
											}
										}
									break;
									// InfoBubble
									case 4:
										if ($managePanelInfowin == 0)
										{
											
											// InfoBubble Create - Begin
											$scripttext .= '  infoBubble'. $currentmarker->id.' = new InfoBubble('."\n";
											$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemark_infobubble_style_string($currentmarker, '');
											$scripttext .= '  );'."\n";
											
											$scripttext .= '  infobubblemarkers'.$mapDivSuffix.'.push(infoBubble'. $currentmarker->id.');'."\n";

										
											if ((int)$currentmarker->tab_info == 1)
											{					
												$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
												str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
											}
											
											if ((int)$currentmarker->tab_info == 9)
											{	
													$scripttext .= '  infoBubble'. $currentmarker->id.'.setContent(contentString'. $currentmarker->id.');'."\n";
											}
											else
											{
												
												if ($currentmarker->tab1 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1)).'\');'."\n";
												}
												if ($currentmarker->tab2 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2)).'\');'."\n";
												}
												if ($currentmarker->tab3 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3)).'\');'."\n";
												}
												if ($currentmarker->tab4 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4)).'\');'."\n";
												}
												if ($currentmarker->tab5 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5)).'\');'."\n";
												}
												if ($currentmarker->tab6 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6)).'\');'."\n";
												}
												if ($currentmarker->tab7 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7)).'\');'."\n";
												}
												if ($currentmarker->tab8 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8)).'\');'."\n";
												}
												if ($currentmarker->tab9 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9)).'\');'."\n";
												}
												if ($currentmarker->tab10 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10)).'\');'."\n";
												}
												if ($currentmarker->tab11 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11)).'\');'."\n";
												}
												if ($currentmarker->tab12 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12)).'\');'."\n";
												}
												if ($currentmarker->tab13 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13)).'\');'."\n";
												}
												if ($currentmarker->tab14 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14)).'\');'."\n";
												}
												if ($currentmarker->tab15 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15)).'\');'."\n";
												}
												if ($currentmarker->tab16 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16)).'\');'."\n";
												}
												if ($currentmarker->tab17 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17)).'\');'."\n";
												}
												if ($currentmarker->tab18 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18)).'\');'."\n";
												}
												if ($currentmarker->tab19 != "")
												{
													$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19title)).'\', \''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19)).'\');'."\n";
												}
											}
											
											
											if ((int)$currentmarker->tab_info == 2)
											{					
												$scripttext .= '  infoBubble'. $currentmarker->id.'.addTab(\''.str_replace("'", "\'", 
												str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))).'\', contentString'. $currentmarker->id.');'."\n";
											}
											
											// InfoBubble Create - End
										}
										$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
										if ($featureSpider != 0)
										{
											$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
											$scripttext .= '}' ."\n";
											$scripttext .= 'else {' ."\n";
										}
										if ((int)$currentmarker->zoombyclick != 100)
										{
											$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
											$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
										}
										// Close the other infowin and infobubbles
										$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
										$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
										$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
										$scripttext .= '  }' ."\n";
										// Hide hover window when feature enabled
										if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
										{
											if ((int)$map->hovermarker == 1)
											{
												$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
											}
											else if ((int)$map->hovermarker == 2)
											{
												$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
											}
										}		
										$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkTitle = titlePlacemark'. $currentmarker->id.';' ."\n";
										$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";
										if ((int)$map->markerlistpos != 0)
										{
											$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";	
										}
										if ($managePanelInfowin == 1)
										{
											$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPlacemarkContentTabs(this.zhbdmInfowinContent);' ."\n";
										}	
										else
										{											
											// Open infobubble										
											$scripttext .= '  if (!infoBubble'. $currentmarker->id.'.isOpen())'."\n";
											$scripttext .= '  {'."\n";		
											$scripttext .= '  	infoBubble'. $currentmarker->id.'.open(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
											$scripttext .= '  }'."\n";
										}										

										if ($featureSpider != 0)
										{
											$scripttext .= '};' ."\n";
										}
										$scripttext .= '  });' ."\n";									
									break;
									// Open Street View
									case 5:
										if (isset($map->streetview) && (int)$map->streetview != 0) 
										{
											$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";
											}
											$scripttext .= '  panorama'.$mapDivSuffix.'.setPosition(latlng'. $currentmarker->id.');' ."\n";
											$mapSV = comZhBaiduMapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
											if ($mapSV != "")
											{
												$scripttext .= '  panorama'.$mapDivSuffix.'.setPov('.$mapSV.');'."\n";
											}
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
											$scripttext .= '  });' ."\n";
										}
										else
										{
											$scripttext .= 'marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";
											if ($featureSpider != 0)
											{
												$scripttext .= 'if (zhbdmObjMgr'.$mapDivSuffix.'.canDoClick('. $currentmarker->id.')==1) {' ."\n";
												$scripttext .= '}' ."\n";
												$scripttext .= 'else {' ."\n";
											}
											if ((int)$currentmarker->zoombyclick != 100)
											{
												$scripttext .= '  map'.$mapDivSuffix.'.setCenter(this.getPosition());' ."\n";
												$scripttext .= '  map'.$mapDivSuffix.'.setZoom('.(int)$currentmarker->zoombyclick.');' ."\n";												
											}
											$mapSV = comZhBaiduMapPlacemarksHelper::get_StreetViewOptions($currentmarker->streetviewstyleid);
											$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
											$scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
											$scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
											$scripttext .= '  }' ."\n";
											// Hide hover window when feature enabled
											if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
											{
												if ((int)$map->hovermarker == 1)
												{
													$scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
												}
												else if ((int)$map->hovermarker == 2)
												{
													$scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
												}
											}
											//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
											$scripttext .= '  infowindow'.$mapDivSuffix.'.zhbdmPlacemarkOriginalPosition = this.zhbdmOriginalPosition;' ."\n";

											if ((int)$map->markerlistpos != 0)
											{
												$scripttext .= '  Map_Animate_Marker_Hide(map'.$mapDivSuffix.', marker'. $currentmarker->id.');'."\n";
											}
											
											if ($mapSV == "")
											{
												$scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', \'\');'."\n";
											}
											else
											{
												$scripttext .= 'showPlacemarkPanorama'.$mapDivSuffix.'('.$currentmarker->streetviewinfowinw.','.$currentmarker->streetviewinfowinh.', '.$mapSV.');'."\n";
											}
											
											if ($featureSpider != 0)
											{
												$scripttext .= '};' ."\n";
											}
 											$scripttext .= '});' ."\n";
										}
									break;
									default:
										$scripttext .= '' ."\n";
									break;
								}
								// Action By Click - End
								}
							}
							else
							{
								// Action By click for update placemark = Open InfoWin
									$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'click\', function(event) {' ."\n";

									$scripttext .= 'var contentStringButtons'.$currentmarker->id.' = "" +' ."\n";
									$scripttext .= '    \'<hr />\'+' ."\n";					
									$scripttext .= '    \'<input name="markerlat" type="hidden" value="\'+latlng'. $currentmarker->id.'.lat + \'" />\'+' ."\n";
									$scripttext .= '    \'<input name="markerlng" type="hidden" value="\'+latlng'.$currentmarker->id.'.lng + \'" />\'+' ."\n";
									$scripttext .= '    \'<input name="marker_action" type="hidden" value="update" />\'+' ."\n";
									$scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
									$scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
									$scripttext .= '    \'<input name="markersubmit" type="submit" value="'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BUTTON_UPDATE' ).'" />\'+' ."\n";
									$scripttext .= '    \'</form>\'+' ."\n";		
									$scripttext .= '\'</div>\'+'."\n";
                                                                        $scripttext .= '\'</div>\'+'."\n";
		
									// Form Delete
									if ((int)$map->usermarkersdelete == 1)
									{
										$scripttext .= '\'<div id="contentDeletePlacemark">\'+'."\n";
										$scripttext .= '    \'<form id="deletePlacemarkForm'.$currentmarker->id.'" action="'.JURI::current().'" method="post">\'+'."\n";
										$scripttext .= '    \'<input name="marker_action" type="hidden" value="delete" />\'+' ."\n";
										$scripttext .= '    \'<input name="markerid" type="hidden" value="'.$currentmarker->id.'" />\'+' ."\n";
										$scripttext .= '    \'<input name="contactid" type="hidden" value="'.$currentmarker->contactid.'" />\'+' ."\n";
										$scripttext .= '    \'<input name="markersubmit" type="submit" value="'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BUTTON_DELETE' ).'" />\'+' ."\n";
										$scripttext .= '    \'</form>\'+' ."\n";		
										$scripttext .= '\'</div>\';'."\n";
									}
									else
									{
										$scripttext .= '\'\';'."\n";
									}
									
									$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentStringPart1'.$currentmarker->id.'+';
									$scripttext .= 'contentInsertPlacemarkIcon.replace(/insertPlacemarkForm/g,"updatePlacemarkForm'. $currentmarker->id.'")';
									$scripttext .= '.replace(\'"markericonimage" src="\', \'"markericonimage" src="'.$imgpathIcons.str_replace("#", "%23", $currentmarker->icontype).'.png"\')';
									$scripttext .= '.replace(\'<option value="'.$currentmarker->icontype.'">'.$currentmarker->icontype.'</option>\', \'<option value="'.$currentmarker->icontype.'" selected="selected">'.$currentmarker->icontype.'</option>\')';
									$scripttext .= '+';
									$scripttext .= 'contentStringPart2'.$currentmarker->id.'+';
									$scripttext .= 'contentStringButtons'.$currentmarker->id;
									$scripttext .= ');' ."\n";
									//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(this.getPosition());' ."\n";
									$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', this.getPosition());' ."\n";
									
									$scripttext .= '  });' ."\n";

									$scripttext .= '  marker'. $currentmarker->id.'.addEventListener( \'drag\', function(event) {' ."\n";

									$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
									$scripttext .= '	latlng'. $currentmarker->id.' = event.point;';
									
									$scripttext .= '  });' ."\n";


							}
							
							// If user can change placemark - override content string - end


							if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
								&& 
								/* 19.02.2013 
								   for flexible support group management 
								   and have ability to set off placemarks from group managenent */
							    (isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker == 1)
								)
							{
								if ((isset($map->markercluster) && (int)$map->markercluster == 1))
								{
									if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
									{
										if ($currentmarker->activeincluster == 1
										|| $currentmarker->markergroup == 0)
										{
											$scripttext .= 'markerCluster'.$currentmarker->markergroup.'.addMarker(marker'. $currentmarker->id.');' ."\n";
										}
									}
									else
									{
										if ($currentmarker->activeincluster == 1
										|| $currentmarker->markergroup == 0)
										{
											$scripttext .= 'markerCluster0.addMarker(marker'. $currentmarker->id.');' ."\n";
										}
									}
								}
								else
								{
									// No need add to cluster
								}
							}
							else
							{
								if ((isset($map->markercluster) && (int)$map->markercluster == 1))
								{
									if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
									{
										$scripttext .= 'markerCluster'.$currentmarker->markergroup.'.addMarker(marker'. $currentmarker->id.');' ."\n";
									}
									else
									{
										$scripttext .= 'markerCluster0.addMarker(marker'. $currentmarker->id.');' ."\n";
									}
								}
								else
								{
									// No need add to cluster
								}
							}

	
					
							//
							// Generate list elements for each marker.
							$scripttext .= comZhBaiduMapPlacemarksHelper::get_placemarklist_string(
												0,
												$mapDivSuffix, 
												$currentmarker, 
												$markerlistcssstyle,
												$map->markerlistpos,
												$map->markerlistcontent,
												$map->markerlistaction,
												$imgpathIcons);
							// Generating Placemark List - End
						}
						
						// Change Map center and set Center Placemark Action
						if ($currentPlacemarkCenter != "do not change")
						{
							if ((int)$currentPlacemarkCenter == $currentmarker->id)
							{
								$scripttext .= 'map'.$mapDivSuffix.'.setCenter(latlng'.(int)$currentPlacemarkCenter.');'."\n";
								$scripttext .= 'latlng'.$mapDivSuffix.' = latlng'.(int)$currentPlacemarkCenter.';'."\n";
								$scripttext .= 'routedestination'.$mapDivSuffix.' = latlng'.$mapDivSuffix.';'."\n";
							}
						}
						
						if ($currentPlacemarkActionID != "do not change")
						{
							if ((int)$currentPlacemarkActionID == $currentmarker->id)
							{
						
								if ($currentPlacemarkAction != "")
								{
									$currentPlacemarkExecuteArray = explode(";", $currentPlacemarkAction);
									
									for($i = 0; $i < count($currentPlacemarkExecuteArray); $i++) 
									{
										switch (strtolower(trim($currentPlacemarkExecuteArray[$i])))
										{
											case "":
											   // null
											break;
											case "do not change":
												// do not change
											break;
											case "click":
                                                                                                $scripttext .= '  marker'. (int)$currentPlacemarkActionID.'.dispatchEvent("click");' ."\n";
											break;
											case "bounce":
												$scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setAnimation(BMAP_ANIMATION_BOUNCE);'."\n";
											break;
											default:
												$imgimg = $imgpathIcons.str_replace("#", "%23", $currentPlacemarkExecuteArray[$i]).'.png';
                                                                                                $imgimg4size = $imgpath4size.$currentPlacemarkExecuteArray[$i].'.png';

                                                                                                list ($imgwidth, $imgheight) = getimagesize($imgimg4size);

                                                                                                $scripttext .= 'marker'. (int)$currentPlacemarkActionID.'.setIcon(new BMap.Icon("'.$imgimg.'", new BMap.Size('.$imgwidth.','.$imgheight.')));'."\n";
                                                                                        break;
										}
									}
								}
							}
									
						}					
						
					
						if ((int)$currentmarker->openbaloon == 1)
						{
							$lastmarker2open = $currentmarker;
						}

						// End marker creation with address
      					$scripttext .= '  }'."\n";
						$scripttext .= '  else'."\n";
						$scripttext .= '  {'."\n";
        				$scripttext .= '    alert("'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': '.$currentmarker->addresstext.'" + "\n"+"id:'. $currentmarker->id.'");'."\n";
      					$scripttext .= '  }'."\n";
    					$scripttext .= '});'."\n";
					}
										
				}
				
				
			}
			// End restriction
		}
		// Main loop by markers - End
	
	}

	// Ajax Marker Listeners
	if (isset($map->useajax) && (int)$map->useajax != 0) 
	{
        //$scripttext .= 'alert("begin: '.$mapDivSuffix.'");' ."\n";
        $scripttext .= 'for (var i=0; i<ajaxmarkersLL'.$mapDivSuffix.'.length; i++)' ."\n";
        $scripttext .= '{' ."\n";
		//$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].zhbdmPlacemarkID);' ."\n";
		if ((int)$map->useajax == 1)
		{
			$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("mootools", ajaxmarkersLL'.$mapDivSuffix.'[i]);' ."\n";
		}
		else
		{
			$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddListeners("jquery", ajaxmarkersLL'.$mapDivSuffix.'[i]);' ."\n";
		}
        $scripttext .= '}' ."\n";
        //scripttext .= 'alert("-end");' ."\n";
		

            // For Hovering Feature - Begin
            if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
            {
                    $scripttext .= 'for (var i=0; i<ajaxmarkersLLhover'.$mapDivSuffix.'.length; i++)' ."\n";
                    $scripttext .= '{' ."\n";
                    //$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].zhbdmPlacemarkID);' ."\n";
                    if ((int)$map->useajax == 1)
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("mootools", ajaxmarkersLLhover'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    else
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkAddHoverListeners("jquery", ajaxmarkersLLhover'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    $scripttext .= '}' ."\n";
            }
            // For Hovering Feature - End
	   
	}

	// Execute Action - Open InfoWin and etc
	if (isset($lastmarker2open)
	&& (isset($map->useajax) && (int)$map->useajax == 0))
	{
		if ((int)$lastmarker2open->baloon != 0)
		{
			switch ((int)$lastmarker2open->actionbyclick)
			{
				case 0:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				case 1:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				case 2:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				case 3:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				case 4:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				case 5:
					$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
					//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
					$scripttext .= '  marker'. $lastmarker2open->id.'.dispatchEvent("click");' ."\n";
				break;
				default:
					$scripttext .= '' ."\n";
				break;
			}
		}
		else
		{
				$scripttext .= 'var contentString'. $lastmarker2open->id.' = '.
					comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
						$mapDivSuffix,
						$lastmarker2open, $map->usercontact, $map->useruser,
						$userContactAttrs, $service_DoDirection,
						$imgpathIcons, $imgpathUtils, $directoryIcons, $map->placemark_rating, $main_lang, $placemarkTitleTag, $map->showcreateinfo);
				$scripttext .= ';'."\n";
				$scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
				$scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(contentString'. $lastmarker2open->id.');' ."\n";
				//$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(latlng'. $lastmarker2open->id.');' ."\n";
				$scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', latlng'. $lastmarker2open->id.');' ."\n";
		}

	}

	if ($placemarkSearch != 0)
	{
                if ($fv_override_placemark_list_mapping_type != 0)
                {
                    // remove new lines
                    // change comma to semicolon
                    // fix double quotes, back slash    
                    if ($fv_override_placemark_list_mapping_type == 100)
                    {
                        $fv_override_placemark_list_accent = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_placemark_list_accent))));
                        $fv_override_placemark_list_mapping = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_placemark_list_mapping))));                       
                    }
                    else
                    {
                        $fv_override_placemark_list_accent = "";
                        $fv_override_placemark_list_mapping = "";
                        
                    }
                    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkListSearchMapping('.$fv_override_placemark_list_mapping_type.','.$fv_override_placemark_list_accent_side.', "'.$fv_override_placemark_list_accent.'"'.', "'.$fv_override_placemark_list_mapping.'"'.');'."\n";
                }
		$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.enablePlacemarkListSearch('.$fv_override_placemark_list_search.');'."\n";
	}
 
	if ($groupSearch != 0)
	{

                if ($fv_override_group_list_mapping_type != 0)
                {
                    // remove new lines
                    // change comma to semicolon
                    // fix double quotes, back slash    
                    if ($fv_override_group_list_mapping_type == 100)
                    {
                        $fv_override_group_list_accent = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_group_list_accent))));
                        $fv_override_group_list_mapping = str_replace("\\", "\\\\", str_replace("\"", "QQ", str_replace(",", ";", str_replace(array("\r", "\r\n", "\n", "\"", "\'", " "), '', $fv_override_group_list_mapping))));
                    }
                    else
                    {
                        $fv_override_group_list_accent = "";
                        $fv_override_group_list_mapping = "";
                    }
                    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setGroupListSearchMapping('.$fv_override_group_list_mapping_type.','.$fv_override_group_list_accent_side.', "'.$fv_override_group_list_accent.'"'.', "'.$fv_override_group_list_mapping.'"'.');'."\n";
                }            
		$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.enableGroupListSearch('.$fv_override_group_list_search.');'."\n";
	}        
 
	// 16.08.2013 - ajax loading
        //$scripttext .= 'x='.$zhbdmObjectManager.';' ."\n";
        //$scripttext .= 'y='.$ajaxLoadObjects.';' ."\n";
        //$scripttext .= 'z='.$ajaxLoadObjectType.';' ."\n";
        
	if ($zhbdmObjectManager != 0)
	{
		if ($ajaxLoadObjects != 0)
		{

			if ($ajaxLoadObjectType == 2)
			{				
				$scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'tilesloaded\', LoadMapObjects'.$mapDivSuffix.');' ."\n";	
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'zoomend\', LoadMapObjects'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'moveend\', LoadMapObjects'.$mapDivSuffix.');' ."\n";                           
	
				$scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'load\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'tilesloaded\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'zoomend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'moveend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                
			}
			else
			{		
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'load\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'tilesloaded\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'zoomend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= 'map'.$mapDivSuffix.'.addEventListener( \'moveend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
				
			}
			
		}
	}
        
	// Routers
	if (isset($routers) && !empty($routers)) 
	{
		$routepanelcount = 0;
		$routepaneltotalcount = 0;
		$scripttext .= 'var directionsService = new google.maps.DirectionsService();' ."\n";

		$routeHTMLdescription ='';
		
		//Begin for each Route
		foreach ($routers as $key => $currentrouter) 
		{
			// Start Route by Address
			if ($currentrouter->route != "")
			{
				$routername ='';
				$routername = 'route'. $currentrouter->id;
				$scripttext .= 'var directionsDisplay'. $currentrouter->id.' = new google.maps.DirectionsRenderer();' ."\n";
				$scripttext .= 'map'.$mapDivSuffix.'.addOverlay(directionsDisplay'. $currentrouter->id.');' ."\n";

				if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
				{
					$scripttext .= 'directionsDisplay'. $currentrouter->id.'.setPanel(document.getElementById("BDMapsRoutePanel'.$mapDivSuffix.'"));' ."\n";
					$routepanelcount++;
					if (isset($currentrouter->showpaneltotal) && (int)$currentrouter->showpaneltotal == 1) 
					{
						$routepaneltotalcount++;
					}
				}
				
				$cs = explode(";", $currentrouter->route);
				$cs_total = count($cs)-1;
				$cs_idx = 0;
				$wp_list = '';
				foreach($cs as $curroute)
				{	
					if ($cs_idx == 0)
					{
						$scripttext .= 'var startposition='.$curroute.';'."\n";
					}
					else if ($cs_idx == $cs_total)
					{
						$scripttext .= 'var endposition='.$curroute.';'."\n";
					}
					else
					{
						if ($wp_list == '')
						{
							$wp_list .= '{ location: '.$curroute.', stopover:true }';
						}
						else
						{
							$wp_list .= ', '."\n".'{ location: '.$curroute.', stopover:true }';
						}
					}

					$cs_idx += 1;
				}

					  
				
				$scripttext .= 'var rendererOptions'. $currentrouter->id.' = {' ."\n";
				if (isset($currentrouter->draggable))
				{
					switch ($currentrouter->draggable) 
					{
					case 0:
						$scripttext .= 'draggable:false' ."\n";
					break;
					case 1:
						$scripttext .= 'draggable:true' ."\n";
					break;
					default:
						$scripttext .= 'draggable:false' ."\n";
					break;
					}
				}
				if (isset($currentrouter->showtype))
				{
					switch ($currentrouter->showtype) 
					{
					case 0:
						$scripttext .= ', preserveViewport:false' ."\n";
					break;
					case 1:
						$scripttext .= ', preserveViewport:true' ."\n";
					break;
					default:
						$scripttext .= '' ."\n";
					break;
					}
				}

				if (isset($currentrouter->suppressmarkers))
				{
					switch ($currentrouter->suppressmarkers) 
					{
					case 0:
						$scripttext .= ', suppressMarkers:false' ."\n";
					break;
					case 1:
						$scripttext .= ', suppressMarkers:true' ."\n";
					break;
					default:
						$scripttext .= '' ."\n";
					break;
					}
				}
				
				// now you can alter route color options
				$scripttext .= ', polylineOptions: {' ."\n"; 
				$scripttext .= '    strokeColor: "'.$currentrouter->color.'"'."\n";
				$scripttext .= '  , strokeOpacity: '.$currentrouter->opacity."\n";
				$scripttext .= '  , strokeWeight: '.$currentrouter->weight."\n";
				$scripttext .= '}' ."\n";
				
				$scripttext .= '};' ."\n";
				
				$scripttext .= 'directionsDisplay'. $currentrouter->id.'.setOptions(rendererOptions'. $currentrouter->id.');' ."\n";

				$scripttext .= '  var directionsRequest'. $currentrouter->id.' = {' ."\n";
				$scripttext .= '    origin: startposition, ' ."\n";
				$scripttext .= '    destination: endposition,' ."\n";
				if ($wp_list != '')
				{
					$scripttext .= ' waypoints: ['.$wp_list.'],'."\n";
				}
				if (isset($currentrouter->providealt) && (int)$currentrouter->providealt == 1) 
				{
					$scripttext .= 'provideRouteAlternatives: true,' ."\n";
				} else {
					$scripttext .= 'provideRouteAlternatives: false,' ."\n";
				}
				if (isset($currentrouter->avoidhighways) && (int)$currentrouter->avoidhighways == 1) 
				{
					$scripttext .= 'avoidHighways: true,' ."\n";
				} else {
					$scripttext .= 'avoidHighways: false,' ."\n";
				}
				if (isset($currentrouter->avoidtolls) && (int)$currentrouter->avoidtolls == 1) 
				{
					$scripttext .= 'avoidTolls: true,' ."\n";
				} else {
					$scripttext .= 'avoidTolls: false,' ."\n";
				}
				if (isset($currentrouter->optimizewaypoints) && (int)$currentrouter->optimizewaypoints == 1) 
				{
					$scripttext .= 'optimizeWaypoints: true,' ."\n";
				} else {
					$scripttext .= 'optimizeWaypoints: false,' ."\n";
				}

				if (isset($currentrouter->travelmode)) 
				{
					switch ($currentrouter->travelmode) 
					{
					case 0:
					break;
					case 1:
						$scripttext .= 'travelMode: google.maps.TravelMode.DRIVING,' ."\n";
					break;
					case 2:
						$scripttext .= 'travelMode: google.maps.TravelMode.WALKING,' ."\n";
					break;
					case 3:
						$scripttext .= 'travelMode: google.maps.TravelMode.BICYCLING,' ."\n";
					break;
					case 4:
						$scripttext .= 'travelMode: google.maps.TravelMode.TRANSIT,' ."\n";
					break;
					default:
						$scripttext .= '' ."\n";
					break;
					}
				}

				if (isset($currentrouter->unitsystem)) 
				{
					switch ($currentrouter->unitsystem) 
					{
					case 0:
					break;
					case 1:
						$scripttext .= 'unitSystem: google.maps.UnitSystem.METRIC' ."\n";
					break;
					case 2:
						$scripttext .= 'unitSystem: google.maps.UnitSystem.IMPERIAL' ."\n";
					break;
					default:
						$scripttext .= '' ."\n";
					break;
					}
				}
				$scripttext .= '  };' ."\n";

				
				if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
				{
					$scripttext .= 'google.maps.event.addEventListener(directionsDisplay'. $currentrouter->id.', \'directions_changed\', function() {' ."\n";
					$scripttext .= '  computeTotalDistance(directionsDisplay'. $currentrouter->id.'.directions);' ."\n";
					$scripttext .= '});' ."\n";
				}
				
				$scripttext .= '  directionsService.route(directionsRequest'. $currentrouter->id.', function(result, status) {' ."\n";
				$scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
				$scripttext .= '      directionsDisplay'. $currentrouter->id.'.setDirections(result);' ."\n";
				$scripttext .= '    }' ."\n";
				$scripttext .= '    else {' ."\n";
				$scripttext .= '		alert("'.JText::_('COM_ZHBAIDUMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
				$scripttext .= '    }' ."\n";
				$scripttext .= '});' ."\n";

			}
			// End Route by Address
			// Start Route by Marker
			if ($currentrouter->routebymarker != "")
			{
				$routername ='';
				$routername = 'routeByMarker'. $currentrouter->id;
				$scripttext .= 'var directionsDisplayByMarker'. $currentrouter->id.' = new google.maps.DirectionsRenderer();' ."\n";
				$scripttext .= 'map'.$mapDivSuffix.'.addOverlay(directionsDisplayByMarker'. $currentrouter->id.');' ."\n";

				if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
				{
					$scripttext .= 'directionsDisplayByMarker'. $currentrouter->id.'.setPanel(document.getElementById("BDMapsRoutePanel'.$mapDivSuffix.'"));' ."\n";
					$routepanelcount++;
					if (isset($currentrouter->showpaneltotal) && (int)$currentrouter->showpaneltotal == 1) 
					{
						$routepaneltotalcount++;
					}
				}
				
				$cs = explode(";", $currentrouter->routebymarker);
				$cs_total = count($cs)-1;
				$cs_idx = 0;
				$wp_list = '';
				$skipRouteCreation = 0;
				foreach($cs as $curroute)
				{	
					$currouteLatLng = comZhBaiduMapPlacemarksHelper::get_placemark_coordinates($curroute);
					//$scripttext .= 'alert("'.$currouteLatLng.'");'."\n";

					if ($currouteLatLng != "")
					{
						if ($currouteLatLng == "geocode")
						{
							$scripttext .= 'alert(\''.JText::_('COM_ZHBAIDUMAP_MAPROUTER_FINDMARKER_ERROR_GEOCODE').' '.$curroute.'\');'."\n";
							$skipRouteCreation = 1;
						}
						else
						{
							if ($cs_idx == 0)
							{
								$scripttext .= 'var startposition='.$currouteLatLng.';'."\n";
							}
							else if ($cs_idx == $cs_total)
							{
								$scripttext .= 'var endposition='.$currouteLatLng.';'."\n";
							}
							else
							{
								if ($wp_list == '')
								{
									$wp_list .= '{ location: '.$currouteLatLng.', stopover:true }';
								}
								else
								{
									$wp_list .= ', '."\n".'{ location: '.$currouteLatLng.', stopover:true }';
								}
							}
						}
					}
					else
					{
						$scripttext .= 'alert(\''.JText::_('COM_ZHBAIDUMAP_MAPROUTER_FINDMARKER_ERROR_REASON').' '.$curroute.'\');'."\n";
						$skipRouteCreation = 1;
					}

					$cs_idx += 1;
				}

					  
				if ($skipRouteCreation == 0)
				{
					$scripttext .= 'var rendererOptionsByMarker'. $currentrouter->id.' = {' ."\n";
					if (isset($currentrouter->draggable))
					{
						switch ($currentrouter->draggable) 
						{
						case 0:
							$scripttext .= 'draggable:false' ."\n";
						break;
						case 1:
							$scripttext .= 'draggable:true' ."\n";
						break;
						default:
							$scripttext .= 'draggable:false' ."\n";
						break;
						}
					}
					if (isset($currentrouter->showtype))
					{
						switch ($currentrouter->showtype) 
						{
						case 0:
							$scripttext .= ', preserveViewport:false' ."\n";
						break;
						case 1:
							$scripttext .= ', preserveViewport:true' ."\n";
						break;
						default:
							$scripttext .= '' ."\n";
						break;
						}
					}

					if (isset($currentrouter->suppressmarkers))
					{
						switch ($currentrouter->suppressmarkers) 
						{
						case 0:
							$scripttext .= ', suppressMarkers:false' ."\n";
						break;
						case 1:
							$scripttext .= ', suppressMarkers:true' ."\n";
						break;
						default:
							$scripttext .= '' ."\n";
						break;
						}
					}

					// now you can alter route color options
					$scripttext .= ', polylineOptions: {' ."\n"; 
					$scripttext .= '    strokeColor: "'.$currentrouter->color.'"'."\n";
					$scripttext .= '  , strokeOpacity: '.$currentrouter->opacity."\n";
					$scripttext .= '  , strokeWeight: '.$currentrouter->weight."\n";
					$scripttext .= '}' ."\n";	
					

					$scripttext .= '};' ."\n";
					
					$scripttext .= 'directionsDisplayByMarker'. $currentrouter->id.'.setOptions(rendererOptionsByMarker'. $currentrouter->id.');' ."\n";

					$scripttext .= '  var directionsRequestByMarker'. $currentrouter->id.' = {' ."\n";
					$scripttext .= '    origin: startposition, ' ."\n";
					$scripttext .= '    destination: endposition,' ."\n";
					if ($wp_list != '')
					{
						$scripttext .= ' waypoints: ['.$wp_list.'],'."\n";
					}
					if (isset($currentrouter->providealt) && (int)$currentrouter->providealt == 1) 
					{
						$scripttext .= 'provideRouteAlternatives: true,' ."\n";
					} else {
						$scripttext .= 'provideRouteAlternatives: false,' ."\n";
					}
					if (isset($currentrouter->avoidhighways) && (int)$currentrouter->avoidhighways == 1) 
					{
						$scripttext .= 'avoidHighways: true,' ."\n";
					} else {
						$scripttext .= 'avoidHighways: false,' ."\n";
					}
					if (isset($currentrouter->avoidtolls) && (int)$currentrouter->avoidtolls == 1) 
					{
						$scripttext .= 'avoidTolls: true,' ."\n";
					} else {
						$scripttext .= 'avoidTolls: false,' ."\n";
					}
					if (isset($currentrouter->optimizewaypoints) && (int)$currentrouter->optimizewaypoints == 1) 
					{
						$scripttext .= 'optimizeWaypoints: true,' ."\n";
					} else {
						$scripttext .= 'optimizeWaypoints: false,' ."\n";
					}

					if (isset($currentrouter->travelmode)) 
					{
						switch ($currentrouter->travelmode) 
						{
						case 0:
						break;
						case 1:
							$scripttext .= 'travelMode: google.maps.TravelMode.DRIVING,' ."\n";
						break;
						case 2:
							$scripttext .= 'travelMode: google.maps.TravelMode.WALKING,' ."\n";
						break;
						case 3:
							$scripttext .= 'travelMode: google.maps.TravelMode.BICYCLING,' ."\n";
						break;
						case 4:
							$scripttext .= 'travelMode: google.maps.TravelMode.TRANSIT,' ."\n";
						break;
						default:
							$scripttext .= '' ."\n";
						break;
						}
					}

					if (isset($currentrouter->unitsystem)) 
					{
						switch ($currentrouter->unitsystem) 
						{
						case 0:
						break;
						case 1:
							$scripttext .= 'unitSystem: google.maps.UnitSystem.METRIC' ."\n";
						break;
						case 2:
							$scripttext .= 'unitSystem: google.maps.UnitSystem.IMPERIAL' ."\n";
						break;
						default:
							$scripttext .= '' ."\n";
						break;
						}
					}
					$scripttext .= '  };' ."\n";
					
					if (isset($currentrouter->showpanel) && (int)$currentrouter->showpanel == 1) 
					{
						$scripttext .= 'google.maps.event.addEventListener(directionsDisplayByMarker'. $currentrouter->id.', \'directions_changed\', function() {' ."\n";
						$scripttext .= '  computeTotalDistance(directionsDisplayByMarker'. $currentrouter->id.'.directions);' ."\n";
						$scripttext .= '});' ."\n";
					}
					
					$scripttext .= '  directionsService.route(directionsRequestByMarker'. $currentrouter->id.', function(result, status) {' ."\n";
					$scripttext .= '    if (status == google.maps.DirectionsStatus.OK) {' ."\n";
					$scripttext .= '      directionsDisplayByMarker'. $currentrouter->id.'.setDirections(result);' ."\n";
					$scripttext .= '    }' ."\n";
					$scripttext .= '    else {' ."\n";
					$scripttext .= '		alert("'.JText::_('COM_ZHBAIDUMAP_MAP_DIRECTION_FAILED').' " + status);' ."\n";
					$scripttext .= '    }' ."\n";
					$scripttext .= '});' ."\n";

				}
			}
			// End Route by Marker

			if (isset($currentrouter->showdescription) && (int)$currentrouter->showdescription == 1) 
			{
				if ($currentrouter->description != "")
				{
					$routeHTMLdescription .= '<h2>';
					$routeHTMLdescription .= htmlspecialchars($currentrouter->description, ENT_QUOTES, 'UTF-8');
					$routeHTMLdescription .= '</h2>';
				}
				if ($currentrouter->descriptionhtml != "")
				{
					$routeHTMLdescription .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentrouter->descriptionhtml));
				}
			}
			
		}
		// End for each Route
		
		if ($routepanelcount > 1 || $routepanelcount == 0 || $routepaneltotalcount == 0)
		{
			$scripttext .= 'var toHideRouteDiv = document.getElementById("BDMapsRoutePanel_Total'.$mapDivSuffix.'");' ."\n";
			$scripttext .= 'toHideRouteDiv.style.display = "none";' ."\n";
			//$scripttext .= 'alert("Hide because > 1 or = 0");';
		}

		if ($routeHTMLdescription != "")
		{
			$scripttext .= '  document.getElementById("BDMapsRoutePanel_Description'.$mapDivSuffix.'").innerHTML =  "<p>'. $routeHTMLdescription .'</p>";'."\n";
		}
		
		$scripttext .= 'function computeTotalDistance(result) {' ."\n";
		if ($routepaneltotalcount == 1)
		{
			$scripttext .= '  var total = 0;' ."\n";
			$scripttext .= '  var myroute = result.routes[0];' ."\n";
			$scripttext .= '  for (i = 0; i < myroute.legs.length; i++) {' ."\n";
			$scripttext .= '      total += myroute.legs[i].distance.value;' ."\n";
			$scripttext .= '  }' ."\n";
			$scripttext .= '  total = total / 1000.;' ."\n";
			$scripttext .= '  total = total.toFixed(1);' ."\n";
			
			$scripttext .= '  document.getElementById("BDMapsRoutePanel_Total'.$mapDivSuffix.'").innerHTML = "<p>'.JText::_('COM_ZHBAIDUMAP_MAPROUTER_DETAIL_SHOWPANEL_HDR_TOTAL').' " + total + " '.JText::_('COM_ZHBAIDUMAP_MAPROUTER_DETAIL_SHOWPANEL_HDR_KM').'</p>";' ."\n";
		}
		$scripttext .= '};' ."\n";
		
	}


	// Paths
	if (isset($paths) && !empty($paths)) 
	{
		foreach ($paths as $key => $currentpath) 
		{

		    $scripttext .= 'var contentPathString'. $currentpath->id.' = "";'."\n";
                    if (isset($map->useajax) && (int)$map->useajax != 0)
                    {
                        // do not create content string, create by loop only in the end
                    }
                    else
                    {
                        if ((int)$currentpath->actionbyclick == 1)
                        {
                                // contentPathString - Begin
                                $scripttext .= 'contentPathString'. $currentpath->id.' = '.
                                                        comZhBaiduMapPathsHelper::get_path_content_string(
                                                                $mapDivSuffix,
                                                                $currentpath, 
                                                                $imgpathIcons, $imgpathUtils, $directoryIcons, $main_lang, $placemarkTitleTag);
                                // contentPathString - End
                        }	
                    }

		    if (isset($currentpath->objecttype))
		    {
			    $current_path_path = str_replace(array("\r", "\r\n", "\n"), '', $currentpath->path);
			    
			    if ($current_path_path != "")
			    {



				    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
				    {
					    if (isset($map->useajax) && (int)$map->useajax != 0)
					    {
						    // do not create content string, create by loop only in the end
					    }
					    else
					    {
						    if ((int)$map->hovermarker == 1
						      ||(int)$map->hovermarker == 2)
						    {
							    if ($currentpath->hoverhtml != "")
							    {
								    $scripttext .= 'var hoverStringPath'. $currentpath->id.' = '.
									    comZhBaiduMapPathsHelper::get_path_hover_string(
										    $currentpath);									
							    }
						    }
					    }
				    }						

				    switch ($currentpath->objecttype) 
				    {
					    case 0: // LINE

						    $scripttext .= ' var allCoordinates'. $currentpath->id.' = [ '."\n";
						    $scripttext .=' new BMap.Point('.str_replace(";","), new BMap.Point(", $current_path_path).') '."\n";
						    $scripttext .= ' ]; '."\n";
						    $scripttext .= ' var plPath'. $currentpath->id.' = new BMap.Polyline('."\n";
						    $scripttext .= ' allCoordinates'. $currentpath->id.','."\n";

                                                    /*
						    if (isset($currentpath->geodesic) && (int)$currentpath->geodesic == 1) 
						    {
							    $scripttext .= ' geodesic: true '."\n";
						    }
						    else
						    {
							    $scripttext .= ' geodesic: false '."\n";
						    }
                                                     */
                                                    
						    $scripttext .= '{strokeColor: "'.$currentpath->color.'"'."\n";
						    $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
						    $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
						    $scripttext .= ' });'."\n";

						    // 28.01.2015 - Added GroupManagement
						    if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
						      &&(isset($map->markergroupctlpath) 
						      && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
						    {
							    if ($zhbdmObjectManager != 0)
							    {
								    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'. $currentpath->id.');'."\n";
							    }
						    }
						    else
						    {
							    $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(plPath'. $currentpath->id.');'."\n";
						    }


                                                    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmPathID = '. $currentpath->id.';' ."\n";
                                                    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmObjectType = '. $currentpath->objecttype.';' ."\n";
						    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmInfowinContent = contentPathString'. $currentpath->id.';' ."\n";	
						    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'";' ."\n";	
                                                    
                                                    if ($currentpath->hover_color != "")
                                                    {
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmHoverChangeColor = 1;' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmStrokeColor = "'. $currentpath->color.'";' ."\n";
                                                    }
                                                    else
                                                    {
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmHoverChangeColor = 0;' ."\n";
                                                    }

                                                    // Mouse hover - begin
                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                    {
                                                            if ($currentpath->hoverhtml != "")
                                                            {
                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                {
                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                }
                                                                else
                                                                {
                                                                    if ((int)$map->hovermarker == 1)
                                                                    {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor:("'.$currentpath->hover_color.'");' ."\n";
                                                                            }
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                            //$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");' ."\n";
                                                                            }
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                    }
                                                                    else if ((int)$map->hovermarker == 2)
                                                                    {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");' ."\n";											
                                                                            }
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");' ."\n";
                                                                            }
                                                                                    $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                    }
                                                                }

                                                            }
                                                            else
                                                            {
                                                                    if ($currentpath->hover_color != "")
                                                                    {
                                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                        {
                                                                                $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                        }
                                                                        else
                                                                        {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                        }
                                                                    }													
                                                            }
                                                    }
                                                    else
                                                    {
                                                            if ($currentpath->hover_color != "")
                                                            {
                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                {
                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                }
                                                                else
                                                                {
                                                                    $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");' ."\n";
                                                                    $scripttext .= '  });' ."\n";
                                                                    $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                    $scripttext .= '    plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");' ."\n";
                                                                    $scripttext .= '  });' ."\n";
                                                                }
                                                            }							
                                                    }							
                                                    // Mouse hover - end
                                                    
                                                                    
                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                    {
                                                            // do not create listeners, create by loop only in the end
                                                            $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                    }
                                                    else
                                                    {	                                                   
                                                        // Action By Click Path - Begin							
                                                        switch ((int)$currentpath->actionbyclick)
                                                        {
                                                                // None
                                                                case 0:
                                                                break;
                                                                // Info
                                                                case 1:
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
                                                                                // Close the other infobubbles
                                                                                $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                $scripttext .= '  }' ."\n";
                                                                                // Hide hover window when feature enabled
                                                                                if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                                                {
                                                                                        if ((int)$map->hovermarker == 1)
                                                                                        {
                                                                                                $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                        }
                                                                                        else if ((int)$map->hovermarker == 2)
                                                                                        {
                                                                                                $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                        }
                                                                                }
                                                                                // Open infowin
                                                                                if ((int)$map->markerlistpos != 0)
                                                                                {
                                                                                        $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                }

                                                                                if ($managePanelInfowin == 1)
                                                                                {
                                                                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.zhbdmInfowinContent);' ."\n";
                                                                                }	
                                                                                else
                                                                                {
                                                                                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
                                                                                        //$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                                        $scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', event.point);' ."\n";
                                                                                }
                                                                                        $scripttext .= '  });' ."\n";
                                                                break;
                                                                // Link
                                                                case 2:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                $scripttext .= '  });' ."\n";											
                                                                        }
                                                                break;
                                                                // Link in self
                                                                case 3:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                break;
                                                                default:
                                                                        $scripttext .= '' ."\n";
                                                                break;
                                                        }
                                                        // Action By Click Path - End
                                                    }
					    break;
					    case 1: //POLYGON
						    $scripttext .= ' var allCoordinates'. $currentpath->id.' = [ '."\n";
						    $scripttext .=' new BMap.Point('.str_replace(";","), new BMap.Point(", $current_path_path).') '."\n";
						    $scripttext .= ' ]; '."\n";
						    $scripttext .= ' var plPath'. $currentpath->id.' = new BMap.Polygon('."\n";
						    $scripttext .= ' allCoordinates'. $currentpath->id.','."\n";
                                                    /*
						    if (isset($currentpath->geodesic) && (int)$currentpath->geodesic == 1) 
						    {
							    $scripttext .= ' geodesic: true, '."\n";
						    }
						    else
						    {
							    $scripttext .= ' geodesic: false, '."\n";
						    }                                                   
                                                    */
						    $scripttext .= '{strokeColor: "'.$currentpath->color.'"'."\n";
						    $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
						    $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
						    if ($currentpath->fillcolor != "")
						    {
							    $scripttext .= ',fillColor: "'.$currentpath->fillcolor.'"'."\n";
						    }
						    if ($currentpath->fillopacity != "")
						    {
							    $scripttext .= ',fillOpacity: '.$currentpath->fillopacity."\n";
						    }
						    $scripttext .= ' });'."\n";

						    // 28.01.2015 - Added GroupManagement
						    if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
						      &&(isset($map->markergroupctlpath) 
						      && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
						    {
							    if ($zhbdmObjectManager != 0)
							    {
								    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'. $currentpath->id.');'."\n";
							    }
						    }
						    else
						    {
							    $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(plPath'. $currentpath->id.');'."\n";
						    }


                                                    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmPathID = '. $currentpath->id.';' ."\n";
                                                    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmObjectType = '. $currentpath->objecttype.';' ."\n";
						    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmInfowinContent = contentPathString'. $currentpath->id.';' ."\n";	
						    $scripttext .= '  plPath'. $currentpath->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'";' ."\n";	

                                                    if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                    {
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmHoverChangeColor = 1;' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmStrokeColor = "'. $currentpath->color.'";' ."\n";
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmFillColor = "'. $currentpath->fillcolor.'";' ."\n";
                                                    }
                                                    else
                                                    {
                                                        $scripttext .= '  plPath'. $currentpath->id.'.zhbdmHoverChangeColor = 0;' ."\n";
                                                    }												
                                                    
                                                    // Mouse hover - begin
                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                    {
                                                            if ($currentpath->hoverhtml != "")
                                                            {
                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                {
                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                }
                                                                else
                                                                {                                                                
                                                                    if ((int)$map->hovermarker == 1)
                                                                    {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                            {
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                    }											
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                    }	
                                                                            }
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                            //$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                            {
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                        $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                    }								
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";		
                                                                                    }
                                                                            }
                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                    }
                                                                    else if ((int)$map->hovermarker == 2)
                                                                    {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                            {
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                    }											
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                    }	
                                                                            }
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'. $currentpath->id.');' ."\n";
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                            $scripttext .= '  });' ."\n";

                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                            {
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                        $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                    }								
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";		
                                                                                    }
                                                                            }
                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                            $scripttext .= '  });' ."\n";
                                                                    }
                                                                }    

                                                            }
                                                            else
                                                            {
                                                                    if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                    {
                                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                        {
                                                                                $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                        }
                                                                        else
                                                                        {
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                    $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                            }											
                                                                            if ($currentpath->hover_fillcolor != "")
                                                                            {
                                                                                    $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                            }	
                                                                            $scripttext .= '  });' ."\n";
                                                                            $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                            if ($currentpath->hover_color != "")
                                                                            {
                                                                                 $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                            }								
                                                                             if ($currentpath->hover_fillcolor != "")
                                                                            {
                                                                                     $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";		
                                                                            }
                                                                            $scripttext .= '  });' ."\n";
                                                                        }
                                                                    }													
                                                            }
                                                    }
                                                    else
                                                    {
                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                            {
                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                {
                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                                }
                                                                else
                                                                {
                                                                    $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                    if ($currentpath->hover_color != "")
                                                                    {
                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                    }											
                                                                    if ($currentpath->hover_fillcolor != "")
                                                                    {
                                                                            $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                    }	                                                                       
                                                                    $scripttext .= '  });' ."\n";
                                                                    $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                    if ($currentpath->hover_color != "")
                                                                    {
                                                                         $scripttext .= '     plPath'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                    }								
                                                                     if ($currentpath->hover_fillcolor != "")
                                                                    {
                                                                             $scripttext .= '     plPath'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";		
                                                                    }
                                                                    $scripttext .= '  });' ."\n";
                                                                }
                                                            }							
                                                    }							
                                                    // Mouse hover - end

                                                                    
                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                    {
                                                            // do not create listeners, create by loop only in the end
                                                            $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $currentpath->id.');'."\n";
                                                    }
                                                    else
                                                    {
                                                        // Action By Click Path - Begin							
                                                        switch ((int)$currentpath->actionbyclick)
                                                        {
                                                                // None
                                                                case 0:
                                                                break;
                                                                // Info
                                                                case 1:
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
                                                                                // Close the other infobubbles
                                                                                $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                $scripttext .= '  }' ."\n";
                                                                                // Hide hover window when feature enabled
                                                                                if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                                                {
                                                                                        if ((int)$map->hovermarker == 1)
                                                                                        {
                                                                                                $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                        }
                                                                                        else if ((int)$map->hovermarker == 2)
                                                                                        {
                                                                                                $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                        }
                                                                                }
                                                                                // Open infowin
                                                                                if ((int)$map->markerlistpos != 0)
                                                                                {
                                                                                        $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                }

                                                                                if ($managePanelInfowin == 1)
                                                                                {
                                                                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.zhbdmInfowinContent);' ."\n";
                                                                                }	
                                                                                else
                                                                                {											
                                                                                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
                                                                                        //$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                                        $scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', event.point);' ."\n";
                                                                                }
                                                                                $scripttext .= '  });' ."\n";
                                                                break;
                                                                // Link
                                                                case 2:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                $scripttext .= '  });' ."\n";											
                                                                        }
                                                                break;
                                                                // Link in self
                                                                case 3:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                break;
                                                                default:
                                                                        $scripttext .= '' ."\n";
                                                                break;
                                                        }
                                                        // Action By Click Path - End
                                                    
                                                    }
					    break;
					    case 2: //CIRCLE
						if ($currentpath->radius != "")
						    {
							    $arrayPathCoords = explode(';', $current_path_path);
							    $arrayPathIndex = 0;
							    foreach ($arrayPathCoords as $currentpathcoordinates) 
							    {
								    $arrayPathIndex += 1;

								    $scripttext .= ' var plPath'.$arrayPathIndex.'_'. $currentpath->id.' = new BMap.Circle('."\n";
								    $scripttext .= ' new BMap.Point('.$currentpathcoordinates.')'."\n";
								    $scripttext .= ', '.$currentpath->radius.','."\n";
								    $scripttext .= '{strokeColor: "'.$currentpath->color.'"'."\n";
								    $scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
								    $scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
								    if ($currentpath->fillcolor != "")
								    {
									    $scripttext .= ',fillColor: "'.$currentpath->fillcolor.'"'."\n";
								    }
								    if ($currentpath->fillopacity != "")
								    {
									    $scripttext .= ',fillOpacity: '.$currentpath->fillopacity."\n";
								    }
								    $scripttext .= '  });' ."\n";


								    // 28.01.2015 - Added GroupManagement
								    if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
								      &&(isset($map->markergroupctlpath) 
								      && (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3))))
								    {
									    if ($zhbdmObjectManager != 0)
									    {
										    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathXAdd('.$currentpath->markergroup.', plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
									    }
								    }
								    else
								    {
									    $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
								    }


                                                                    $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmPathID = '. $currentpath->id.';' ."\n";
                                                                    $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmObjectType = '. $currentpath->objecttype.';' ."\n";
								    $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmInfowinContent = contentPathString'. $currentpath->id.';' ."\n";	
								    $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'";' ."\n";	

                                                                    if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                    {
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmHoverChangeColor = 1;' ."\n";
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmStrokeColor = "'. $currentpath->color.'";' ."\n";
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmFillColor = "'. $currentpath->fillcolor.'";' ."\n";
                                                                    }
                                                                    else
                                                                    {
                                                                        $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.zhbdmHoverChangeColor = 0;' ."\n";
                                                                    }	

                                                                    // Mouse hover - begin
                                                                    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                                    {
                                                                            if ($currentpath->hoverhtml != "")
                                                                            {
                                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                {
                                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                }
                                                                                else
                                                                                {                                                                                
                                                                                    if ((int)$map->hovermarker == 1)
                                                                                    {
                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                                    
                                                                                                    if ($currentpath->hover_color != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                                    }											
                                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                                    }	

                                                                                            }
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setContent(hoverStringPath'.$currentpath->id.');' ."\n";
                                                                                            //$scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                                    if ($currentpath->hover_color != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                                    }								
                                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->fillcolor.'");'."\n";
                                                                                                    }
                                                                                            }
                                                                                            $scripttext .= '  hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                    else if ((int)$map->hovermarker == 2)
                                                                                    {
                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                                    
                                                                                                    if ($currentpath->hover_color != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                                    }											
                                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                                    }	

                                                                                            }
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setContent(hoverStringPath'.$currentpath->id.');' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                                            $scripttext .= '  var anchor = new Hover_Anchor("path", this, event);' ."\n";
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.open(map'.$mapDivSuffix.', anchor);' ."\n";
                                                                                            $scripttext .= '  });' ."\n";

                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                                    if ($currentpath->hover_color != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                                    }								
                                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                                    {
                                                                                                        $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->fillcolor.'");'."\n";
                                                                                                    }
                                                                                            }
                                                                                            $scripttext .= '  hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                            $scripttext .= '  });' ."\n";
                                                                                    }
                                                                                }

                                                                            }
                                                                            else
                                                                            {
                                                                                    if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                        if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                        {
                                                                                                $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "")
                                                                                            {
                                                                                               $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                            }											
                                                                                            if ($currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                               $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                            }	                                                                                           
                                                                                         
                                                                                            $scripttext .= '  });' ."\n";
                                                                                            $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                                            if ($currentpath->hover_color != "")
                                                                                            {
                                                                                               $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                            }											
                                                                                            if ($currentpath->hover_fillcolor != "")
                                                                                            {
                                                                                               $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";
                                                                                            }                                                                                            

                                                                                            $scripttext .= '  });' ."\n";
                                                                                        }
                                                                                    }													
                                                                            }
                                                                    }
                                                                    else
                                                                    {
                                                                            if ($currentpath->hover_color != "" || $currentpath->hover_fillcolor != "")
                                                                            {
                                                                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                                {
                                                                                        $scripttext .= '  ajaxpathshover'.$mapDivSuffix.'.push(plPath'.$arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                                }
                                                                                else
                                                                                {
                                                                                    $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseover\', function(event) {' ."\n";
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                       $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->hover_color.'");'."\n";
                                                                                    }											
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                       $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->hover_fillcolor.'");'."\n";
                                                                                    }
                                                                                    $scripttext .= '  });' ."\n";
                                                                                    $scripttext .= '  plPath'. $arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'mouseout\', function(event) {' ."\n";
                                                                                    if ($currentpath->hover_color != "")
                                                                                    {
                                                                                       $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setStrokeColor("'.$currentpath->color.'");'."\n";
                                                                                    }											
                                                                                    if ($currentpath->hover_fillcolor != "")
                                                                                    {
                                                                                       $scripttext .= '    plPath'. $arrayPathIndex.'_'. $currentpath->id.'.setFillColor("'.$currentpath->fillcolor.'");'."\n";
                                                                                    }   
                                                                                    $scripttext .= '  });' ."\n";
                                                                                }
                                                                            }							
                                                                    }																
                                                                    // Mouse hover - end

                                                                    
                                                                    if (isset($map->useajax) && (int)$map->useajax != 0)
                                                                    {
                                                                            // do not create listeners, create by loop only in the end
                                                                            $scripttext .= '  ajaxpaths'.$mapDivSuffix.'.push(plPath'. $arrayPathIndex.'_'. $currentpath->id.');'."\n";
                                                                    }
                                                                    else
                                                                    { 
                                                                        // Action By Click Path - Begin							
                                                                        switch ((int)$currentpath->actionbyclick)
                                                                        {
                                                                                // None
                                                                                case 0:
                                                                                break;
                                                                                // Info
                                                                                case 1:
                                                                                                $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                                $scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
                                                                                                // Close the other infobubbles
                                                                                                $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
                                                                                                $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
                                                                                                $scripttext .= '  }' ."\n";
                                                                                                // Hide hover window when feature enabled
                                                                                                if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
                                                                                                {
                                                                                                        if ((int)$map->hovermarker == 1)
                                                                                                        {
                                                                                                                $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
                                                                                                        }
                                                                                                        else if ((int)$map->hovermarker == 2)
                                                                                                        {
                                                                                                                $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
                                                                                                        }
                                                                                                }
                                                                                                // Open infowin
                                                                                                if ((int)$map->markerlistpos != 0)
                                                                                                {
                                                                                                        $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
                                                                                                }

                                                                                                if ($managePanelInfowin == 1)
                                                                                                {
                                                                                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.zhbdmInfowinContent);' ."\n";
                                                                                                }	
                                                                                                else
                                                                                                {
                                                                                                        $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
                                                                                                        //$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
                                                                                                        $scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', event.point);' ."\n";	
                                                                                                }											

                                                                                                $scripttext .= '  });' ."\n";
                                                                                break;
                                                                // Link
                                                                case 2:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
                                                                                $scripttext .= '  });' ."\n";											
                                                                        }
                                                                break;
                                                                // Link in self
                                                                case 3:
                                                                        if ($currentpath->hrefsite != "")
                                                                        {
                                                                                $scripttext .= '  plPath'.$arrayPathIndex.'_'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
                                                                                $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
                                                                                $scripttext .= '  });' ."\n";
                                                                        }
                                                                break;
                                                                                default:
                                                                                        $scripttext .= '' ."\n";
                                                                                break;
                                                                        }
                                                                        // Action By Click Path - End

                                                                    }

							    }
						    }
					    break;
				    }

				    if ($featurePathElevation == 1)
				    {
					    if ((int)$currentpath->elevation != 0
					     && (int)$currentpath->objecttype == 0
					     )
					    {
						    if ($currentpath->elevationicontype == "")
						    {
							    $elevationMouseOverIcon = 'gm#simple-lightblue';
						    }
						    else
						    {
							    $elevationMouseOverIcon = $currentpath->elevationicontype;
						    }
						    $elevationMouseOverIcon = str_replace("#", "%23", $elevationMouseOverIcon).'.png';

						    $scripttext .= 'elevationPlotDiagram'.$mapDivSuffix.'(allCoordinates'. $currentpath->id.', '.
															    (int)$currentpath->elevationcount.', '.
															    (int)$currentpath->elevationwidth.', '.
															    (int)$currentpath->elevationheight.', '.
															    '"'.$elevationMouseOverIcon.'", '.
															    (int)$currentpath->elevation.','.
															    (int)$currentpath->elevationbaseline.','.
															    '"'.$currentpath->v_baseline_color.'", '.
															    '"'.$currentpath->v_gridline_color.'", '.
															    (int)(int)$currentpath->v_gridline_count.', '.
															    '"'.$currentpath->v_minor_gridline_color.'", '.
															    (int)$currentpath->v_minor_gridline_count.', '.
															    '"'.$currentpath->background_color_stroke.'", '.
															    (int)$currentpath->background_color_width.', '.
															    '"'.$currentpath->background_color_fill.'", '.
															    '"'.$currentpath->v_max_value.'", '.
															    '"'.$currentpath->v_min_value.'"'.
															    ');' ."\n";
					    }
				    }


			    }
		    }

		    if ($currentpath->kmllayer != "") 
		    {
				$scripttext .= 'var kmlOptions'. $currentpath->id.' = {' ."\n";
				if (isset($currentpath->showtype))
				{
					switch ($currentpath->showtype) 
					{
					case 0:
						$scripttext .= 'preserveViewport:false' ."\n";
					break;
					case 1:
						$scripttext .= 'preserveViewport:true' ."\n";
					break;
					default:
						$scripttext .= 'preserveViewport:false' ."\n";
					break;
					}
				}
				else
				{
					$scripttext .= 'preserveViewport:false' ."\n";
				}
				if (isset($currentpath->suppressinfowindows))
				{
					if ((int)$currentpath->suppressinfowindows == 1)
					{
						$scripttext .= ', suppressInfoWindows:true' ."\n";
					}
					else
					{
						$scripttext .= ', suppressInfoWindows:false' ."\n";
					}
				}
				$scripttext .= '};' ."\n";
			
				$scripttext .= 'var kmlLayer'. $currentpath->id.' = new google.maps.KmlLayer(\''.$currentpath->kmllayer.'\', kmlOptions'. $currentpath->id.');' ."\n";
			    
				if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0)
				  &&(isset($map->markergroupctlpath) 
			      && (((int)$map->markergroupctlpath == 1) || ((int)$map->markergroupctlpath == 3))))
				{
					if ($zhbdmObjectManager != 0)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAdd('.$currentpath->markergroup.', kmlLayer'. $currentpath->id.');'."\n";
					}
				}
				else
				{
					$scripttext .= 'map'.$mapDivSuffix.'.addOverlay(kmlLayer'. $currentpath->id.');' ."\n";
				}
				
											
				
				
				if ($featurePathElevationKML == 1)
				{				
					if ((int)$currentpath->elevation != 0)
					{
							if ($currentpath->elevationicontype == "")
							{
								$elevationMouseOverIcon = 'gm#simple-lightblue';
							}
							else
							{
								$elevationMouseOverIcon = $currentpath->elevationicontype;
							}
							$elevationMouseOverIcon = str_replace("#", "%23", $elevationMouseOverIcon).'.png';
							
							//$scripttext .= ' alert("step1");' ."\n";

							$scripttext .= '	var myParser = new geoXML3.parser({afterParse: useTheData});' ."\n";

							//$scripttext .= ' alert("step2");' ."\n";

							$scripttext .= '	myParser.parse(\''.$currentpath->kmllayer.'\');' ."\n";

							//$scripttext .= ' alert("step3");' ."\n";

							$scripttext .= '	function useTheData(doc) {' ."\n";
							// Geodata handling goes here, using JSON properties of the doc object
							$scripttext .= '		var SAMPLES = '.(int)$currentpath->elevationcountkml.';' ."\n";
							$scripttext .= '		var pathx;' ."\n";

							//$scripttext .= ' alert("doc = "+doc.length);' ."\n";

							$scripttext .= '		var geoXmlDoc = doc[0];' ."\n";
							//$scripttext .= ' alert("count = "+geoXmlDoc.placemarks.length);' ."\n";
							$scripttext .= '		  for (var i = 0; i < geoXmlDoc.placemarks.length; i++) {' ."\n";
							$scripttext .= '			var placemark = geoXmlDoc.placemarks[i];' ."\n";
							$scripttext .= '			if (placemark.polyline) {' ."\n";
							$scripttext .= '			  if (!pathx) {' ."\n";
							$scripttext .= '				pathx = [];' ."\n";
							$scripttext .= '				var samples = placemark.polyline.getPath().getLength();' ."\n";
							$scripttext .= '				var incr = 1;' ."\n";
							$scripttext .= '				if (SAMPLES != 0) ' ."\n";
							$scripttext .= '				{' ."\n";
							$scripttext .= '					incr = samples/SAMPLES;' ."\n";
							$scripttext .= '					if (incr < 1) incr = 1;' ."\n";
							$scripttext .= '				}' ."\n";
							$scripttext .= '				for (var i=0;i<samples; i+=incr)' ."\n";
							$scripttext .= '				{' ."\n";
							$scripttext .= '				  pathx.push(placemark.polyline.getPath().getAt(parseInt(i)));' ."\n";
							$scripttext .= '				}' ."\n";
							$scripttext .= '			  }' ."\n";					 
							$scripttext .= '			}' ."\n";
							$scripttext .= '		  }' ."\n";
							$scripttext .= '		if (pathx) {' ."\n";
							$scripttext .= '    	elevationPlotDiagram'.$mapDivSuffix.'(pathx, '.
																(int)$currentpath->elevationcount.', '.
																(int)$currentpath->elevationwidth.', '.
																(int)$currentpath->elevationheight.', '.
																'"'.$elevationMouseOverIcon.'", '.
																(int)$currentpath->elevation.','.
																(int)$currentpath->elevationbaseline.','.
																'"'.$currentpath->v_baseline_color.'", '.
																'"'.$currentpath->v_gridline_color.'", '.
																(int)$currentpath->v_gridline_count.', '.
																'"'.$currentpath->v_minor_gridline_color.'", '.
																(int)$currentpath->v_minor_gridline_count.', '.
																'"'.$currentpath->background_color_stroke.'", '.
																(int)$currentpath->background_color_width.', '.
																'"'.$currentpath->background_color_fill.'", '.
																'"'.$currentpath->v_max_value.'", '.
																'"'.$currentpath->v_min_value.'"'.
																');' ."\n";
							$scripttext .= '		}' ."\n";
							$scripttext .= '	};' ."\n";
					}
				}
				
		    }
			
		    if ($currentpath->imgurl != ""
			&& $currentpath->imgbounds != "") 
		    {
	

			$imgGroundBoundsArray = explode(";", str_replace(',',';',$currentpath->imgbounds));
			if (count($imgGroundBoundsArray) != 4)
			{
			    $scripttext .= 'alert("'.JText::_('COM_ZHBAIDUMAP_MAP_ERROR_IMGGROUNDBOUNDS').'");'."\n";
			}
			else
			{
			    $scripttext .= 'var imgGroundBounds'. $currentpath->id.' = new BMap.Bounds(' ."\n";
			    $scripttext .= '  	new BMap.Point('.$imgGroundBoundsArray[0].', '.$imgGroundBoundsArray[1].'),' ."\n";
			    $scripttext .= '  	new BMap.Point('.$imgGroundBoundsArray[2].', '.$imgGroundBoundsArray[3].'));' ."\n";


			    $scripttext .= 'var imgGroundOptions'. $currentpath->id.' = {' ."\n";
			    if (isset($currentpath->imgopacity))
			    {
				if ($currentpath->imgopacity != "")
				{
				    $scripttext .= '  opacity:'.$currentpath->imgopacity ."\n";
				}
				else
				{
				    $scripttext .= '  opacity: 1'."\n";
				}	
			    }
			    else
			    {
				$scripttext .= '  opacity: 1'."\n";
			    }

                            // doesn't work
                            //$scripttext .= ', imageURL: \''.$currentpath->imgurl.'\'';
                            
                            if (isset($currentpath->minzoom) && (int)$currentpath->minzoom != 0)
                            {
                                    $scripttext .= ', displayOnMinLevel: '.(int)$currentpath->minzoom ."\n";
                            }
                            if (isset($currentpath->maxzoom) && (int)$currentpath->maxzoom != 0)
                            {
                                    $scripttext .= ', displayOnMaxLevel: '.(int)$currentpath->maxzoom."\n";
                            }                            
                            /*
			    if (isset($currentpath->imgclickable))
			    {
				    if ((int)$currentpath->imgclickable == 1)
				    {
					    $scripttext .= ', clickable:true' ."\n";
				    }
				    else
				    {
					    $scripttext .= ', clickable:false' ."\n";
				    }
			    }                           
                            */
			    $scripttext .= '};' ."\n";

			    $scripttext .= 'var imgGroundLayer'. $currentpath->id.' = new BMap.GroundOverlay(imgGroundBounds'. $currentpath->id.', imgGroundOptions'. $currentpath->id.');' ."\n";

                            $scripttext .= 'imgGroundLayer'. $currentpath->id.'.setImageURL(\''.$currentpath->imgurl.'\');' ."\n";
                             
			    $scripttext .= '  imgGroundLayer'. $currentpath->id.'.zhbdmPathID = '. $currentpath->id.';' ."\n";
                            $scripttext .= '  imgGroundLayer'. $currentpath->id.'.zhbdmInfowinContent = contentPathString'. $currentpath->id.';' ."\n";	
			    $scripttext .= '  imgGroundLayer'. $currentpath->id.'.zhbdmTitle = "'.str_replace('\\', '/', str_replace('"', '\'\'', $currentpath->title)).'";' ."\n";	


			    $scripttext .= 'map'.$mapDivSuffix.'.addOverlay(imgGroundLayer'. $currentpath->id.');' ."\n";
                            
                            if ($needOverlayControl != 0)
                            {
                                if ((int)$currentpath->imgopacitymanage == 1)
                                {
                                    $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.addGroundOverlay(imgGroundLayer'.$currentpath->id.');'."\n";
                                }
                            }

			    if (isset($currentpath->imgclickable))
			    {
                                if (isset($map->useajax) && (int)$map->useajax != 0)
                                {
                                        // do not create listeners, create by loop only in the end
                                        $scripttext .= '  ajaxpathsOVL'.$mapDivSuffix.'.push(imgGroundLayer'. $currentpath->id.');'."\n";
                                }
                                else
                                {
				    if ((int)$currentpath->imgclickable == 1)
				    {				    
					    // Action By Click Path - Begin							
					    switch ((int)$currentpath->actionbyclick)
					    {
						    // None
						    case 0:
						    break;
						    // Info
						    case 1:
								    $scripttext .= '  imgGroundLayer'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
								    $scripttext .= '  map'.$mapDivSuffix.'.closeInfoWindow();' ."\n";
								    // Close the other infobubbles
								    $scripttext .= '  for (i = 0; i < infobubblemarkers'.$mapDivSuffix.'.length; i++) {' ."\n";
								    $scripttext .= '      infobubblemarkers'.$mapDivSuffix.'[i].close();' ."\n";
								    $scripttext .= '  }' ."\n";
								    // Hide hover window when feature enabled
								    if (isset($map->hovermarker) && ((int)$map->hovermarker !=0))	
								    {
									    if ((int)$map->hovermarker == 1)
									    {
										    $scripttext .= 'hoverinfowindow'.$mapDivSuffix.'.close();' ."\n";
									    }
									    else if ((int)$map->hovermarker == 2)
									    {
										    $scripttext .= 'hoverinfobubble'.$mapDivSuffix.'.close();' ."\n";
									    }
								    }
								    // Open infowin
								    if ((int)$map->markerlistpos != 0)
								    {
									    $scripttext .= '  Map_Animate_Marker_Hide_Force(map'.$mapDivSuffix.');'."\n";
								    }

								    if ($managePanelInfowin == 1)
								    {
									    $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelShowPathContent(this.zhbdmInfowinContent);' ."\n";
								    }	
								    else
								    {											
									    $scripttext .= '  infowindow'.$mapDivSuffix.'.setContent(this.zhbdmInfowinContent);' ."\n";
									    //$scripttext .= '  infowindow'.$mapDivSuffix.'.setPosition(event.point);' ."\n";
									    $scripttext .= '  map'.$mapDivSuffix.'.openInfoWindow(infowindow'.$mapDivSuffix.', event.point);' ."\n";
								    }
								    $scripttext .= '  });' ."\n";
						    break;
						    // Link
						    case 2:
							    if ($currentpath->hrefsite != "")
							    {
								    $scripttext .= '  imgGroundLayer'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
								    $scripttext .= '  window.open("'.$currentpath->hrefsite.'");' ."\n";
								    $scripttext .= '  });' ."\n";											
							    }
						    break;
						    // Link in self
						    case 3:
							    if ($currentpath->hrefsite != "")
							    {
								    $scripttext .= '  imgGroundLayer'. $currentpath->id.'.addEventListener( \'click\', function(event) {' ."\n";
								    $scripttext .= '  window.location = "'.$currentpath->hrefsite.'";' ."\n";
								    $scripttext .= '  });' ."\n";
							    }
						    break;
						    default:
							    $scripttext .= '' ."\n";
						    break;
					    }
					    // Action By Click Path - End	
				    }
			    
                                }
                            }
			}
	


		    }
			

		    
		}
                
 

	}
        
        if (isset($map->useajax) && (int)$map->useajax != 0) 
	{

            $scripttext .= 'for (var i=0; i<ajaxpaths'.$mapDivSuffix.'.length; i++)' ."\n";
            $scripttext .= '{' ."\n";
                    if ((int)$map->useajax == 1)
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddListeners("mootools", ajaxpaths'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    else
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddListeners("jquery", ajaxpaths'.$mapDivSuffix.'[i]);' ."\n";
                    }
            $scripttext .= '}' ."\n";
            
            // For Hovering Feature - Begin
            $scripttext .= 'for (var i=0; i<ajaxpathshover'.$mapDivSuffix.'.length; i++)' ."\n";
            $scripttext .= '{' ."\n";
            //$scripttext .= '    alert("Call:"+ajaxmarkersLL'.$mapDivSuffix.'[i].zhbdmPlacemarkID);' ."\n";
                if ((int)$map->useajax == 1)
                {
                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddHoverListeners("mootools", ajaxpathshover'.$mapDivSuffix.'[i]);' ."\n";
                }
                else
                {
                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddHoverListeners("jquery", ajaxpathshover'.$mapDivSuffix.'[i]);' ."\n";
                }
            $scripttext .= '}' ."\n";
               
            // For Hovering Feature - End   
            
            $scripttext .= 'for (var i=0; i<ajaxpathsOVL'.$mapDivSuffix.'.length; i++)' ."\n";
            $scripttext .= '{' ."\n";
                    if ((int)$map->useajax == 1)
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddListeners("mootools", ajaxpathsOVL'.$mapDivSuffix.'[i]);' ."\n";
                    }
                    else
                    {
                            $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PathAddListeners("jquery", ajaxpathsOVL'.$mapDivSuffix.'[i]);' ."\n";
                    }
            $scripttext .= '}' ."\n";            
        }
        

        if ($needOverlayControl != 0)
        {

            $scripttext .= 'map'.$mapDivSuffix.'.addControl(new zhbdmOverlayOpacityControl('.
                    '"'.$mapDivSuffix.'",'. 
                    'map'.$mapDivSuffix.','. 
                    'zhbdmObjMgr'.$mapDivSuffix.','.
                    $feature4control.','. 
                    (int)$map->overlayopacitycontrol.','. 
                    (int)$map->overlayopacitycontrolpos.','. 
                    (int)$map->overlayopacitycontrolofsx.','. 
                    (int)$map->overlayopacitycontrolofsy.','. 
                    '"opacityoverlay",'. 
                    '"'.JText::_('COM_ZHBAIDUMAP_MAP_OPACITY_OVERLAY_CONTROL').'"'.
                    '));'."\n";                  

		
        }        


	
	// Map center - begin
	if ((int)$map->mapcentercontrol != 0) 
	{

			$scripttext .= 'map'.$mapDivSuffix.'.addControl(new zhbdmMapCenterButtonControl('.
				'latlng'.$mapDivSuffix.','.
				'"'.$ctrl_zoom.'",'.
				'map'.$mapDivSuffix.','. 
				$feature4control.','. 
				(int)$map->mapcentercontrol.','. 
				(int)$map->mapcentercontrolpos.','. 
                                (int)$map->mapcentercontrolofsx.','. 
                                (int)$map->mapcentercontrolofsy.','.                                 
				'"mapcenter",'. 
				'"'.JText::_('COM_ZHBAIDUMAP_MAP_HOMECONTROL_LABEL').'",'.
				'19,'. 
				'16,'. 
				'"'.$imgpathUtils.'home.png"'.
				'));'."\n";				
									
	}
	// Map center - end	
        
        
        if ($credits != '')
	{
		$scripttext .= '  document.getElementById("BDMapsCredit'.$mapDivSuffix.'").innerHTML = \''.$credits.'\';'."\n";
	}

	if ((isset($map->autoposition) && (int)$map->autoposition == 1))
	{
			$scripttext .= 'findMyPosition'.$mapDivSuffix.'("Map");' ."\n";
	}
        
        // idle - map loaded
        // Do open list if preset to yes
	if (isset($map->markerlistpos) && (int)$map->markerlistpos != 0) 
	{
		if ((int)$map->markerlistpos == 111
		  ||(int)$map->markerlistpos == 112
		  ||(int)$map->markerlistpos == 121
		  ) 
		{
			// We don't have to do in any case when table or external
			// because it displayed		
		}
		else
		{
			if ((int)$map->markerlistbuttontype == 0
			||(int)$map->markerlistpos == 120 // panel
			)				
			{
				// Open because for non-button
				$scripttext .= '	var toShowDiv = document.getElementById("BDMapsMarkerList'.$mapDivSuffix.'");' ."\n";
				$scripttext .= '	toShowDiv.style.display = "block";' ."\n";
			}
			else
			{
				switch ($map->markerlistbuttontype) 
				{
					case 0:
						$scripttext .= '	var toShowDiv = document.getElementById("BDMapsMarkerList'.$mapDivSuffix.'");' ."\n";
						$scripttext .= '	toShowDiv.style.display = "block";' ."\n";
					break;
					case 1:
						$scripttext .= '';
					break;
					case 2:
						$scripttext .= '';
					break;
					case 11:
						$scripttext .= '	var toShowDiv = document.getElementById("BDMapsMarkerList'.$mapDivSuffix.'");' ."\n";
						$scripttext .= '	toShowDiv.style.display = "block";' ."\n";
					break;
					case 12:
						$scripttext .= '	var toShowDiv = document.getElementById("BDMapsMarkerList'.$mapDivSuffix.'");' ."\n";
						$scripttext .= '	toShowDiv.style.display = "block";' ."\n";
					break;
					default:
						$scripttext .= '';
					break;
				}
			}
								
		}	
	}
	// Open Placemark List Presetspaths)
        
	$scripttext .= 'var toShowLoading = document.getElementById("BDMapsLoading'.$mapDivSuffix.'");'."\n";
	$scripttext .= '  toShowLoading.style.display = \'none\';'."\n";
	
 	if ($zhbdmObjectManager != 0)
	{
		if ((isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0))
		{
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enableObjectGroupManagement();' ."\n";
			
			if ((isset($map->markergrouptype) && (int)$map->markergrouptype == 1))
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setObjectGroupManagementType("OnlyOneActive");' ."\n";
			}
			

			if ((isset($map->markergroupctlmarker) && (int)$map->markergroupctlmarker != 0))
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePlacemarkGroupManagement();' ."\n";
			}
			if (isset($map->markergroupctlpath) 
			&& (((int)$map->markergroupctlpath == 1) || ((int)$map->markergroupctlpath == 3)))
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePathGroupManagement();' ."\n";
			}
			
			if (isset($map->markergroupctlpath) 
			&& (((int)$map->markergroupctlpath == 2) || ((int)$map->markergroupctlpath == 3)))
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePathXGroupManagement();' ."\n";
			}

		}

		
		if ((isset($map->markercluster) && (int)$map->markercluster == 1))
		{
			$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePlacemarkClusterization();' ."\n";
			if ((isset($map->markerclustergroup) && (int)$map->markerclustergroup == 1))
			{
				$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.enablePlacemarkClusterizationByGroup();' ."\n";
			}
		}

		$scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.InitializeByGroupState();'."\n";
		
	}
	       
        
// end initialize
$scripttext .= '};' ."\n";

	if ($zhbdmObjectManager != 0)
	{
		if ($ajaxLoadObjects != 0)
		{

			if ($ajaxLoadObjectType == 2)
			{
				$scripttext .= 'function LoadMapObjects'.$mapDivSuffix.' (event) {' ."\n";
                                //$scripttext .= 'alert("Load ...");' ."\n";
                                
                                $scripttext .= 'zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
				                                
				if ($ajaxLoadObjects == 1)
				{
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
				}
				else if ($ajaxLoadObjects == 2)
				{
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
				}
                                
				$scripttext .= '};' ."\n";	
				
				$scripttext .= ' function LoadMapObjectsOnce'.$mapDivSuffix.' (event) {' ."\n";
                                //$scripttext .= 'alert("Load once...");' ."\n";
                                if ($ajaxLoadObjects == 1)
                                {
                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPathAJAX("mootools");' ."\n";
                                }
                                else if ($ajaxLoadObjects == 2)
                                {
                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPathAJAX("jquery");' ."\n";
                                }
                                
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'load\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'tilesloaded\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'zoomend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'moveend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";                                
				$scripttext .= '};' ."\n";	
				
			}
			else
			{
				$scripttext .= ' function LoadMapObjectsOnce'.$mapDivSuffix.' (event) {' ."\n";
                                //$scripttext .= 'alert("Load once v2...");' ."\n";
                                
				if ($ajaxLoadObjectType == 1)
				{
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType(2);' ."\n";
					if ($ajaxLoadObjects == 1)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
					}
					else if ($ajaxLoadObjects == 2)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
					}
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType(0);' ."\n";
					if ($ajaxLoadObjects == 1)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
					}
					else if ($ajaxLoadObjects == 2)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
					}
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
				}
				else
				{
					$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.setPlacemarkLoadType('.$ajaxLoadObjectType.');' ."\n";
					if ($ajaxLoadObjects == 1)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("mootools");' ."\n";
					}
					else if ($ajaxLoadObjects == 2)
					{
						$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPlacemarkAJAX("jquery");' ."\n";
					}
				}
                                
                                if ($ajaxLoadObjects == 1)
                                {
                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPathAJAX("mootools");' ."\n";
                                }
                                else if ($ajaxLoadObjects == 2)
                                {
                                        $scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GetPathAJAX("jquery");' ."\n";
                                }
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'load\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
				$scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'tilesloaded\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'zoomend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";
                                $scripttext .= '  map'.$mapDivSuffix.'.removeEventListener( \'moveend\', LoadMapObjectsOnce'.$mapDivSuffix.');' ."\n";      
                                $scripttext .= '};' ."\n";	
                               
			}
			
		}
	}
        
    // Geo Position - Begin
    if ((isset($map->autoposition) && (int)$map->autoposition == 1)
     || (isset($map->geolocationcontrol) && (int)$map->geolocationcontrol == 1))
    {
                    $scripttext .= 'function findMyPosition'.$mapDivSuffix.'(AutoPosition, DirectionsDisplay, DirectionsService, Marker, SearchTravelMode, LocationDestination) {' ."\n";

                    $scripttext .= '  var geolocation = new BMap.Geolocation();' ."\n";
                    $scripttext .= '  geolocation.getCurrentPosition(function(r){' ."\n";
                    $scripttext .= '    if(this.getStatus() == BMAP_STATUS_SUCCESS){' ."\n";
                    $scripttext .= '      initialLocation = new BMap.Point(r.point.lng, r.point.lat);' ."\n";
                    $scripttext .= '      map'.$mapDivSuffix.'.setCenter(initialLocation);' ."\n";
                    $scripttext .= '      if (AutoPosition == "Button")' ."\n";
                    $scripttext .= '      {' ."\n";
                    //$scripttext .= '    	 placesACbyButton'.$mapDivSuffix.'(0, DirectionsDisplay, DirectionsService, Marker, "", SearchTravelMode, initialLocation, LocationDestination);' ."\n";
                    $scripttext .= '      }' ."\n";
                    //$scripttext .= '    alert(\'detected\'+r.point.lng+\',\'+r.point.lat);' ."\n";
                    $scripttext .= '    }' ."\n";
                    $scripttext .= '    else {' ."\n";
                    //$scripttext .= '    alert(\'failed\'+this.getStatus());' ."\n";
                    $scripttext .= '    }' ."\n";
                    $scripttext .= '  });'."\n";

                    $scripttext .= '};'."\n";

    }
    // Geo Position - End
        
if ((isset($map->placemark_rating) && ((int)$map->placemark_rating !=0))  
  || ($ajaxLoadObjects != 0)
  || ($ajaxLoadContent != 0)
  )
{
	$scripttext .= 'function PlacemarkRateOver'.$mapDivSuffix.'(p_id, p_idx, p_max) {' ."\n";
	$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateMouseOver(p_id, p_idx, p_max);' ."\n";
	$scripttext .= '};' ."\n";

	$scripttext .= 'function PlacemarkRateOut'.$mapDivSuffix.'(p_id, p_idx, p_max) {' ."\n";
	$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateMouseOut(p_id, p_idx, p_max);' ."\n";
	$scripttext .= '};' ."\n";

	$scripttext .= 'function PlacemarkRateDivOut'.$mapDivSuffix.'(p_id, p_max) {' ."\n";
	$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateDivMouseOut(p_id, p_max);' ."\n";	
	$scripttext .= '};' ."\n";
	
	$scripttext .= 'function PlacemarkRateUpdate'.$mapDivSuffix.'(p_id, p_val, p_max) {' ."\n";
	if ($ajaxLoadObjects != 0)
	{
		if ($ajaxLoadObjects == 1)
		{
			$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
		}
		else if ($ajaxLoadObjects == 2)
		{
			$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
		}
	}
	else
	{
		if ((int)$map->useajax != 0)
		{
			if ((int)$map->useajax == 1)
			{ 
				$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
			}
			else if ((int)$map->useajax == 2)
			{
				$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
			}
		}
		else 
		{
			if ((int)$map->placemark_rating == 1)
			{ 
				$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("mootools", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
			}
			else if ((int)$map->placemark_rating == 2)
			{
				$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.PlacemarkRateUpdate("jquery", p_id, p_val, p_max, \''.$main_lang.'\');' ."\n";	
			}
		}
		
	}
	$scripttext .= '};' ."\n";
	
}

	$scripttext .= 'function PlacemarkByIDShow(p_id, p_action, p_zoom) {' ."\n";
	if ($externalmarkerlink == 1)
	{
		$scripttext .= '  if (p_zoom != undefined && p_zoom != "")' ."\n";
		$scripttext .= '  {' ."\n";
		$scripttext .= '  	map'.$mapDivSuffix.'.setZoom(p_zoom);' ."\n";
		$scripttext .= '  }' ."\n";

		$scripttext .= '  if( allPlacemarkArray[p_id] === undefined ) ' ."\n";
		$scripttext .= '  {' ."\n";
		$scripttext .= '  	alert("Unable to find placemark with ID = " + p_id);' ."\n";
		$scripttext .= '  }' ."\n";
		$scripttext .= '  else' ."\n";
		$scripttext .= '  {' ."\n";
		$scripttext .= '    cur_action = p_action.toLowerCase().split(",");' ."\n";
		$scripttext .= '    for (i = 0; i < cur_action.length; i++) {' ."\n";
		$scripttext .= '      if (cur_action[i] == "click")' ."\n";
		$scripttext .= '      {' ."\n";
		$scripttext .= '    	var f_mo = allPlacemarkArray[p_id].markerobject;'."\n";
                $scripttext .= '        f_mo.dispatchEvent("click");' ."\n";
		$scripttext .= '      }' ."\n";
		$scripttext .= '      else if (cur_action[i] == "center")' ."\n";
		$scripttext .= '      {' ."\n";
		$scripttext .= '  	    map'.$mapDivSuffix.'.setCenter(allPlacemarkArray[p_id].latlngobject);' ."\n";
		$scripttext .= '      }' ."\n";
		$scripttext .= '    }' ."\n";
		$scripttext .= '  }' ."\n";
	}
	else
	{
		$scripttext .= '  	alert("This feature is supported only when you enable it in map menu item or module property!");' ."\n";
	}
	$scripttext .= '}' ."\n";
	

	if ($externalmarkerlink == 1)
	{
		$scripttext .= 'function PlacemarkByID(p_id, p_lat, p_lng, p_obj, p_ll, p_rate) {' ."\n";
		$scripttext .= 'this.id = p_id;' ."\n";
		$scripttext .= 'this.lat = p_lat;' ."\n";
		$scripttext .= 'this.lng = p_lng;' ."\n";
		$scripttext .= 'this.markerobject = p_obj;' ."\n";
		$scripttext .= 'this.latlngobject = p_ll;' ."\n";
		$scripttext .= 'this.rate = p_rate;' ."\n";
		$scripttext .= '}' ."\n";
		
		$scripttext .= 'function PlacemarkByIDAdd(p_id, p_lat, p_lng, p_obj, p_ll, p_rate) {' ."\n";
		$scripttext .= '	allPlacemarkArray[p_id] = new PlacemarkByID(p_id, p_lat, p_lng, p_obj, p_ll, p_rate);' ."\n";
		$scripttext .= '}' ."\n";
	}
	
	
	// Infowin content generated by helper. Need more changes, static methods...
	//if ($zhbdmObjectManager == 0) 
	//{
		$scripttext .= 'function showPlacemarkPanorama'.$mapDivSuffix.'(p_width, p_height, p_pov) {' ."\n";
		if ($managePanelInfowin == 1)
		{
			$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.panelPlacemarkShowPanorama(p_width, p_height, p_pov);' ."\n";
		}
		else
		{
			$scripttext .= '  PlacemarkShowPanorama(map'.$mapDivSuffix.', infowindow'.$mapDivSuffix.', p_width, p_height, p_pov, "'.$mapDivSuffix.'");' ."\n";
		}
		$scripttext .= '};' ."\n";
	//}


	if (isset($map->markergroupcontrol) && (int)$map->markergroupcontrol != 0) 
	{
		$scripttext .= 'function callToggleGroup'.$mapDivSuffix.'(groupid){   ' ."\n";
		$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GroupStateToggle(groupid);' ."\n";
		$scripttext .= '}'."\n";
		
		$scripttext .= 'function callShowAllGroup'.$mapDivSuffix.'(){   ' ."\n";
		$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GroupStateShowAll();' ."\n";
		$scripttext .= '}'."\n";

		$scripttext .= 'function callHideAllGroup'.$mapDivSuffix.'(){   ' ."\n";
		$scripttext .= '  zhbdmObjMgr'.$mapDivSuffix.'.GroupStateHideAll();' ."\n";
		$scripttext .= '}'."\n";
	}

		// Toggle for Insert Markers - Begin
	if (isset($map->usermarkers) 
	    && ((int)$map->usermarkersinsert == 1 || (int)$map->usermarkersupdate == 1)
		&& ((int)$map->usermarkers == 1
			||(int)$map->usermarkers == 2)) 
	{
		if ($allowUserMarker == 1)
		{
				$scripttext .= 'function showonlyone(thename, theid) {'."\n";
				$scripttext .= '  var xPlacemarkA = document.getElementById("bodyInsertPlacemarkA"+theid);'."\n";
				$scripttext .= '  var xPlacemarkGrpA = document.getElementById("bodyInsertPlacemarkGrpA"+theid);'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '  var xContactA = document.getElementById("bodyInsertContactA"+theid);'."\n";
				$scripttext .= '  var xContactAdrA = document.getElementById("bodyInsertContactAdrA"+theid);'."\n";
			}
				$scripttext .= '  if (thename == \'Contact\')'."\n";
				$scripttext .= '  {'."\n";
				$scripttext .= '    var toHide2 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
				$scripttext .= '    var toHide3 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    var toHide1 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
				$scripttext .= '    var toShow = document.getElementById("bodyInsertContact"+theid);'."\n";
			}
				$scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
			}
				$scripttext .= '  }'."\n";
				$scripttext .= '  else if (thename == \'Placemark\')'."\n";
				$scripttext .= '  {'."\n";
				$scripttext .= '    var toHide1 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
				$scripttext .= '    var toShow = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    var toHide2 = document.getElementById("bodyInsertContact"+theid);'."\n";
				$scripttext .= '    var toHide3 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
			}
				$scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
			}
				$scripttext .= '  }'."\n";
				$scripttext .= '  else if (thename == \'PlacemarkGroup\')'."\n";
				$scripttext .= '  {'."\n";
				$scripttext .= '    var toShow = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
				$scripttext .= '    var toHide1 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    var toHide2 = document.getElementById("bodyInsertContact"+theid);'."\n";
				$scripttext .= '    var toHide3 = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
			}
				$scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
			}
				$scripttext .= '  }'."\n";
				$scripttext .= '  else if (thename == \'ContactAddress\')'."\n";
				$scripttext .= '  {'."\n";
				$scripttext .= '    var toHide2 = document.getElementById("bodyInsertPlacemark"+theid);'."\n";
				$scripttext .= '    var toHide3 = document.getElementById("bodyInsertPlacemarkGrp"+theid);'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    var toHide1 = document.getElementById("bodyInsertContact"+theid);'."\n";
				$scripttext .= '    var toShow = document.getElementById("bodyInsertContactAdr"+theid);'."\n";
			}
				$scripttext .= '    xPlacemarkA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xPlacemarkGrpA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'\';'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '    xContactA.innerHTML = \'<img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'\';'."\n";
				$scripttext .= '    xContactAdrA.innerHTML = \'<img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'\';'."\n";
			}
				$scripttext .= '  }'."\n";
				$scripttext .= '  toHide1.style.display = \'none\';'."\n";
				$scripttext .= '  toShow.style.display = \'block\';'."\n";
			if (isset($map->usercontact) && (int)$map->usercontact == 1)
			{
				$scripttext .= '  toHide2.style.display = \'none\';'."\n";
				$scripttext .= '  toHide3.style.display = \'none\';'."\n";
			}
				$scripttext .= '}'."\n";
		}   
	}
	// Toggle for Insert Markers - End
        
if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
	// Do not add loader
}
else
{
	if ($loadtype == "1")
	{
		$scripttext .= ' window.addEvent(\'domready\', initialize'.$mapInitTag.');' ."\n";
	}
	else if ($loadtype == "2")
	{
		$scripttext .= 'var tmpJQ'.$mapDivSuffix.' = jQuery.noConflict();'."\n";
		$scripttext .= ' tmpJQ(document).ready(function() {initialize'.$mapInitTag.'();});' ."\n";
	}
	else
	{
		$scripttext .= ' function addLoadEvent(func) {' ."\n";
		$scripttext .= '  var oldonload = window.onload;' ."\n";
		$scripttext .= '  if (typeof window.onload != \'function\') {' ."\n";
		$scripttext .= '    window.onload = func;' ."\n";
		$scripttext .= '  } else {' ."\n";
		$scripttext .= '    window.onload = function() {' ."\n";
		$scripttext .= '      if (oldonload) {' ."\n";
		$scripttext .= '        oldonload();' ."\n";
		$scripttext .= '      }' ."\n";
		$scripttext .= '      func();' ."\n";
		$scripttext .= '    }' ."\n";
		$scripttext .= '  }' ."\n";
		$scripttext .= '}	' ."\n";	

		$scripttext .= 'addLoadEvent(initialize'.$mapInitTag.');' ."\n";
	}
			

}

//$scripttext .= 'window.onload = initialize;' ."\n";

	
$scripttextEnd .= '/*]]>*/</script>' ."\n";
// Script end


if (isset($MapXdoLoad) && ((int)$MapXdoLoad == 0))
{
	if (isset($useObjectStructure) && (int)$useObjectStructure == 1)
	{
		$this->scripttext = $scripttext;
		$this->scripthead = $scripthead;
		$this->scriptinitialize .= ' initialize'.$mapInitTag.'();' ."\n";
	}
	else
	{
	}
}
else
{
	$scripttextFull = $scripttextBegin . $scripttext. $scripttextEnd;
	echo $scripttextFull;
}



}
// end of main part
