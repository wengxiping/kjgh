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
abstract class comZhBaiduMapPlacemarksHelper
{

	public static function get_MapOverrides($Id)
	{
		
		if ((int)$Id != 0)
		{
			$dbSO = JFactory::getDBO();

			$querySO = $dbSO->getQuery(true);
			$querySO->select('h.*')
				->from('#__zhbaidumaps_text_overrides as h')
				->where('h.id = '.(int) $Id);
			$dbSO->setQuery($querySO);        
			$mySO = $dbSO->loadObject();
			
			
		}

		return $mySO;
	}
	public static function get_placemark_coordinates($markerId)
	{
		if ((int)$markerId != 0)
		{
			$dbMrk = JFactory::getDBO();

			$queryMrk = $dbMrk->getQuery(true);
			$queryMrk->select('h.*')
				->from('#__zhbaidumaps_markers as h')
				->where('h.id = '.(int) $markerId);
			$dbMrk->setQuery($queryMrk);        
			$myMarker = $dbMrk->loadObject();
			
			if (isset($myMarker))
			{
				if ($myMarker->latitude != "" && $myMarker->longitude != "")
				{
					return 'new BMap.Point('.$myMarker->longitude.', ' .$myMarker->latitude.')';
				}
				else
				{
					return 'geocode';
				}
			}
			else
			{
				return '';
			}	
		}
	}
	
	public static function get_placemark_tags($id, $type, $style)
	{
            $addWhereClause = "";
            
            if ((int)$id != 0 && $type != ""
            && ($type == "com_zhbaidumap.mapmarker" || $type == "com_contact.contact"))
            {
                    switch ((int)$style) 
                    {

                        case 0:
                                $tagstyle = '-simple';
                        break;
                        case 1:
                                $tagstyle = '-advanced';
                        break;
                        case 2:
                                $tagstyle = '-external';
                        break;
                        default:
                                $tagstyle = '-simple';
                        break;
                    }
                            
                    $dbMrk = JFactory::getDBO();

                    $addWhereClause .= 'm.content_item_id='.(int)$id;
                    $addWhereClause .= ' and t.type_alias=\''.$type.'\'';                    

                    $queryMrk = $dbMrk->getQuery(true);
                    $queryMrk->select('tg.title')
                            ->from('#__contentitem_tag_map as m')
                            ->leftJoin('#__content_types as t ON m.type_id=t.type_id')
                            ->leftJoin('#__tags as tg ON m.tag_id=tg.id')
                            ->where($addWhereClause)
                            ;
                    
                    $queryMrk->order('tg.title');
                    
                    $dbMrk->setQuery($queryMrk);        

                    $myMarker = $dbMrk->loadObjectList();

                    $cur_name = "";

                    if (isset($myMarker) && !empty($myMarker))
                    {
                        //$cur_name = count($myMarker);

                        foreach ($myMarker as $key => $myMrk) 
                        {
                            $cur_name .= '<div id="BDMapsMarkerTagITEM'.(int)$id.'" class="zhbdm-placemark-tag-item'.$tagstyle.'-div">';
                            $cur_name .= htmlspecialchars(str_replace('\\', '/', $myMrk->title) , ENT_QUOTES, 'UTF-8');
                            $cur_name .= '</div>';
                        }
                    }

                    return $cur_name;
            }

	}
   
	public static function get_placemark_content_string(
						$currentArticleId,
						$currentmarker, $usercontact, $useruser,
						$usercontactattributes, $service_DoDirection,
						$imgpathIcons, $imgpathUtils, $directoryIcons, $placemark_rating, $lang, $titleTag, $showCreateInfo)
	{
	
 		$currentLanguage = JFactory::getLanguage();
		$currentLangTag = $currentLanguage->getTag();
		
		if (isset($titleTag) && $titleTag != "")
		{
			if ($titleTag == "h2"
			 || $titleTag == "h3")
			{
				$currentTitleTag = $titleTag;
			}
			else
			{
				$currentTitleTag ='h2';
			}
		}
		else
		{
			$currentTitleTag ='h2';
		}
		
                $main_lang_little = "";
                
		if (isset($lang) && $lang != "")
		{
                        $main_lang_little = substr($lang,0, strpos($lang, '-'));
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $lang, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $lang, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap' , $lang, true);	
		}
		else
		{
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $currentLangTag, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $currentLangTag, true);		
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap', $currentLangTag, true);		
		}
           
            
                $returnText = '';
		$userContactAttrs = explode(",", $usercontactattributes);

		for($i = 0; $i < count($userContactAttrs); $i++) 
		{
			$userContactAttrs[$i] = strtolower(trim($userContactAttrs[$i]));
		}
	  
			$returnText .= '\'<div id="placemarkContent'. $currentmarker->id.'" class="placemarkContent">\' +	' ."\n";
			if (isset($currentmarker->markercontent) &&
				(((int)$currentmarker->markercontent == 0) ||
				 ((int)$currentmarker->markercontent == 1) ||
                                ((int)$currentmarker->markercontent == 9) )
				)
			{
				$returnText .= '\'<'.$currentTitleTag.' id="headContent'. $currentmarker->id.'" class="placemarkHead">'.'\'+' ."\n";
				$returnText .= '\''.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'\'+'."\n";
				$returnText .= '\'</'.$currentTitleTag.'>\'+' ."\n";
			}
			$returnText .= '\'<div id="bodyContent'. $currentmarker->id.'"  class="placemarkBody">\'+'."\n";

			if ($currentmarker->hrefimage!="")
			{
				$tmp_image_path = strtolower($currentmarker->hrefimage);
				if (substr($tmp_image_path,0,5) == "http:"
				|| substr($tmp_image_path,0,6) == "https:"
				|| substr($tmp_image_path,0,1) == "/"
				|| substr($tmp_image_path,0,1) == ".")
				{
					$tmp_image_path_add = "";
				}
				else
				{
					$tmp_image_path_add = "/";
				}
				$returnText .= '\'<img src="'.$tmp_image_path_add.$currentmarker->hrefimage.'" alt="" />\'+'."\n";
			}

			if (isset($currentmarker->markercontent) &&
				(((int)$currentmarker->markercontent == 0) ||
				 ((int)$currentmarker->markercontent == 2))
				)
			{
				$returnText .= '\''.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'\'+'."\n";
			}
                        
                        $pluginPrepContentText = $currentmarker->descriptionhtml;
                        
                        if (isset($currentmarker->preparecontent) && (int)$currentmarker->preparecontent != 0)
                        {
                            $pluginPrepContentText = JHtml::_('content.prepare', $pluginPrepContentText);
                            $pluginPrepContentText = str_replace(array("\r", "\r\n", "\n"), '', $pluginPrepContentText);
                            // protect double replace in case \'  
                            $pluginPrepContentText = str_replace("\\\'", "\n", $pluginPrepContentText);
                            $pluginPrepContentText = str_replace("'", "\'", $pluginPrepContentText);
                            $pluginPrepContentText = str_replace("\n", "\'", $pluginPrepContentText);
                        }
                        else 
                        {
                            $pluginPrepContentText = str_replace(array("\r", "\r\n", "\n"), '', $pluginPrepContentText);
                            $pluginPrepContentText = str_replace("'", "\'", $pluginPrepContentText);                            
                        }
                        
                        $returnText .= '\''.$pluginPrepContentText.'\'+'."\n";
			//$returnText .= '\''.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'\'+'."\n";

