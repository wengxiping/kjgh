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

use Joomla\Utilities\ArrayHelper;

// import Joomla controller library
jimport('joomla.application.component.controller');

require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/placemarks.php';
require_once JPATH_SITE . '/components/com_zhbaidumap/helpers/paths.php';

/**
 * Zh BaiduMap Component Controller
 */
class ZhBaiduMapController extends JControllerLegacy
{
	public function getPlacemarkDetails() {

		$id = JRequest::getVar('id') ;
		$usercontactattributes = JRequest::getVar('contactattrs');
		$usercontact = JRequest::getVar('usercontact');
		$useruser = JRequest::getVar('useruser');
		$service_DoDirection = JRequest::getVar('servicedirection');
		$imgpathIcons = JRequest::getVar('iconicon');
		$imgpathUtils = JRequest::getVar('iconutil');
		$directoryIcons = JRequest::getVar('icondir');
		$currentArticleId = JRequest::getVar('articleid');
		$placemarkrating = JRequest::getVar('placemarkrating');
		$placemarkTitleTag = JRequest::getVar('placemarktitletag');
		$showcreateinfo = JRequest::getVar('showcreateinfo');
		$panelinfowin = JRequest::getVar('panelinfowin');
		
		$lang = JRequest::getVar('language');

		
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

  
		// Create some addition filters - Begin
		$addWhereClause = '';
		$addWhereClause .= ' and h.id = '. (int)$id;
		
		if ((int)$usercontact == 1)
		{
			$query->select('h.*, '.
				' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
				' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
				' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage,'.
				' cn.suburb as contact_suburb, cn.state as contact_state, cn.country as contact_country, cn.postcode as contact_postcode '.
				'')
				->from('#__zhbaidumaps_markers as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->leftJoin('#__contact_details as cn ON h.contactid=cn.id')
				->where('1=1' . $addWhereClause)
				;
		}
		else
		{
			$query->select('h.*, '.
				' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
				' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety'.
				'')
				->from('#__zhbaidumaps_markers as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1'. $addWhereClause)
				;

		}
		
		$db->setQuery($query);        
		
		$marker = $db->loadObject();
		
		
		if (isset($marker))
		{
			$responseVar = array( 'id'=>(int)$id
								, 'dataexists'=>1
								, 'actionbyclick'=>$marker->actionbyclick
								, 'zoombyclick'=>$marker->zoombyclick
			//, 'usercontactattributes'=>$usercontactattributes
			//, 'usercontact'=>$usercontact
			//, 'useruser'=>$useruser
			//, 'service_DoDirection'=> $service_DoDirection
			//,'i'=>$imgpathIcons
			//,'u'=>$imgpathUtils
			//,'d'=>$directoryIcons
								);
			if ($marker->actionbyclick == 1)
			{
				$responseVar['titleplacemark'] = htmlspecialchars(str_replace('\\', '/', $marker->title), ENT_QUOTES, 'UTF-8');
				$responseVar['contentstring'] = comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
											$currentArticleId,
											$marker, $usercontact, $useruser,
											$usercontactattributes, $service_DoDirection,
											$imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo
                                                                                        ) . ';';
			}

			if ($marker->actionbyclick == 2 
				|| $marker->actionbyclick == 3)
			{
				$responseVar['hrefsite'] = $marker->hrefsite;
			}
			if ($marker->actionbyclick ==4)
			{
				if ((int)$panelinfowin == 1)
				{
					$responseVar['tab_info_title'] = JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' );
					$responseVar['contentstring'] = comZhBaiduMapPlacemarksHelper::get_placemark_tabs_content_string(
														$currentArticleId, $marker,
														comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
															$currentArticleId,
															$marker, $usercontact, $useruser,
															$usercontactattributes, $service_DoDirection,
															$imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo
                                                                                                                        ),
														$imgpathIcons, $imgpathUtils, $directoryIcons, $lang). ';';	
					
				}
				else
				{
					$responseVar['tab_info'] = $marker->tab_info;
					
					if ((int)$marker->tab_info != 0)
					{
						$responseVar['tab_info_title'] = JText::_( 'COM_ZHBAIDUMAP_INFOBUBBLE_TAB_INFO_TITLE' );
						$responseVar['contentstring'] = comZhBaiduMapPlacemarksHelper::get_placemark_content_string(
													$currentArticleId,
													$marker, $usercontact, $useruser,
													$usercontactattributes, $service_DoDirection,
													$imgpathIcons, $imgpathUtils, $directoryIcons, $placemarkrating, $lang, $placemarkTitleTag, $showcreateinfo,
                                                                                                        $gobaidu, $gobaidu_text). ';';
					}
					$responseVar['infobubblestyle'] = comZhBaiduMapPlacemarksHelper::get_placemark_infobubble_style_string($marker, '');
					$responseVar['tab1'] = $marker->tab1;
					$responseVar['tab2'] = $marker->tab2;
					$responseVar['tab3'] = $marker->tab3;
					$responseVar['tab4'] = $marker->tab4;
					$responseVar['tab5'] = $marker->tab5;
					$responseVar['tab6'] = $marker->tab6;
					$responseVar['tab7'] = $marker->tab7;
					$responseVar['tab8'] = $marker->tab8;
					$responseVar['tab9'] = $marker->tab9;
					$responseVar['tab10'] = $marker->tab10;
					$responseVar['tab11'] = $marker->tab11;
					$responseVar['tab12'] = $marker->tab12;
					$responseVar['tab13'] = $marker->tab13;
					$responseVar['tab14'] = $marker->tab14;
					$responseVar['tab15'] = $marker->tab15;
					$responseVar['tab16'] = $marker->tab16;
					$responseVar['tab17'] = $marker->tab17;
					$responseVar['tab18'] = $marker->tab18;
					$responseVar['tab19'] = $marker->tab19;
					$responseVar['tab1title'] = $marker->tab1title;
					$responseVar['tab2title'] = $marker->tab2title;
					$responseVar['tab3title'] = $marker->tab3title;
					$responseVar['tab4title'] = $marker->tab4title;
					$responseVar['tab5title'] = $marker->tab5title;
					$responseVar['tab6title'] = $marker->tab6title;
					$responseVar['tab7title'] = $marker->tab7title;
					$responseVar['tab8title'] = $marker->tab8title;
					$responseVar['tab9title'] = $marker->tab9title;
					$responseVar['tab10title'] = $marker->tab10title;
					$responseVar['tab11title'] = $marker->tab11title;
					$responseVar['tab12title'] = $marker->tab12title;
					$responseVar['tab13title'] = $marker->tab13title;
					$responseVar['tab14title'] = $marker->tab14title;
					$responseVar['tab15title'] = $marker->tab15title;
					$responseVar['tab16title'] = $marker->tab16title;
					$responseVar['tab17title'] = $marker->tab17title;
					$responseVar['tab18title'] = $marker->tab18title;
					$responseVar['tab19title'] = $marker->tab19title;					
				}

				
				
			}

			if ($marker->actionbyclick == 5)
			{
				$responseVar['streetviewinfowinw'] = $marker->streetviewinfowinw;
				$responseVar['streetviewinfowinh'] = $marker->streetviewinfowinh;
				$responseVar['streetviewinfowinmapsv'] = comZhBaiduMapPlacemarksHelper::get_StreetViewOptions($marker->streetviewstyleid);
			}


			
		}
		else
		{
			$responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
		}
		echo (json_encode($responseVar));
		

	}
	
	
	public function setPlacemarkRating() {

		$id = JRequest::getVar('id') ;
		$rating = JRequest::getVar('rating') ;
		$lang = JRequest::getVar('language') ;

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
			$currentLanguage->load('com_zhbaidumap', JPATH_SITE . '/components/com_zhbaidumap' , $currentLangTag, true);	
		}
	

		$currentUser = JFactory::getUser();

		$userIP = $_SERVER['REMOTE_ADDR'];
		$userHOST = ((isset($_SERVER['REMOTE_HOST']) && !empty($_SERVER['REMOTE_HOST'])) ? $_SERVER['REMOTE_HOST'] : gethostbyaddr($_SERVER['REMOTE_ADDR'])); 
		
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		if ($currentUser->id == 0)
		{
			$db->setQuery( 'SELECT 1 as done FROM `#__zhbaidumaps_marker_rates` '.
			' WHERE 1=1 '.
			' and `ip`='.$db->Quote($userIP).
			' and `hostname`='.$db->Quote($userHOST).
			' and `markerid`='.(int)$id
			);
		}
		else
		{
			$db->setQuery( 'SELECT 1 as done FROM `#__zhbaidumaps_marker_rates` '.
			' WHERE 1=1 '.
			' and `createdbyuser`='.$currentUser->id.
			' and `markerid`='.(int)$id
			);
		}

		
		$selectExist = $db->loadObject();
		
		if (!isset($selectExist)) 
		{
			// insert into rating table
			$newRow = new stdClass;
			$newRow->markerid = (int)$id;
			$newRow->rating_value = $rating;
			$newRow->rating_date = JFactory::getDate()->toSQL();
			$newRow->ip = $userIP;
			$newRow->hostname = $userHOST;
			$newRow->createdbyuser = $currentUser->id;

			$dml_result_insert = $db->insertObject( '#__zhbaidumaps_marker_rates', $newRow, 'id' );
			
			// get average rating
			if ($dml_result_insert)
			{
				$query = $db->getQuery(true);

				$db->setQuery( 'SELECT AVG(rating_value) as rating, COUNT(*) as cnt FROM `#__zhbaidumaps_marker_rates` '.
				'WHERE `markerid`='.(int)$id);
				
				$selectAVG = $db->loadObject();
				
				if (isset($selectAVG)) 
				{
					$rating_avg = $selectAVG->rating;
					$rating_cnt = $selectAVG->cnt;
					
					// update rating field
					$updateRow = new stdClass;
					$updateRow->id = (int)$id;
					$updateRow->rating_value = $rating_avg;
					$updateRow->rating_count = $rating_cnt;
					
					$dml_result_update = $db->updateObject( '#__zhbaidumaps_markers', $updateRow, 'id' );
					
					
					if ($dml_result_update)
					{
						$responseVar = array( 'id'=>(int)$id
											, 'dataexists'=>1
											, 'userrating'=>$rating
											, 'averagerating'=>$rating_avg
											, 'averagecount'=>$rating_cnt
											, 'IP'=>$userIP
											, 'HOST'=>$userHOST
											, 'errortext'=>JText::_('COM_ZHBAIDUMAP_MAP_RATING_THANKS') 
											);
						
					}
					else
					{
						$responseVar = array('id'=>$id
											,'dataexists'=>0
											,'errortext'=>JText::_('COM_ZHBAIDUMAP_MAP_RATING_UNABLE_UPDATE_AVERAGE')
											);
					}
				}
				else
				{
					$responseVar = array('id'=>$id
										,'dataexists'=>0
										,'errortext'=>JText::_('COM_ZHBAIDUMAP_MAP_RATING_UNABLE_GET_AVERAGE')
										);
				}
			}
			else
			{
				$responseVar = array('id'=>$id
									,'dataexists'=>0
									,'errortext'=>JText::_('COM_ZHBAIDUMAP_MAP_RATING_UNABLE_INSERT_RATE')
									);
			}
		}
		else
		{
			$responseVar = array('id'=>$id
								,'dataexists'=>0
								,'errortext'=>JText::_('COM_ZHBAIDUMAP_MAP_RATING_ALREADY_VOTED')
								);
		}
		
		echo (json_encode($responseVar));
		

	}
	
	
	public function getPlacemarkHoverText() {

		$id = JRequest::getVar('id') ;
		
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		// Create some addition filters - Begin
		$addWhereClause = '';
		$addWhereClause .= ' and h.id = '. (int)$id;
		
		$query->select('h.*, '.
			' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
			' bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished ')
			->from('#__zhbaidumaps_markers as h')
			->leftJoin('#__categories as c ON h.catid=c.id')
			->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
			->leftJoin('#__zhbaidumaps_infobubbles as bub ON h.tabid=bub.id')
			->where('1=1'. $addWhereClause)
			;

		
		$db->setQuery($query);        
		
		$marker = $db->loadObject();
		
		
		if (isset($marker))
		{
			if ($marker->hoverhtml != "")
			{
				$responseVar = array( 'id'=>(int)$id
								, 'dataexists'=>1
								);
				$responseVar['hoverstring'] = comZhBaiduMapPlacemarksHelper::get_placemark_hover_string(
										    	$marker);
			}
			else
			{
				$responseVar = array('id'=>$id
                                                    ,'dataexists'=>0
                                                    );
			}
		}
		else
		{
			$responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
		}
		echo (json_encode($responseVar));

	}

        public function getPathHoverText() {

		$id = JRequest::getVar('id') ;
		
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

		// Create some addition filters - Begin
		$addWhereClause = '';
		$addWhereClause .= ' and h.id = '. (int)$id;
		
		$query->select('h.*, '.
			' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster')
			->from('#__zhbaidumaps_paths as h')
			->leftJoin('#__categories as c ON h.catid=c.id')
			->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
			->where('1=1'. $addWhereClause)
			;

		
		$db->setQuery($query);        
		
		$path = $db->loadObject();
		
		
		if (isset($path))
		{
			if ($path->hoverhtml != "")
			{
				$responseVar = array( 'id'=>(int)$id
                                                    , 'dataexists'=>1
                                                    );
				$responseVar['hoverstring'] = comZhBaiduMapPathsHelper::get_path_hover_string(
										    	$path);
                                $responseVar['objecttype'] = $path->objecttype;                               
                                $responseVar['color'] = $path->color;
                                $responseVar['fillcolor'] = $path->fillcolor;
                                $responseVar['hover_color'] = $path->hover_color;
                                $responseVar['hover_fillcolor'] = $path->hover_fillcolor;
                                
			}
                        else if ($path->hover_color != "" || $path->hover_fillcolor != "")
                        {
 				$responseVar = array( 'id'=>(int)$id
                                                    , 'dataexists'=>1
                                                    );
				$responseVar['hoverstring'] = "";
                                $responseVar['objecttype'] = $path->objecttype;                               
                                $responseVar['color'] = $path->color;
                                $responseVar['fillcolor'] = $path->fillcolor;
                                $responseVar['hover_color'] = $path->hover_color;
                                $responseVar['hover_fillcolor'] = $path->hover_fillcolor;                           
                        }
			else
			{
				$responseVar = array('id'=>$id
                                                    ,'dataexists'=>0
                                                    );
			}
		}
		else
		{
			$responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
		}
		echo (json_encode($responseVar));

	}

	
	// 16.08.2013 ajax loading

	public function getAJAXPlacemarkList() {

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$x1 = JRequest::getVar('x1');
		$x2 = JRequest::getVar('x2');
		$y1 = JRequest::getVar('y1');
		$y2 = JRequest::getVar('y2');

		$placemarkloadtype = JRequest::getVar('placemarkloadtype');
		
		$mapid = JRequest::getVar('mapid');
		$placemarklistid = str_replace(';',',', JRequest::getVar('placemarklistid'));
		$explacemarklistid = str_replace(';',',', JRequest::getVar('explacemarklistid'));
		$grouplistid = str_replace(';',',', JRequest::getVar('grouplistid'));
		$categorylistid = str_replace(';',',', JRequest::getVar('categorylistid'));
		$mf = JRequest::getVar('usermarkersfilter');
		
		$id = $mapid;


		
		// Create some addition filters - Begin
		$addWhereClause = '';

			
			if ($placemarklistid == ""
			 && $grouplistid == ""
			 && $categorylistid == "")
			{
				$addWhereClause .= ' and h.mapid='.(int)$id;

				if ($explacemarklistid != "")
				{
					$tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        $tmp_expl_ids = implode(',', $tmp_expl_ids);
					
					if (strpos($tmp_expl_ids, ','))
					{
						$addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
					}
				}
				
			}
			else
			{
				if ($placemarklistid != "")
				{
					$tmp_pl_ids = explode(',', str_replace(';',',', $placemarklistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_pl_ids = ArrayHelper::toInteger($tmp_pl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_pl_ids);
                                        }
                                        $tmp_pl_ids = implode(',', $tmp_pl_ids);
					
					if (strpos($tmp_pl_ids, ','))
					{
						$addWhereClause .= ' and h.id IN ('.$tmp_pl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id = '.(int)$tmp_pl_ids;
					}
				}
				if ($explacemarklistid != "")
				{
					$tmp_expl_ids = explode(',', str_replace(';',',', $explacemarklistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        $tmp_expl_ids = implode(',', $tmp_expl_ids);
					
					if (strpos($tmp_expl_ids, ','))
					{
						$addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
					}
				}
				if ($grouplistid != "")
				{
					$tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_grp_ids);
                                        }
                                        $tmp_grp_ids = implode(',', $tmp_grp_ids);
					
					if (strpos($tmp_grp_ids, ','))
					{
						$addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
					}
				}
				if ($categorylistid != "")
				{
					$tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_cat_ids);
                                        }
                                        $tmp_cat_ids = implode(',', $tmp_cat_ids);
					
					if (strpos($tmp_cat_ids, ','))
					{
						$addWhereClause .= ' and h.catid IN ('.$tmp_cat_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.catid = '.(int)$tmp_cat_ids;
					}
				}
			}
			
			// You can not enter markers

			switch ((int)$mf)
			{
				case 0:
					$addWhereClause .= ' and h.published=1';
				break;
				case 1:
					$currentUser = JFactory::getUser();
					$addWhereClause .= ' and h.published=1';
					$addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
				break;
				case 2:
					$currentUser = JFactory::getUser();
					$currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
					$addWhereClause .= ' and h.published=1';
					$addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
				break;
				default:
					$addWhereClause .= ' and h.published=1';
				break;					
			}
			
			// Create some addition filters - End


			if ($placemarkloadtype == "2")
			{			
				$addWhereClause .= ' and h.longitude >= '.(int)$x1;
				$addWhereClause .= ' and h.longitude <= '.(int)$x2;
				$addWhereClause .= ' and h.latitude >= '.(int)$y1;
				$addWhereClause .= ' and h.latitude <= '.(int)$y2;
			}

			$query->select('h.id'
				//',g.published as publishedgroup '
				)
				->from('#__zhbaidumaps_markers as h')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1' . $addWhereClause)
			;

			$nullDate = $db->Quote($db->getNullDate());
			$nowDate = $db->Quote(JFactory::getDate()->toSQL());
			
			$query->where('(h.publish_up = ' . $nullDate . ' OR h.publish_up <= ' . $nowDate . ')');
			$query->where('(h.publish_down = ' . $nullDate . ' OR h.publish_down >= ' . $nowDate . ')');
			
            $db->setQuery($query);        
			
			// Markers
			if (!$markers = $db->loadObjectList()) 
			{
				$responseVar = array('cnt'=>0
									,'dataexists'=>0
									);
			}
			else
			{
				$responseVar = array( 'cnt'=>count($markers)
									, 'dataexists'=>1
									, 'markers'=> $markers 
									);
			}
			
		
		echo (json_encode($responseVar));
		

	}
	
	public function getAJAXPlacemarks() {

		
		$ajaxarray = JRequest::getVar('ajaxarray');
		$markerlistpos = JRequest::getVar('mapmarkerlistpos');
		$markerlistcontent = JRequest::getVar('mapmarkerlistcontent');
		$markerlistaction = JRequest::getVar('mapmarkerlistaction');
		$markerlistcssstyle = JRequest::getVar('mapmarkerlistcssstyle');
		$mapDivSuffix = JRequest::getVar('maparticleid');
		$imgpathIcons = JRequest::getVar('iconicon');
		
		if (count($ajaxarray) > 0)
		{
                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                        {
                            $ajaxarray = ArrayHelper::toInteger($ajaxarray);
                        }
                        else
                        {
                            JArrayHelper::toInteger($ajaxarray);
                        }
			$placemarklist = implode(",", $ajaxarray);
				
	
			$db = JFactory::getDBO();

            $query = $db->getQuery(true);

      
			// Create some addition filters - Begin
			$addWhereClause = '';

			$addWhereClause .= ' and h.id IN ('.$placemarklist.')';

			$query->select('h.*, '.
				' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
				' g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster ')
				->from('#__zhbaidumaps_markers as h')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1' . $addWhereClause)
				->order('h.title');
			
            $db->setQuery($query);        
			
			// Markers
			if (!$markers = $db->loadObjectList()) 
			{
				$responseVar = array('cnt'=>0
									,'dataexists'=>0
									);
			}
			else
			{
				
				if ((int)$markerlistpos != 0)
				{
					foreach ($markers as $key => &$marker) 	
					{
						$marker->placemarklistcontent = comZhBaiduMapPlacemarksHelper::get_placemarklist_string(
												1,
												$mapDivSuffix, 
												$marker, 
												$markerlistcssstyle,
												$markerlistpos,
												$markerlistcontent,
												$markerlistaction,
												$imgpathIcons);
					}					
				}
				

				
				$responseVar = array( 'cnt'=>count($markers)
									, 'dataexists'=>1
									, 'markers'=> $markers 
									// it doesn't need for production
									// , 'ajaxarray'=>$placemarklist
									);
			}
			
		}
		else
		{
			$responseVar = array('cnt'=>0
								,'dataexists'=>0
								);
		}

		echo (json_encode($responseVar));
		

	}
  
 	public function getPathDetails() {

		$id = JRequest::getVar('id') ;
		$service_DoDirection = JRequest::getVar('servicedirection');
		$imgpathIcons = JRequest::getVar('iconicon');
		$imgpathUtils = JRequest::getVar('iconutil');
		$directoryIcons = JRequest::getVar('icondir');
		$currentArticleId = JRequest::getVar('articleid');
		$placemarkTitleTag = JRequest::getVar('placemarktitletag');
		$panelinfowin = JRequest::getVar('panelinfowin');
                
		$lang = JRequest::getVar('language');

		
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);

  
		// Create some addition filters - Begin
		$addWhereClause = '';
		$addWhereClause .= ' and h.id = '. (int)$id;
		

			$query->select('h.*, '.
				' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
				' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety')
				->from('#__zhbaidumaps_paths as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1'. $addWhereClause)
				;
		
		$db->setQuery($query);        
		
		$path = $db->loadObject();
		
		
		if (isset($path))
		{
			$responseVar = array( 'id'=>(int)$id
                                            , 'dataexists'=>1
                                            , 'actionbyclick'=>$path->actionbyclick
			//,'i'=>$imgpathIcons
			//,'u'=>$imgpathUtils
			//,'d'=>$directoryIcons
								);
			if ($path->actionbyclick == 1)
			{
				$responseVar['titlepath'] = htmlspecialchars(str_replace('\\', '/', $path->title), ENT_QUOTES, 'UTF-8');
				$responseVar['contentstring'] = comZhBaiduMapPathsHelper::get_path_content_string(
											$currentArticleId,
											$path, 
											$imgpathIcons, $imgpathUtils, $directoryIcons, $lang, $placemarkTitleTag) . ';';
			}

			if ($path->actionbyclick == 2 
			|| $path->actionbyclick == 3)
			{
				$responseVar['hrefsite'] = $path->hrefsite;
			}

			
		}
		else
		{
			$responseVar = array('id'=>$id
                                            ,'dataexists'=>0
                                            );
		}
		echo (json_encode($responseVar));
		

	}

	public function getAJAXPathList() {

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);


		$mapid = JRequest::getVar('mapid');
		$pathlistid = str_replace(';',',', JRequest::getVar('pathlistid'));
		$expathlistid = str_replace(';',',', JRequest::getVar('expathlistid'));
		$grouplistid = str_replace(';',',', JRequest::getVar('grouplistid'));
		$categorylistid = str_replace(';',',', JRequest::getVar('categorylistid'));
		
		$id = $mapid;


		
		// Create some addition filters - Begin
		$addWhereClause = '';

			
			if ($pathlistid == ""
			 && $grouplistid == ""
			 && $categorylistid == "")
			{
				$addWhereClause .= ' and h.mapid='.(int)$id;

				if ($expathlistid != "")
				{
					$tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        $tmp_expl_ids = implode(',', $tmp_expl_ids);
					
					if (strpos($tmp_expl_ids, ','))
					{
						$addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
					}
				}
				
			}
			else
			{
				if ($pathlistid != "")
				{
					$tmp_pl_ids = explode(',', str_replace(';',',', $pathlistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_pl_ids = ArrayHelper::toInteger($tmp_pl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_pl_ids);
                                        }
                                        $tmp_pl_ids = implode(',', $tmp_pl_ids);
					
					if (strpos($tmp_pl_ids, ','))
					{
						$addWhereClause .= ' and h.id IN ('.$tmp_pl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id = '.(int)$tmp_pl_ids;
					}
				}
				if ($expathlistid != "")
				{
					$tmp_expl_ids = explode(',', str_replace(';',',', $expathlistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_expl_ids = ArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_expl_ids);
                                        }
                                        $tmp_expl_ids = implode(',', $tmp_expl_ids);
					
					if (strpos($tmp_expl_ids, ','))
					{
						$addWhereClause .= ' and h.id NOT IN ('.$tmp_expl_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.id != '.(int)$tmp_expl_ids;
					}
				}
				if ($grouplistid != "")
				{
					$tmp_grp_ids = explode(',', str_replace(';',',', $grouplistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_grp_ids = ArrayHelper::toInteger($tmp_grp_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_grp_ids);
                                        }
                                        $tmp_grp_ids = implode(',', $tmp_grp_ids);
					
					if (strpos($tmp_grp_ids, ','))
					{
						$addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
					}
				}
				if ($categorylistid != "")
				{
					$tmp_cat_ids = explode(',', str_replace(';',',', $categorylistid));                                       
                                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                                        {
                                            $tmp_cat_ids = ArrayHelper::toInteger($tmp_cat_ids);
                                        }
                                        else
                                        {
                                            JArrayHelper::toInteger($tmp_cat_ids);
                                        }
                                        $tmp_cat_ids = implode(',', $tmp_cat_ids);
					
					if (strpos($tmp_cat_ids, ','))
					{
						$addWhereClause .= ' and h.catid IN ('.$tmp_cat_ids.')';
					}
					else
					{
						$addWhereClause .= ' and h.catid = '.(int)$tmp_cat_ids;
					}
				}
			}
			

                        $addWhereClause .= ' and h.published=1';
				
			// Create some addition filters - End


			$query->select('h.id'
				//',g.published as publishedgroup '
				)
				->from('#__zhbaidumaps_paths as h')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1' . $addWhereClause)
			;

			//$nullDate = $db->Quote($db->getNullDate());
			//$nowDate = $db->Quote(JFactory::getDate()->toSQL());
			
			//$query->where('(h.publish_up = ' . $nullDate . ' OR h.publish_up <= ' . $nowDate . ')');
			//$query->where('(h.publish_down = ' . $nullDate . ' OR h.publish_down >= ' . $nowDate . ')');
			
                        $db->setQuery($query);        
			
			// Paths
			if (!$paths = $db->loadObjectList()) 
			{
				$responseVar = array('cnt'=>0
									,'dataexists'=>0
									);
			}
			else
			{
				$responseVar = array( 'cnt'=>count($paths)
                                                    , 'dataexists'=>1
                                                    , 'paths'=> $paths 
                                                    );
			}
			
		
		echo (json_encode($responseVar));
		

	}
	
	public function getAJAXPaths() {

		
		$ajaxarray = JRequest::getVar('ajaxarray');
		$mapDivSuffix = JRequest::getVar('maparticleid');
		
		if (count($ajaxarray) > 0)
		{
                        if(version_compare(JVERSION, '3.5.0', 'ge'))
                        {
                            $ajaxarray = ArrayHelper::toInteger($ajaxarray);
                        }
                        else
                        {
                            JArrayHelper::toInteger($ajaxarray);
                        }			
			$pathlist = implode(",", $ajaxarray);
				
	
			$db = JFactory::getDBO();

                        $query = $db->getQuery(true);

      
			// Create some addition filters - Begin
			$addWhereClause = '';

			$addWhereClause .= ' and h.id IN ('.$pathlist.')';

			$query->select('h.*, '.
				' g.published as publishedgroup ')
				->from('#__zhbaidumaps_paths as h')
				->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
				->where('1=1' . $addWhereClause)
				->order('h.title');
			
                        $db->setQuery($query);        
			
			// Paths
			if (!$paths = $db->loadObjectList()) 
			{
				$responseVar = array('cnt'=>0
                                                    ,'dataexists'=>0
                                                    );
			}
			else
			{
				/*
				if ((int)$markerlistpos != 0)
				{
					foreach ($markers as $key => &$marker) 	
					{
						$marker->placemarklistcontent = comZhBaiduMapPlacemarksHelper::get_placemarklist_string(
												1,
												$mapDivSuffix, 
												$marker, 
												$markerlistcssstyle,
												$markerlistpos,
												$markerlistcontent,
												$markerlistaction,
												$imgpathIcons);
					}					
				}
				
                                */
				
                                //
                                foreach ($paths as $key => $currentpath) 
                                {
                                    $current_path_path = '';
                                    $current_path_path = str_replace(array("\r", "\r\n", "\n"), '', $currentpath->path);
                                    $currentpath->path = $current_path_path;
                                    
                                    if ($currentpath->imgbounds != "")
                                    {
                                        $current_path_imgbounds = '';
                                        $current_path_imgbounds = str_replace(',',';',$currentpath->imgbounds);
                                        $currentpath->imgbounds = $current_path_imgbounds;
                                    }
                                }
                                
				$responseVar = array( 'cnt'=>count($paths)
									, 'dataexists'=>1
									, 'paths'=> $paths 
									// it doesn't need for production
									// , 'ajaxarray'=>$placemarklist
									);
			}
			
		}
		else
		{
			$responseVar = array('cnt'=>0
                                            ,'dataexists'=>0
                                            );
		}

		echo (json_encode($responseVar));
		

	}    
}