			if (isset($currentmarker->markercontent) &&
				((int)$currentmarker->markercontent == 9 
				 || (int)$currentmarker->markercontent == 8) &&
				((isset($currentmarker->articleid) && (int)$currentmarker->articleid != 0) 
				  || (isset($currentmarker->hrefarticle) && $currentmarker->hrefarticle != ""))
				)
			{			
				$returnText .= '\'<div id="article'. $currentmarker->id.'"  class="iframeArticle">\'+'."\n";		
				$returnText .= '\'<iframe id="articleiframe'. $currentmarker->id.'"';
                                if ($currentmarker->hrefarticle != "")
				{
					$returnText .= ' src="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->hrefarticle), ENT_QUOTES, 'UTF-8').'"';
				}
				else
				{
					$returnText .= ' src="'.JURI::base().'index.php?option=com_content&amp;view=article&amp;id='.$currentmarker->articleid.'&amp;tmpl=component"';
				}
				if (isset($currentmarker->iframearticleclass) && $currentmarker->iframearticleclass)
				{
					$returnText .= ' class="'.$currentmarker->iframearticleclass.'"';	
				}				
				$returnText .= '>';	
				$returnText .= '</iframe>\'+'."\n";
				$returnText .= '\'</div>\'+'."\n";
			}
			// Contact info - begin
			if (isset($usercontact) && ((int)$usercontact != 0))
			{
				if (isset($currentmarker->showcontact) && ((int)$currentmarker->showcontact != 0))
				{
					switch ((int)$currentmarker->showcontact) 
					{
						case 1:
							for($i = 0; $i < count($userContactAttrs); $i++) 
							{
								if ($currentmarker->contact_name != ""
								&& $userContactAttrs[$i] == 'name') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_NAME').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_position != ""
								&& $userContactAttrs[$i] == 'position') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_POSITION').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_position), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_address != ""
								&& $userContactAttrs[$i] == 'address') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS').' '.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'suburb') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_SUBURB_SUBURB').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'city') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_SUBURB_CITY').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'state') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_STATE_STATE').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'province') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_STATE_PROVINCE').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_country != ""
								&& $userContactAttrs[$i] == 'country') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_COUNTRY').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_country), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'postcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_POSTCODE_POSTAL').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'zipcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_POSTCODE_ZIP').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_phone != ""
								&& $userContactAttrs[$i] == 'phone') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_PHONE').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_mobile != ""
								&& $userContactAttrs[$i] == 'mobile') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_MOBILE').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_mobile), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_fax != ""
								&& $userContactAttrs[$i] == 'fax') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_FAX').' '.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_fax), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_email != ""
								&& $userContactAttrs[$i] == 'email') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_EMAIL').' '.str_replace('@','&#64;',htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_email), ENT_QUOTES, 'UTF-8')).'</p>\'+'."\n";
								}
								if ($currentmarker->contact_webpage != ""
								&& $userContactAttrs[$i] == 'website') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_WEBSITE').' '.'<a href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'" target="_blank">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'</a> '.'</p>\'+'."\n";
								}                                                                
							}			

						break;
						case 2:
							for($i = 0; $i < count($userContactAttrs); $i++) 
							{
								if ($currentmarker->contact_name != ""
								&& $userContactAttrs[$i] == 'name') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_position != ""
								&& $userContactAttrs[$i] == 'position') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_position), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_address != ""
								&& $userContactAttrs[$i] == 'address') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'address.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS').'" />'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
								}

								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'suburb') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'city') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'state') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'province') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_country != ""
								&& $userContactAttrs[$i] == 'country') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_country), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'postcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'zipcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_phone != ""
								&& $userContactAttrs[$i] == 'phone') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'phone.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_PHONE').'" />'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_mobile != ""
								&& $userContactAttrs[$i] == 'mobile') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'mobile.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_MOBILE').'" />'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_mobile), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_fax != ""
								&& $userContactAttrs[$i] == 'fax') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'fax.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_FAX').'" />'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_fax), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_email != ""
								&& $userContactAttrs[$i] == 'email') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'email.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_EMAIL').'" />'.str_replace('@','&#64;',htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_email), ENT_QUOTES, 'UTF-8')).'</p>\'+'."\n";
								}
                                                                if ($currentmarker->contact_webpage != ""
								&& $userContactAttrs[$i] == 'website') 
								{
									$returnText .= '\'<p class="placemarkBodyContact"><img src="'.$imgpathUtils.'website.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_CONTACT_WEBSITE').'" />'.'<a href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'" target="_blank">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'</a> '.'</p>\'+'."\n";
								}                                                               
							}
						break;
						case 3:
							for($i = 0; $i < count($userContactAttrs); $i++) 
							{
								if ($currentmarker->contact_name != ""
								&& $userContactAttrs[$i] == 'name') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_position != ""
								&& $userContactAttrs[$i] == 'position') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_position), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_address != ""
								&& $userContactAttrs[$i] == 'address') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
								}

								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'suburb') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'city') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'state') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'province') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_country != ""
								&& $userContactAttrs[$i] == 'country') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_country), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'postcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'zipcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_phone != ""
								&& $userContactAttrs[$i] == 'phone') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_mobile != ""
								&& $userContactAttrs[$i] == 'mobile') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_mobile), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_fax != ""
								&& $userContactAttrs[$i] == 'fax') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_fax), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_email != ""
								&& $userContactAttrs[$i] == 'email') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.str_replace('@','&#64;',htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_email), ENT_QUOTES, 'UTF-8')).'</p>\'+'."\n";
								}
								if ($currentmarker->contact_webpage != ""
								&& $userContactAttrs[$i] == 'website') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.'<a href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'" target="_blank">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'</a> '.'</p>\'+'."\n";
								}                                                                
                                                                
							}
						break;
						default:
							for($i = 0; $i < count($userContactAttrs); $i++) 
							{
								if ($currentmarker->contact_name != ""
								&& $userContactAttrs[$i] == 'name') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_position != ""
								&& $userContactAttrs[$i] == 'position') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_position), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_address != ""
								&& $userContactAttrs[$i] == 'address') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
								}
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'suburb') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_suburb != ""
								&& $userContactAttrs[$i] == 'city') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_suburb), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'state') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_state != ""
								&& $userContactAttrs[$i] == 'province') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_state), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_country != ""
								&& $userContactAttrs[$i] == 'country') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_country), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'postcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_postcode != ""
								&& $userContactAttrs[$i] == 'zipcode') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_postcode), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_phone != ""
								&& $userContactAttrs[$i] == 'phone') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_mobile != ""
								&& $userContactAttrs[$i] == 'mobile') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_mobile), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								if ($currentmarker->contact_fax != ""
								&& $userContactAttrs[$i] == 'fax') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_fax), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
								}
								
								if ($currentmarker->contact_email != ""
								&& $userContactAttrs[$i] == 'email') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.str_replace('@','&#64;',htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_email), ENT_QUOTES, 'UTF-8')).'</p>\'+'."\n";
								}
                                                                if ($currentmarker->contact_webpage != ""
								&& $userContactAttrs[$i] == 'website') 
								{
									$returnText .= '\'<p class="placemarkBodyContact">'.'<a href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'" target="_blank">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->contact_webpage), ENT_QUOTES, 'UTF-8').'</a> '.'</p>\'+'."\n";
								}                                                                
							}
						break;										
					}
				}
			}
			// Contact info - end
			// User info - begin
			if (isset($useruser) && ((int)$useruser != 0))
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::get_userinfo_for_marker(
														$currentmarker->createdbyuser, $currentmarker->showuser,
														$imgpathIcons, $imgpathUtils, $directoryIcons);
			}
			// User info - end
			
			if ($currentmarker->hrefsite!="")
			{
				$returnText .= '\'<p><a class="placemarkHREF" href="'.$currentmarker->hrefsite.'" target="_blank">';
				if ($currentmarker->hrefsitename != "")
				{
					$returnText .= htmlspecialchars($currentmarker->hrefsitename, ENT_QUOTES, 'UTF-8');
				}
				else
				{
					$returnText .= $currentmarker->hrefsite;
				}
				$returnText .= '</a></p>\'+'."\n";
			}

			$returnText .= '\'</div>\'+'."\n";
			
			if ((int)$currentmarker->tag_show != 0)
			{
				switch ((int)$currentmarker->tag_style) 
				{

					case 0:
							$tagstyle = '-simple';
					break;
					case 1:
							$tagstyle = '-advanced';
					break;
					case 2:
							$tagstyle = '-external';
					break;
					default:
							$tagstyle = '-simple';
					break;
				}

				$returnText .= '\'<div id="BDMapsMarkerTagDIV'.(int)$currentmarker->id.'" class="zhbdm-placemark-tag'.$tagstyle.'-div">\'+'."\n";

				if ((int)$currentmarker->tag_show == 1)
				{
					$markerTags = comZhBaiduMapPlacemarksHelper::get_placemark_tags($currentmarker->id, "com_zhgooglemap.mapmarker", $currentmarker->tag_style);
					$returnText .= '\'<div id="BDMapsMarkerTagMarker'.(int)$currentmarker->id.'" class="zhbdm-placemark-tag-marker'.$tagstyle.'-div">'.$markerTags.'</div>\'+'."\n";
				}
				if ((int)$currentmarker->tag_show == 2)
				{
					if ((int)$currentmarker->contactid != 0)
					{
						$markerTags = comZhBaiduMapPlacemarksHelper::get_placemark_tags($currentmarker->contactid, "com_contact.contact", $currentmarker->tag_style);
						$returnText .= '\'<div id="BDMapsMarkerTagContact'.(int)$currentmarker->id.'" class="zhbdm-placemark-tag-contact'.$tagstyle.'-div">'.$markerTags.'</div>\'+'."\n";
					}
				}
				
				$returnText .= '\'</div>\'+'."\n";
						
			}

			$toolbarCreateInfoFlg = 0;
			
			if (isset($showCreateInfo) && (int)$showCreateInfo != 0)
			{
				$toolbarCreateInfoText = "";
				
				$toolbarCreateInfo = comZhBaiduMapPlacemarksHelper::get_userinfo_for_marker_timestamp(
														$currentmarker->createdbyuser);
				if ((((int)$showCreateInfo == 1) || ((int)$showCreateInfo == 3) ||
					 ((int)$showCreateInfo == 51) || ((int)$showCreateInfo == 53))
					&& $toolbarCreateInfo != "")
				{
					if (((int)$showCreateInfo == 1) || ((int)$showCreateInfo == 3))
					{
						$toolbarCreateInfoFlg = 1;
					}
					elseif (((int)$showCreateInfo == 51) || ((int)$showCreateInfo == 53))
					{
						$toolbarCreateInfoFlg = 2;
					}
					else
					{
						$toolbarCreateInfoFlg = 1;
					}
					
					$toolbarCreateInfoText .=  '\'<div id="BDMapsMarkerStampUserDIV" class="zhbdm-placemark-stamp-user-div">\'+'."\n";
					$toolbarCreateInfoText .= '\''.$toolbarCreateInfo.'\'+'."\n";
					$toolbarCreateInfoText .= '\'</div>\'+'."\n";
				}
				
				$toolbarCreateInfo = comZhBaiduMapPlacemarksHelper::get_datetime_for_marker_timestamp(
														$currentmarker->createddate);
				if ((((int)$showCreateInfo == 2) || ((int)$showCreateInfo == 3) ||
					 ((int)$showCreateInfo == 52) || ((int)$showCreateInfo == 53))
					&& $toolbarCreateInfo != "")
				{
					if (((int)$showCreateInfo == 2) || ((int)$showCreateInfo == 3))
					{
						$toolbarCreateInfoFlg = 1;
					}
					elseif (((int)$showCreateInfo == 52) || ((int)$showCreateInfo == 53))
					{
						$toolbarCreateInfoFlg = 2;
					}
					else
					{
						$toolbarCreateInfoFlg = 1;
					}

					$toolbarCreateInfoText .=  '\'<div id="BDMapsMarkerStampDateDIV" class="zhbdm-placemark-stamp-date-div">\'+'."\n";
					$toolbarCreateInfoText .= '\''.$toolbarCreateInfo.'\'+'."\n";
					$toolbarCreateInfoText .= '\'</div>\'+'."\n";
				}
				
			}
                        
			// Placemark Toolbar - begin
			$toolbarToolbarFlg = 0;
			$toolbarToolbarText = "";
                        if ($service_DoDirection == 1)
			{
				$toolbarToolbarFlg = 1;
				$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
				$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-start" href="#" title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_FINISH').'" onclick="';
				$toolbarToolbarText .= 'setRouteDestination'.$currentArticleId.'(0);';
				$toolbarToolbarText .= ' return false;"><img class="zhbdm-placemark-action-toolbaritem-img-start" src="'.$imgpathUtils.'start.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_FINISH').'" /></a>\'+'."\n";
				$toolbarToolbarText .= '\'</div>\'+'."\n";
				$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
				$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-finish" href="#" title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_START').'" onclick="';
				$toolbarToolbarText .= 'setRouteDestination'.$currentArticleId.'(1);';
				$toolbarToolbarText .= ' return false;"><img class="zhbdm-placemark-action-toolbaritem-img-finish" src="'.$imgpathUtils.'finish.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_START').'" /></a>\'+'."\n";
				$toolbarToolbarText .= '\'</div>\'+'."\n";
			}

			
			
			if (isset($usercontact) && (int)$usercontact != 0 && isset($currentmarker->toolbarcontact) && (int)$currentmarker->toolbarcontact != 0)
			{	
				
				if ((int)$currentmarker->toolbarcontact == 1)
				{
					$toolbarLinkTarget = " target=\"_blank\"";
				}
				else
				{
					$toolbarLinkTarget = "";
				}
				
				if (isset($currentmarker->hrefcontact) && $currentmarker->hrefcontact != "")
				{
					// Check alternative contact URL
					$toolbarToolbarFlg = 1;
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-contact" href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->hrefcontact), ENT_QUOTES, 'UTF-8').'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_CONTACT').'"><img class="zhbdm-placemark-action-toolbaritem-img-contact" src="'.$imgpathUtils.'contact.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_CONTACT').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
				else if (isset($currentmarker->contactid) && (int)$currentmarker->contactid != 0)
				{
					// Check contact ID for default URL
					$toolbarToolbarFlg = 1;
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-contact" href="'.JURI::base().'index.php?option=com_contact&amp;view=contact&amp;id='.$currentmarker->contactid.'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_CONTACT').'"><img class="zhbdm-placemark-action-toolbaritem-img-contact" src="'.$imgpathUtils.'contact.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_CONTACT').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
			}

			if (isset($currentmarker->toolbararticle) && (int)$currentmarker->toolbararticle != 0)
			{

				if ((int)$currentmarker->toolbararticle == 1)
				{
					$toolbarLinkTarget = " target=\"_blank\"";
				}
				else
				{
					$toolbarLinkTarget = "";
				}
			
				if (isset($currentmarker->hrefarticle) && $currentmarker->hrefarticle != "")
				{
					// Check alternative article URL
					$toolbarToolbarFlg = 1;
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-article" href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->hrefarticle), ENT_QUOTES, 'UTF-8').'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_ARTICLE').'"><img class="zhbdm-placemark-action-toolbaritem-img-article" src="'.$imgpathUtils.'article.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_ARTICLE').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
				else if (isset($currentmarker->articleid) && (int)$currentmarker->articleid != 0)
				{
					$toolbarToolbarFlg = 1;
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-article" href="'.JURI::base().'index.php?option=com_content&amp;view=article&amp;id='.$currentmarker->articleid.'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_ARTICLE').'"><img class="zhbdm-placemark-action-toolbaritem-img-article" src="'.$imgpathUtils.'article.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_ARTICLE').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
			}

			if ((isset($currentmarker->toolbardetail) && (int)$currentmarker->toolbardetail != 0)
                            // need to remove when new placemark detail view will be ported
                            && (isset($currentmarker->hrefdetail) && $currentmarker->hrefdetail != ""))
			{	
				if ((int)$currentmarker->toolbardetail == 1)
				{
					$toolbarLinkTarget = " target=\"_blank\"";
				}
				else
				{
					$toolbarLinkTarget = "";
				}
				
		
				if (isset($currentmarker->hrefdetail) && $currentmarker->hrefdetail != "")
				{
					
					
					// Check alternative details URL
					$toolbarToolbarFlg = 1;
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-details" href="'.htmlspecialchars(str_replace('\\', '/', $currentmarker->hrefdetail), ENT_QUOTES, 'UTF-8').'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_DETAILS').'"><img class="zhbdm-placemark-action-toolbaritem-img-details" src="'.$imgpathUtils.'details.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_DETAILS').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
				else if (isset($currentmarker->id) && (int)$currentmarker->id != 0)
				{
					
					$toolbarToolbarFlg = 1;

					$detailsAttrs = str_replace(";", ',',$currentmarker->attributesdetail);
					$detailsAttrArray = explode(",", $detailsAttrs);

					for($i = 0; $i < count($detailsAttrArray); $i++) 
					{
						$detailsAttrArray[$i] = strtolower(trim($detailsAttrArray[$i]));
					}

					$load_bs = 9;
					$prop_thumbnail = 0;
					$prop_imagegalery = 0;
					$prop_hidedescriptionhtml = 1;
					$prop_showdescriptionfullhtml = 0;
					
					for($i = 0; $i < count($detailsAttrArray); $i++) 
					{
					
						switch ($detailsAttrArray[$i]) 
						{
							
							case 'load bootstrap':
								$load_bs = 0;
							break;
							case 'load bootstrap styles':
								$load_bs = 1;
							break;
							case 'thumbnail':
								$prop_thumbnail = 1;
							break;
							case 'image galery':
								$prop_imagegalery = 1;
							break;
							case 'addition html text':
								$prop_hidedescriptionhtml = 0;
							break;
							case 'full description': 
								$prop_showdescriptionfullhtml = 1;
							break;
						}						
					}
					
					
					$toolbarToolbarText .= '\'<div class="zhbdm-placemark-action-toolbaritem">\'+'."\n";
					$toolbarToolbarText .= '\'<a class="zhbdm-placemark-action-toolbaritem-a-details" href="'.JURI::base().'index.php?option=com_zhbaidumap&amp;view=placemark&amp;load_bootstrap='.$load_bs
															  .'&amp;thumbnail='.$prop_thumbnail
															  .'&amp;imagegalery='.$prop_imagegalery
															  .'&amp;hidedescriptionhtml='.$prop_hidedescriptionhtml
															  .'&amp;showdescriptionfullhtml='.$prop_showdescriptionfullhtml
															  .'&amp;id=' . $currentmarker->id.'" '.$toolbarLinkTarget.' title="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_DETAILS').'"><img class="zhbdm-placemark-action-toolbaritem-img-details" src="'.$imgpathUtils.'details.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_ACTION_DETAILS').'" /></a>\'+'."\n";
					$toolbarToolbarText .= '\'</div>\'+'."\n";
				}
			}

			if ($toolbarToolbarFlg == 1)
			{
				if ($toolbarCreateInfoFlg == 1)
				{
					$returnText .=  '\'<div id="BDMapsMarkerStampDIV" class="zhbdm-placemark-stamp-top-div">\'+'."\n";
					$returnText .=  $toolbarCreateInfoText;
					$returnText .= '\'</div>\'+'."\n";
				}

				$returnText .=  '\'<div id="BDMapsMarkerActionDIV" class="zhbdm-placemark-action-div">\'+'."\n";
				$returnText .=  '\'<div id="BDMapsMarkerActionTOOLBAR" class="zhbdm-placemark-action-toolbar">\'+'."\n";
				$returnText .=  $toolbarToolbarText;
				$returnText .= '\'</div>\'+'."\n";
				$returnText .= '\'</div>\'+'."\n";

				if ($toolbarCreateInfoFlg == 2)
				{
					$returnText .=  '\'<div id="BDMapsMarkerStampDIV" class="zhbdm-placemark-stamp-bottom-div">\'+'."\n";
					$returnText .=  $toolbarCreateInfoText;
					$returnText .= '\'</div>\'+'."\n";
				}

			}
			else
			{
				if ($toolbarCreateInfoFlg != 0)
				{
					$returnText .=  '\'<div id="BDMapsMarkerStampDIV" class="zhbdm-placemark-stamp-top-bottom-div">\'+'."\n";
					$returnText .=  $toolbarCreateInfoText;
					$returnText .= '\'</div>\'+'."\n";
				}
			} 			
                        
			// Placemark Toolbar - end
			
			$returnText .= '\'</div>\';'."\n";
			// contentString - End

		return $returnText;
	}
	
	
	protected static function get_userinfo_for_marker($userId, $showuser, $imgpathIcons, $imgpathUtils, $directoryIcons)
	{
		
		if ((int)$userId != 0)
		{
			$cur_user_name = '';
			$cur_user_address = '';
			$cur_user_phone = '';
			
			$dbUsr = JFactory::getDBO();
			$queryUsr = $dbUsr->getQuery(true);
			
			$queryUsr->select('p.*, h.name as profile_username')
				->from('#__users as h')
				->leftJoin('#__user_profiles as p ON p.user_id=h.id')
				->where('h.id = '.(int)$userId);

			$dbUsr->setQuery($queryUsr);        
			$myUsr = $dbUsr->loadObjectList();
			
			if (isset($myUsr))
			{
				
				foreach ($myUsr as $key => $currentUsers) 
				{
					$cur_user_name = $currentUsers->profile_username;

					if ($currentUsers->profile_key == 'profile.address1')
					{
						$cur_user_address = $currentUsers->profile_value;
					}
					else if ($currentUsers->profile_key == 'profile.phone')
					{
						$cur_user_phone = $currentUsers->profile_value;
					}
					
					
				}
				
				$cur_scripttext = '';
				
				if (isset($showuser) && ((int)$showuser != 0))
				{
					switch ((int)$showuser) 
					{
						case 1:
							if ($cur_user_name != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_USER_NAME').' '.htmlspecialchars(str_replace('\\', '/', $cur_user_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
							if ($cur_user_address != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_USER_ADDRESS').' '.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $cur_user_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
							}
							if ($cur_user_phone != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.JText::_('COM_ZHBAIDUMAP_MAP_USER_USER_PHONE').' '.htmlspecialchars(str_replace('\\', '/', $cur_user_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
						break;
						case 2:
							if ($cur_user_name != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.htmlspecialchars(str_replace('\\', '/', $cur_user_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
							if ($cur_user_address != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser"><img src="'.$imgpathUtils.'address.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_USER_ADDRESS').'" />'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $cur_user_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
							}
							if ($cur_user_phone != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser"><img src="'.$imgpathUtils.'phone.png" alt="'.JText::_('COM_ZHBAIDUMAP_MAP_USER_USER_PHONE').'" />'.htmlspecialchars(str_replace('\\', '/', $cur_user_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
						break;
						case 3:
							if ($cur_user_name != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.htmlspecialchars(str_replace('\\', '/', $cur_user_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
							if ($cur_user_address != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $cur_user_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
							}
							if ($cur_user_phone != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.htmlspecialchars(str_replace('\\', '/', $cur_user_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
						break;
						default:
							if ($cur_user_name != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.htmlspecialchars(str_replace('\\', '/', $cur_user_name), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
							if ($cur_user_address != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.str_replace('<br /><br />', '<br />',str_replace(array("\r", "\r\n", "\n"), '<br />', htmlspecialchars(str_replace('\\', '/', $cur_user_address), ENT_QUOTES, 'UTF-8'))).'</p>\'+'."\n";
							}
							if ($cur_user_phone != "") 
							{
								$cur_scripttext .= '\'<p class="placemarkBodyUser">'.htmlspecialchars(str_replace('\\', '/', $cur_user_phone), ENT_QUOTES, 'UTF-8').'</p>\'+'."\n";
							}
						break;										
					}
				}
				
				return $cur_scripttext;
			}
			else
			{
				return '';
			}	
		}
		else
		{
			return '';
		}	
		
		
	}
	
	protected static function get_datetime_for_marker_timestamp($datetime)
	{
		$db = JFactory::getDBO();
		$nullDate = $db->getNullDate();

		$private_ret_val = '';
	
		if ($datetime != $nullDate)
		{
			$private_ret_val = htmlspecialchars(str_replace('\\', '/', $datetime), ENT_QUOTES, 'UTF-8');
		}

		return $private_ret_val;
		
	}
	
	
	protected static function get_userinfo_for_marker_timestamp($userId)
	{

		$cur_user_name = '';
	
		if ((int)$userId != 0)
		{
			
			$dbUsr = JFactory::getDBO();
			$queryUsr = $dbUsr->getQuery(true);
			
			$queryUsr->select('h.name as usr_username')
				->from('#__users as h')
				->where('h.id = '.(int)$userId);

			$dbUsr->setQuery($queryUsr);        
			$myUsr = $dbUsr->loadObject();
			
			if (isset($myUsr))
			{
				$cur_user_name = htmlspecialchars(str_replace('\\', '/', $myUsr->usr_username), ENT_QUOTES, 'UTF-8');
			}
		}

		return $cur_user_name;
		
	}
	
	private static function get_placemarklist_action_string($type, $currentArticleId, $currentmarkerid, $markerlistaction)
	{
		$scripttext = '';
		
		if ($type == 1)
		{
					$scripttext .= ' markerLI.onclick = function(){ zhbdmObjMgr'.$currentArticleId.'.PlacemarkListAction('. $currentmarkerid.')};'."\n";
		}
		else
		{
				if ((int)$markerlistaction == 0) 
				{
					$scripttext .= ' markerLI.onclick = function(){ map'.$currentArticleId.'.setCenter(latlng'. $currentmarkerid.')};'."\n";
				}
				else if ((int)$markerlistaction == 1) 
				{
					$scripttext .= ' markerLI.onclick = function(){ baidu.maps.event.trigger(marker'. $currentmarkerid.', "click")};'."\n";
				}
				else if ((int)$markerlistaction == 11) 
				{
					$scripttext .= ' markerLI.onclick = function(){ '.
					' map'.$currentArticleId.'.setCenter(latlng'. $currentmarkerid.');'.
					' baidu.maps.event.trigger(marker'. $currentmarkerid.', "click");'.
					'};'."\n";
				}
				else if ((int)$markerlistaction == 2) 
				{
					$scripttext .= ' markerLI.onclick = function(){ '.
					' map'.$currentArticleId.'.setCenter(latlng'. $currentmarkerid.');'.
					' Map_Animate_Marker(map'.$currentArticleId.', marker'. $currentmarkerid.');'.
					'};'."\n";
				}
				else if ((int)$markerlistaction == 3) 
				{
					$scripttext .= ' markerLI.onclick = function(){ '.
					' Map_Animate_Marker(map'.$currentArticleId.', marker'. $currentmarkerid.');'.
					' baidu.maps.event.trigger(marker'. $currentmarkerid.', "click");'.
					'};'."\n";
				}											
				else if ((int)$markerlistaction == 12) 
				{
					$scripttext .= ' markerLI.onclick = function(){ '.
					' map'.$currentArticleId.'.setCenter(latlng'. $currentmarkerid.');'.
					' Map_Animate_Marker(map'.$currentArticleId.', marker'. $currentmarkerid.');'.
					' baidu.maps.event.trigger(marker'. $currentmarkerid.', "click");'.
					'};'."\n";
				}
				else
				{
					$scripttext .= ' markerLI.onclick = function(){ map'.$currentArticleId.'.setCenter(latlng'. $currentmarkerid.')};'."\n";
				}			
		}

		
		return $scripttext;
	}

	public static function get_placemarklist_string(
						$type,
						$currentArticleId, 
						$currentmarker, 
						$markerlistcssstyle,
						$markerlistpos,
						$markerlistcontent,
						$markerlistaction,
						$imgpathIcons)
	{
		$scripttext = '';
		if (isset($markerlistpos) && (int)$markerlistpos != 0) 
		{						
			if (isset($currentmarker->includeinlist))
			{
				$doAddToList = (int)$currentmarker->includeinlist;							 
			}
			else
			{
				$doAddToList = 1;
			}
			
			if ($doAddToList == 1)
			{
				$scripttext .= 'if (markerUL)'."\n";
				$scripttext .= '{'."\n";
				if ((int)$markerlistcontent < 100) 
				{								
						$scripttext .= ' var markerLI = document.createElement(\'li\');'."\n";
						$scripttext .= ' markerLI.id = "zhbdm_pmlist_item_'.$currentArticleId.'_'.$currentmarker->id.'";'."\n";
						$scripttext .= ' markerLI.className = "zhbdm-li-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerLIWrp = document.createElement(\'div\');'."\n";
						$scripttext .= ' markerLIWrp.className = "zhbdm-li-wrp-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerASelWrp = document.createElement(\'div\');'."\n";
						$scripttext .= ' markerASelWrp.className = "zhbdm-li-wrp-a-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerASel = document.createElement(\'a\');'."\n";
						$scripttext .= ' markerASel.className = "zhbdm-li-a-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' markerASel.id = "zhbdm_pmlist_'.$currentArticleId.'_'.$currentmarker->id.'";'."\n";
						$scripttext .= ' markerASel.href = \'javascript:void(0);\';'."\n";
						if ((int)$markerlistcontent == 0) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-0-li-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 1) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-1-lit-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-1-liw-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-1-lid-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 5) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-5-lit-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-5-liw-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-5-lid-'.$markerlistcssstyle.'">'.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 2) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-2-liw-icon-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDIcon'. $currentmarker->id.'" class="zhbdm-2-lii-icon-'.$markerlistcssstyle.'"><img src="';
							if ((int)$currentmarker->overridemarkericon == 0)
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype);
							}
							else
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype);
							}
							$scripttext .= '.png" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-2-lit-icon-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'\'+'."\n";
							$scripttext .= ' \'</div></div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 3) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-3-liw-icon-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDIcon'. $currentmarker->id.'" class="zhbdm-3-lii-icon-'.$markerlistcssstyle.'"><img src="';
							if ((int)$currentmarker->overridemarkericon == 0)
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype);
							}
							else
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype);
							}
							$scripttext .= '.png" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-3-lit-icon-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-3-liwd-icon-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-3-lid-icon-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 6) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-6-liw-icon-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDIcon'. $currentmarker->id.'" class="zhbdm-6-lii-icon-'.$markerlistcssstyle.'"><img src="';
							if ((int)$currentmarker->overridemarkericon == 0)
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype);
							}
							else
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype);
							}
							$scripttext .= '.png" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-6-lit-icon-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-6-liwd-icon-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-6-lid-icon-'.$markerlistcssstyle.'">'.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 4) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'';									
							$scripttext .= '<table class="zhbdm-4-table-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<tbody>';
							$scripttext .= '<tr class="zhbdm-4-row-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<td rowspan=2 class="zhbdm-4-tdicon-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<img src="';
							if ((int)$currentmarker->overridemarkericon == 0)
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype);
							}
							else
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype);
							}
							$scripttext .= '.png" alt="" />';
							$scripttext .= '</td>';
							$scripttext .= '<td class="zhbdm-4-tdtitle-icon-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '<tr>';
							$scripttext .= '<td class="zhbdm-4-tddesc-icon-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '</tbody>';
							$scripttext .= '</table>';
							$scripttext .= ' \';'."\n";
						}
						else if ((int)$markerlistcontent == 7) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'';									
							$scripttext .= '<table class="zhbdm-7-table-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<tbody>';
							$scripttext .= '<tr class="zhbdm-7-row-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<td rowspan=2 class="zhbdm-7-tdicon-icon-'.$markerlistcssstyle.'">';
							$scripttext .= '<img src="';
							if ((int)$currentmarker->overridemarkericon == 0)
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype);
							}
							else
							{
									$scripttext .= $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype);
							}
							$scripttext .= '.png" alt="" />';
							$scripttext .= '</td>';
							$scripttext .= '<td class="zhbdm-7-tdtitle-icon-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '<tr>';
							$scripttext .= '<td class="zhbdm-7-tddesc-icon-'.$markerlistcssstyle.'">';
							$scripttext .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml));
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '</tbody>';
							$scripttext .= '</table>';
							$scripttext .= ' \';'."\n";
						}
						else if ((int)$markerlistcontent == 11) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-11-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-11-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-11-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" />\'+'."\n";
							$scripttext .= ' \'</div></div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 12) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-12-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-12-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-12-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" /></div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-12-liwd-image-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-12-lid-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 16) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-16-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-16-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-16-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" /></div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-16-liwd-image-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-16-lid-image-'.$markerlistcssstyle.'">'.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 13) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-13-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-13-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-13-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'\'+'."\n";
							$scripttext .= ' \'</div></div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 14) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-14-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-14-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-14-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-14-liwd-image-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-14-lid-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 17) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDWrp'. $currentmarker->id.'" class="zhbdm-17-liw-image-'.$markerlistcssstyle.'">\'+'."\n";
							$scripttext .= ' \'<div id="markerDImage'. $currentmarker->id.'" class="zhbdm-17-lii-image-'.$markerlistcssstyle.'"><img src="'.$currentmarker->hrefimagethumbnail.'" alt="" /></div>\'+'."\n";
							$scripttext .= ' \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-17-lit-image-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-17-liwd-image-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDesc'. $currentmarker->id.'" class="zhbdm-17-lid-image-'.$markerlistcssstyle.'">'.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'</div>\'+'."\n";
							$scripttext .= ' \'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 15) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'';									
							$scripttext .= '<table class="zhbdm-15-table-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<tbody>';
							$scripttext .= '<tr class="zhbdm-15-row-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<td rowspan=2 class="zhbdm-15-tdicon-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<img src="'.$currentmarker->hrefimagethumbnail.'" alt="" />';
							$scripttext .= '</td>';
							$scripttext .= '<td class="zhbdm-15-tdtitle-image-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '<tr>';
							$scripttext .= '<td class="zhbdm-15-tddesc-image-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '</tbody>';
							$scripttext .= '</table>';
							$scripttext .= ' \';'."\n";
						}
						else if ((int)$markerlistcontent == 18) 
						{
							$scripttext .= ' markerASel.innerHTML = ';
							$scripttext .= ' \'';									
							$scripttext .= '<table class="zhbdm-18-table-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<tbody>';
							$scripttext .= '<tr class="zhbdm-18-row-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<td rowspan=2 class="zhbdm-18-tdicon-image-'.$markerlistcssstyle.'">';
							$scripttext .= '<img src="'.$currentmarker->hrefimagethumbnail.'" alt="" />';
							$scripttext .= '</td>';
							$scripttext .= '<td class="zhbdm-18-tdtitle-image-'.$markerlistcssstyle.'">';
							$scripttext .= htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8');
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '<tr>';
							$scripttext .= '<td class="zhbdm-18-tddesc-image-'.$markerlistcssstyle.'">';
							$scripttext .= str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml));
							$scripttext .= '</td>';
							$scripttext .= '</tr>';
							$scripttext .= '</tbody>';
							$scripttext .= '</table>';
							$scripttext .= ' \';'."\n";
						}
						else
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASel'. $currentmarker->id.'" class="zhbdm-0-li-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
						}


	                    $scripttext .= comZhBaiduMapPlacemarksHelper::get_placemarklist_action_string($type, $currentArticleId, $currentmarker->id, $markerlistaction);

						$scripttext .= ' markerASelWrp.appendChild(markerASel);'."\n";
						$scripttext .= ' markerLIWrp.appendChild(markerASelWrp);'."\n";
						if ((int)$markerlistcontent == 1
						 || (int)$markerlistcontent == 5) 
						{
							$scripttext .= ' markerLIWrp.appendChild(markerDSel);'."\n";
						}
						else if ((int)$markerlistcontent == 3
							  || (int)$markerlistcontent == 6) 
						{
							$scripttext .= ' markerLIWrp.appendChild(markerDSel);'."\n";
						}
						else if ((int)$markerlistcontent == 12
							  || (int)$markerlistcontent == 16) 
						{
							$scripttext .= ' markerLIWrp.appendChild(markerDSel);'."\n";
						}
						else if ((int)$markerlistcontent == 14
							  || (int)$markerlistcontent == 17) 
						{
							$scripttext .= ' markerLIWrp.appendChild(markerDSel);'."\n";
						}
						
						
						$scripttext .= ' markerLI.appendChild(markerLIWrp);'."\n";
						$scripttext .= ' markerUL.appendChild(markerLI);'."\n";
				}
				else
				{
						$scripttext .= ' var markerLI = document.createElement(\'tr\');'."\n";
						$scripttext .= ' markerLI.id = "zhbdm_pmlist_item_'.$currentArticleId.'_'.$currentmarker->id.'";'."\n";
						$scripttext .= ' markerLI.className = "zhbdm-li-table-tr-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerLI_C1 = document.createElement(\'td\');'."\n";
						$scripttext .= ' markerLI_C1.className = "zhbdm-li-table-c1-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerASelWrp = document.createElement(\'div\');'."\n";
						$scripttext .= ' markerASelWrp.className = "zhbdm-li-table-a-wrp-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' var markerASel = document.createElement(\'a\');'."\n";
						$scripttext .= ' markerASel.className = "zhbdm-li-table-a-'.$markerlistcssstyle.'";'."\n";
						$scripttext .= ' markerASel.id = "zhbdm_pmlist_'.$currentArticleId.'_'.$currentmarker->id.'";'."\n";
						$scripttext .= ' markerASel.href = \'javascript:void(0);\';'."\n";
						if ((int)$markerlistcontent == 101) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASelTable'. $currentmarker->id.'" class="zhbdm-101-td-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 102) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASelTable'. $currentmarker->id.'" class="zhbdm-102-td1-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";

							$scripttext .= ' var markerLI_C2 = document.createElement(\'td\');'."\n";
							$scripttext .= ' markerLI_C2.className = "zhbdm-li-table-c2-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-li-table-desc2-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDescTable'. $currentmarker->id.'" class="zhbdm-102-td2-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->description), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";
						}
						else if ((int)$markerlistcontent == 103) 
						{
							$scripttext .= ' markerASel.innerHTML = \'<div id="markerASelTable'. $currentmarker->id.'" class="zhbdm-103-td1-'.$markerlistcssstyle.'">'.htmlspecialchars(str_replace('\\', '/', $currentmarker->title), ENT_QUOTES, 'UTF-8').'</div>\';'."\n";

							$scripttext .= ' var markerLI_C2 = document.createElement(\'td\');'."\n";
							$scripttext .= ' markerLI_C2.className = "zhbdm-li-table-c3-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' var markerDSel = document.createElement(\'div\');'."\n";
							$scripttext .= ' markerDSel.className = "zhbdm-li-table-desc3-'.$markerlistcssstyle.'";'."\n";
							$scripttext .= ' markerDSel.innerHTML = ';
							$scripttext .= ' \'<div id="markerDDescTable'. $currentmarker->id.'" class="zhbdm-103-td2-'.$markerlistcssstyle.'">'.str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->descriptionhtml)).'</div>\';'."\n";
						}
						
	                    $scripttext .= comZhBaiduMapPlacemarksHelper::get_placemarklist_action_string($type, $currentArticleId, $currentmarker->id, $markerlistaction);

						$scripttext .= ' markerASelWrp.appendChild(markerASel);'."\n";
						$scripttext .= ' markerLI_C1.appendChild(markerASelWrp);'."\n";
						if ((int)$markerlistcontent == 102
						 || (int)$markerlistcontent == 103) 
						{
							$scripttext .= ' markerLI_C2.appendChild(markerDSel);'."\n";
						}
						
						
						$scripttext .= ' markerLI.appendChild(markerLI_C1);'."\n";
						if ((int)$markerlistcontent == 102
						 || (int)$markerlistcontent == 103) 
						{
							$scripttext .= ' markerLI.appendChild(markerLI_C2);'."\n";
						}
						$scripttext .= ' markerUL.appendChild(markerLI);'."\n";
				}
				$scripttext .= '}'."\n";
			}
		}
		return $scripttext;
	}
	
        
     public static function get_placemark_content_update_string($usermarkersicon, $usercontact, $currentmarker, $imgpathIcons, $imgpathUtils, $directoryIcons, $newMarkerGroupList)
    {
			$scripttext ='';

			// contentString - User Placemark can Update - Begin
					// Change UserMarker - begin
						
						$scripttext .= 'var contentStringPart1'.$currentmarker->id.' = "" +' ."\n";
						$scripttext .= '\'<div id="contentUpdatePlacemark">\'+'."\n";
						//$scripttext .= '    \'<br />\'+' ."\n";
						//$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LNG' ).' \'+'.$currentLng.' + ' ."\n";
						//$scripttext .= '    \'<br />'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LAT' ).' \'+'.$currentLat.' + ' ."\n";
						//$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LNG' ).' \'+latlng'.$currentmarker->id.'.lng + ' ."\n";
						//$scripttext .= '    \'<br />'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_LAT' ).' \'+latlng'.$currentmarker->id.'.lat + ' ."\n";
						
						// Form Update
                                		$scripttext .= '\'<div id="bodyContentUpdatePlacemark"  class="updatePlacemarkBody">\'+'."\n";
						$scripttext .= '    \'<form id="updatePlacemarkForm'.$currentmarker->id.'" action="'.JURI::current().'" method="post">\'+'."\n";
						$scripttext .= '    \''.'<img src="'.$imgpathUtils.'published'.(int)$currentmarker->published.'.png" alt="" />  \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";

					// Begin Placemark Properties
					$scripttext .= '\'<div id="bodyUpdatePlacemarkDivA'.$currentmarker->id.'"  class="bodyUpdateProperties">\'+'."\n";
					$scripttext .= '\'<a id="bodyInsertPlacemarkA'.$currentmarker->id.'" href="javascript:showonlyone(\\\'Placemark\\\',\\\''.$currentmarker->id.'\\\');" ><img src="'.$imgpathUtils.'collapse.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_PROPERTIES' ).'</a>\'+'."\n";
					$scripttext .= '\'</div>\'+'."\n";
					$scripttext .= '\'<div id="bodyInsertPlacemark'.$currentmarker->id.'"  class="bodyUpdatePlacemarkProperties">\'+'."\n";
						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_NAME' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						$scripttext .= '    \'<input name="markername" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->title, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						//$scripttext .= '    \'<br />\'+' ."\n";
						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_DESCRIPTION' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						$scripttext .= '    \'<input name="markerdescription" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->description, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
						$scripttext .= '    \'<br />\';' ."\n";

						// icon type
						/*
						if(isset($usermarkersicon) && (int)$usermarkersicon == 1) 
						{
							$iconTypeJS = " onchange=\"javascript: ";
							$iconTypeJS .= " if (document.forms.updatePlacemarkForm".$currentmarker->id.".markerimage.options[selectedIndex].value!=\'\') ";
							$iconTypeJS .= " {document.markericonimage".$currentmarker->id.".src=\'".$imgpathIcons."\' + document.forms.updatePlacemarkForm".$currentmarker->id.".markerimage.options[selectedIndex].value.replace(/#/g,\'%23\') + \'.png\'}";
							$iconTypeJS .= " else ";
							$iconTypeJS .= " {document.markericonimage".$currentmarker->id.".src=\'\'}\"";
							
							$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_ICON_TYPE' ).' \'+' ."\n";
							$scripttext .= ' \'';
							$scripttext .= '<img name="markericonimage'.$currentmarker->id.'" src="'.$imgpathIcons .str_replace("#", "%23", $currentmarker->icontype).'.png" alt="" />';
							$scripttext .= '\'+' ."\n";
							$scripttext .= '    \'<br />\'+' ."\n";
							$scripttext .= ' \'';
							$scripttext .= str_replace('.png<', '<', 
												str_replace('.png"', '"', 
													str_replace('JOPTION_SELECT_IMAGE', JText::_('COM_ZHBAIDUMAP_MAP_USER_IMAGESELECT'),
														str_replace(array("\r", "\r\n", "\n"),'', JHTML::_('list.images',  'markerimage', $active = $currentmarker->icontype.'.png', $iconTypeJS, $directoryIcons, $extensions =  "png")))));
							$scripttext .= '\'+' ."\n";
							$scripttext .= '    \'<br />\'+' ."\n";		
						}
						else
						{
							$scripttext .= '    \'<input name="markerimage" type="hidden" value="default#" />\'+' ."\n";	
						}
						*/

						$scripttext .= 'var contentStringPart2'.$currentmarker->id.' = "" +' ."\n";						
						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BALOON' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";

						
						$scripttext .= '    \' <select name="markerbaloon" > \'+' ."\n";
						$scripttext .= '    \' <option value="1" ';
						if ($currentmarker->baloon == 1)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_DROP').'</option> \'+' ."\n";
						$scripttext .= '    \' <option value="2" ';
						if ($currentmarker->baloon == 2)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_BOUNCE').'</option> \'+' ."\n";
						$scripttext .= '    \' <option value="3" ';
						if ($currentmarker->baloon == 3)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_BALOON_SIMPLE').'</option> \'+' ."\n";
						$scripttext .= '    \' </select> \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";

						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_USER_MARKERCONTENT' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						
						$scripttext .= '    \' <select name="markermarkercontent" > \'+' ."\n";
						$scripttext .= '    \' <option value="0" ';
						if ($currentmarker->baloon == 0)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE_DESC').'</option> \'+' ."\n";
						$scripttext .= '    \' <option value="1" ';
						if ($currentmarker->baloon == 1)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_TITLE').'</option> \'+' ."\n";
						$scripttext .= '    \' <option value="2" ';
						if ($currentmarker->baloon == 2)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_DESCRIPTION').'</option> \'+' ."\n";
						$scripttext .= '    \' <option value="100" ';
						if ($currentmarker->baloon == 100)
						{
							$scripttext .= 'selected="selected"';
						}
						$scripttext .= '>'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_MARKERCONTENT_NONE').'</option> \'+' ."\n";
						$scripttext .= '    \' </select> \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						
						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_DETAIL_HREFIMAGE_LABEL' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						$scripttext .= '    \'<input name="markerhrefimage" type="text" maxlength="500" size="50" value="'. htmlspecialchars($currentmarker->hrefimage, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";

						$scripttext .= '    \'<br />\'+' ."\n";

					$scripttext .= '\'</div>\'+'."\n";
					// End Placemark Properties
									
					// Begin Placemark Group Properties
					$scripttext .= '\'<div id="bodyUpdatePlacemarkGrpDivA'.$currentmarker->id.'"  class="bodyUpdateProperties">\'+'."\n";
					$scripttext .= '\'<a id="bodyInsertPlacemarkGrpA'.$currentmarker->id.'" href="javascript:showonlyone(\\\'PlacemarkGroup\\\',\\\''.$currentmarker->id.'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_BASIC_GROUP_PROPERTIES' ).'</a>\'+'."\n";
					$scripttext .= '\'</div>\'+'."\n";
					$scripttext .= '\'<div id="bodyInsertPlacemarkGrp'.$currentmarker->id.'"  class="bodyUpdatePlacemarkGrpProperties">\'+'."\n";
						$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_GROUP' ).' \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";
						
						$scripttext .= '    \' <select name="markergroup" > \'+' ."\n";
						if ($currentmarker->markergroup == 0)
						{
							$scripttext .= '    \' <option value="" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_FILTER_PLACEMARK_GROUP').'</option> \'+' ."\n";
						}
						else
						{
							$scripttext .= '    \' <option value="">'.JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_FILTER_PLACEMARK_GROUP').'</option> \'+' ."\n";
						}
						foreach ($newMarkerGroupList as $key => $newGrp) 
						{
							if ($currentmarker->markergroup == $newGrp->value)
							{
								$scripttext .= '    \' <option value="'.$newGrp->value.'" selected="selected">'.$newGrp->text.'</option> \'+' ."\n";
							}
							else
							{
								$scripttext .= '    \' <option value="'.$newGrp->value.'">'.$newGrp->text.'</option> \'+' ."\n";
							}
						}
						$scripttext .= '    \' </select> \'+' ."\n";
						$scripttext .= '    \'<br />\'+' ."\n";


				$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CATEGORY' ).' \'+' ."\n";
				$scripttext .= '    \'<br />\'+' ."\n";
				$scripttext .= '    \' <select name="markercatid" > \'+' ."\n";
				$scripttext .= '    \' <option value="" selected="selected">'.JText::_( 'COM_ZHBAIDUMAP_MAP_FILTER_CATEGORY').'</option> \'+' ."\n";
				$scripttext .= '    \''.str_replace(array("\r", "\r\n", "\n"),'', 
									   JHtml::_('select.options', JHtml::_('category.options', 'com_zhbaidumap'), 'value', 'text', $currentmarker->catid)) .
									   '\'+' ."\n";
				$scripttext .= '    \' </select> \'+' ."\n";
				$scripttext .= '    \'<br />\'+' ."\n";

				$scripttext .= '    \'<br />\'+' ."\n";
				$scripttext .= '\'</div>\'+'."\n";
				// End Placemark Group Properties

				// Begin Contact Properties
				if (isset($usercontact) && (int)$usercontact == 1) 
				{

					$scripttext .= '\'<div id="bodyUpdateContactDivA'.$currentmarker->id.'"  class="bodyUpdateProperties">\'+'."\n";
					$scripttext .= '\'<a id="bodyInsertContactA'.$currentmarker->id.'" href="javascript:showonlyone(\\\'Contact\\\',\\\''.$currentmarker->id.'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PROPERTIES' ).'</a>\'+'."\n";
					$scripttext .= '\'</div>\'+'."\n";
					$scripttext .= '\'<div id="bodyInsertContact'.$currentmarker->id.'"  class="bodyUpdateContactProperties">\'+'."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_NAME' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactname" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_name, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_POSITION' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactposition" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_position, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_PHONE' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactphone" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_phone, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_MOBILE' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactmobile" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_mobile, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_FAX' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactfax" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_fax, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_EMAIL' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<input name="contactemail" type="text" maxlength="250" size="50" value="'. htmlspecialchars($currentmarker->contact_email, ENT_QUOTES, 'UTF-8').'" />\'+' ."\n";
					$scripttext .= '\'</div>\'+'."\n";
					// Contact Address
					$scripttext .= '\'<div id="bodyUpdateContactAdrDivA'.$currentmarker->id.'"  class="bodyUpdateProperties">\'+'."\n";
					$scripttext .= '\'<a id="bodyInsertContactAdrA'.$currentmarker->id.'" href="javascript:showonlyone(\\\'ContactAddress\\\',\\\''.$currentmarker->id.'\\\');" ><img src="'.$imgpathUtils.'expand.png">'.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS_PROPERTIES' ).'</a>\'+'."\n";
					$scripttext .= '\'</div>\'+'."\n";
					$scripttext .= '\'<div id="bodyInsertContactAdr'.$currentmarker->id.'"  class="bodyUpdateContactAdrProperties">\'+'."\n";
					$scripttext .= '    \''.JText::_( 'COM_ZHBAIDUMAP_MAP_USER_CONTACT_ADDRESS' ).' \'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<textarea name="contactaddress" cols="35" rows="4" >'. str_replace("\n\n", "'+'\\n'+'", str_replace(array("\r", "\r\n", "\n"), "\n",htmlspecialchars($currentmarker->contact_address, ENT_QUOTES, 'UTF-8'))).'</textarea>\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '    \'<br />\'+' ."\n";
					$scripttext .= '\'</div>\'+'."\n";
				}
				// End Contact Properties
		
				$scripttext .= '\'\';'."\n";

					// Change UserMarker - end
					// contentString - User Placemark can Update - End

			return $scripttext;
	}
        
        
        private static function add_tab_to_placemark_content($title, $content)
	{
		$returnText = '';
		
		if ($title != "")
		{
			$returnText .= '\'<h3>'.$title.'</h3>\'+'."\n";
			$returnText .= '\'<div>'.$content.'</div>\'+'."\n";
		}
		return $returnText;
	}
        
        
	private static function add_tab_to_placemark_content_div($title, $div_content)
	{
		$returnText = '';
		
		if ($title != "")
		{
			$returnText .= '\'<h3>'.$title.'</h3>\'+'."\n";
			$returnText .= $div_content."\n";
		}
		return $returnText;
	}
        
	public static function get_placemark_tabs_content_string(
						$currentArticleId, $currentmarker,
						$contentString,
						$imgpathIcons, $imgpathUtils, $directoryIcons, $lang)
	{

		$currentLanguage = JFactory::getLanguage();
		$currentLangTag = $currentLanguage->getTag();
		

		if (isset($lang) && $lang != "")
		{
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $lang, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $lang, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap' , $lang, true);	
		}
		else
		{
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE, $currentLangTag, true);	
			$currentLanguage->load('com_zhbaidumap', JPATH_COMPONENT, $currentLangTag, true);		
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap', $currentLangTag, true);		
		}
		
		$returnText = '';

		if ((int)$currentmarker->tab_info == 9)
		{
		}
		else
		{
			$returnText = '\'<div id="BDMapsPanelAccordion'.$currentArticleId.'">\'+'."\n";
		}

		
		// InfoBubble Create Tabs - Begin					
		if ((int)$currentmarker->tab_info == 1)
		{					
			$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content_div(str_replace("'", "\'", 
			str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))), $contentString .'+');
		}
		
		if ((int)$currentmarker->tab_info == 9)
		{	
				$returnText .= $contentString;
		}
		else
		{
			
			if ($currentmarker->tab1 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab1)));
			}
			if ($currentmarker->tab2 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab2)));
			}
			if ($currentmarker->tab3 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab3)));
			}
			if ($currentmarker->tab4 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab4)));
			}
			if ($currentmarker->tab5 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab5)));
			}
			if ($currentmarker->tab6 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab6)));
			}
			if ($currentmarker->tab7 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab7)));
			}
			if ($currentmarker->tab8 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab8)));
			}
			if ($currentmarker->tab9 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab9)));
			}
			if ($currentmarker->tab10 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab10)));
			}
			if ($currentmarker->tab11 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab11)));
			}
			if ($currentmarker->tab12 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab12)));
			}
			if ($currentmarker->tab13 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab13)));
			}
			if ($currentmarker->tab14 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab14)));
			}
			if ($currentmarker->tab15 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab15)));
			}
			if ($currentmarker->tab16 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab16)));
			}
			if ($currentmarker->tab17 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab17)));
			}
			if ($currentmarker->tab18 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab18)));
			}
			if ($currentmarker->tab19 != "")
			{
				$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content(str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19title)), str_replace("'", "\'", str_replace(array("\r", "\r\n", "\n"), '', $currentmarker->tab19)));
			}
		}
		
		
		if ((int)$currentmarker->tab_info == 2)
		{					
			$returnText .= comZhBaiduMapPlacemarksHelper::add_tab_to_placemark_content_div(str_replace("'", "\'", 
			str_replace(array("\r", "\r\n", "\n"), '', JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' ))), $contentString .'+');
		}

		if ((int)$currentmarker->tab_info == 9)
		{
		}
		else
		{
			$returnText .= '\'</div>\'';
		}
		
		
		
		// InfoBubble Create Tabs - End
		return $returnText;
	}
	
	public static function get_placemark_icon_definition(
						$imgpathIcons,
						$imgpath4size,
						$currentmarker)
	{
		$scripttext = '';
		
                                                        if ((int)$currentmarker->overridemarkericon == 0)
							{
								$imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarker->icontype).'.png';
								$imgimg4size = $imgpath4size.$currentmarker->icontype.'.png';

								list ($imgwidth, $imgheight) = getimagesize($imgimg4size);

								$scripttext .= 'marker'. $currentmarker->id.'.setIcon(new BMap.Icon("'.$imgimg.'", new BMap.Size('.$imgwidth.','.$imgheight.')';

								if (isset($currentmarker->iconofsetx) 
								 && isset($currentmarker->iconofsety) 
								// Write offset all time
								// && ((int)$currentmarker->iconofsetx !=0
								//  || (int)$currentmarker->iconofsety !=0)
								 )
								{
									$ofsX = (int)$currentmarker->iconofsetx + $imgwidth/2;
									$ofsY = (int)$currentmarker->iconofsety + $imgheight;
									$scripttext .= ', { anchor: new BMap.Size('.$ofsX.','.$ofsY.')}' ."\n";
								}
								$scripttext .= '));' ."\n";
							}	
							else
							{
								$imgimg = $imgpathIcons.str_replace("#", "%23", $currentmarker->groupicontype).'.png';
								$imgimg4size = $imgpath4size.$currentmarker->groupicontype.'.png';

								list ($imgwidth, $imgheight) = getimagesize($imgimg4size);

								$scripttext .= 'marker'. $currentmarker->id.'.setIcon(new BMap.Icon("'.$imgimg.'", new BMap.Size('.$imgwidth.','.$imgheight.')';
								if (isset($currentmarker->groupiconofsetx) 
								 && isset($currentmarker->groupiconofsety) 
								// Write offset all time
								// && ((int)$currentmarker->groupiconofsetx !=0
								//  || (int)$currentmarker->groupiconofsety !=0)
								 )
								{
									$ofsX = (int)$currentmarker->groupiconofsetx + $imgwidth/2;
									$ofsY = (int)$currentmarker->groupiconofsety + $imgheight;
									$scripttext .= ', { anchor: new BMap.Size('.$ofsX.','.$ofsY.')}' ."\n";
								}
								$scripttext .= '));' ."\n";

							}
							
	
		return $scripttext;
	}	
	
		        
}
