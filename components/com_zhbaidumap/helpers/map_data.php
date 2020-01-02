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

use Joomla\Utilities\ArrayHelper;

abstract class comZhBaiduMapData
{

	public static function getMap($id) 
	{
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);

            $query->select('h.*, c.title as category')
                    ->from('#__zhbaidumaps_maps as h')
                    ->leftJoin('#__categories as c ON h.catid=c.id')
                    ->where('h.id=' . (int)$id);

            $db->setQuery($query);        
				
            $item = $db->loadObject();

            return $item;
	}

	public static function getMarkers($id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $usermarkers, $usermarkersfilter, $usercontact, $markerorder) 
	{		
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = '';

            if ($placemarklistid == ""
                    && $grouplistid == ""
                    && $categorylistid == ""
                    )
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


            // Create some addition filters - Begin

            if ($usermarkers == 0)
            {
                    // You can not enter markers

                    // You can see all published, and you can't enter markers

                    switch ((int)$usermarkersfilter)
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
            }
            else
            {
                    // You can enter markers

                    switch ((int)$usermarkersfilter)
                    {
                            case 0:
                                    $currentUser = JFactory::getUser();
                                    if ((int)$currentUser->id == 0)
                                    {
                                            $addWhereClause .= ' and h.published=1';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and (h.published=1 or h.createdbyuser='.(int)$currentUser->id .')';
                                    }
                            break;
                            case 1:
                                    $currentUser = JFactory::getUser();
                                    if ((int)$currentUser->id == 0)
                                    {
                                            $addWhereClause .= ' and h.published=1';
                                            $addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and h.createdbyuser='.(int)$currentUser->id;
                                    }
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
            }
            // Create some addition filters - End


            if ((int)$usercontact == 1)
            {
                    $query->select('h.*, '.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
                            ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety,'.
                            ' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage, '.
                            ' cn.suburb as contact_suburb, cn.state as contact_state, cn.country as contact_country, cn.postcode as contact_postcode')
                            ->from('#__zhbaidumaps_markers as h')
                            ->leftJoin('#__categories as c ON h.catid=c.id')
                            ->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
                            ->leftJoin('#__contact_details as cn ON h.contactid=cn.id')
                            ->where('1=1' . $addWhereClause);
            }
            else
            {
                    $query->select('h.*, '.
                            ' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
                            ' g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety')
                            ->from('#__zhbaidumaps_markers as h')
                            ->leftJoin('#__categories as c ON h.catid=c.id')
                            ->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
                            ->where('1=1' . $addWhereClause);

            }


                    if ((int)$markerorder == 0)
                    {
                            $query->order('h.title');
                    }
                    else if ((int)$markerorder == 1)
                    {
                            $query->order('c.title, h.ordering');
                    }
                    else if ((int)$markerorder == 2)
                    {
                            $query->order('c.title desc, h.ordering');
                    }
                    else if ((int)$markerorder == 10)
                    {
                            $query->order('h.userorder, h.title');
                    }
                    else if ((int)$markerorder == 20)
                    {
                            $query->order('g.title, h.title');
                    }
                    else if ((int)$markerorder == 21)
                    {
                            $query->order('g.title desc, h.title');
                    }
                    else if ((int)$markerorder == 22)
                    {
                            $query->order('g.userorder, g.title, h.title');
                    }
                    else if ((int)$markerorder == 23)
                    {
                            $query->order('g.userorder desc, g.title, h.title');
                    }
                    else if ((int)$markerorder == 30)
                    {
                            $query->order('h.createddate, h.title');
                    }
                    else if ((int)$markerorder == 31)
                    {
                            $query->order('h.createddate desc, h.title');
                    }
                    else 
                    {
                            $query->order('h.title');
                    }					

            $nullDate = $db->Quote($db->getNullDate());
            $nowDate = $db->Quote(JFactory::getDate()->toSQL());
            $query->where('(h.publish_up = ' . $nullDate . ' OR h.publish_up <= ' . $nowDate . ')');
            $query->where('(h.publish_down = ' . $nullDate . ' OR h.publish_down >= ' . $nowDate . ')');

            $db->setQuery($query);        
			
            // Markers
            $markers = $db->loadObjectList();


            return $markers;

	}
	
	public static function getRouters($id, $routelistid, $exroutelistid, $grouplistid, $categorylistid) 
	{
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = '';

            if ($routelistid == ""
                    && $grouplistid == ""
                    && $categorylistid == ""
                    )
            {

                    $addWhereClause .= ' and h.mapid='.(int)$id;

                    if ($exroutelistid != "")
                    {
                            $tmp_expl_ids = explode(',', str_replace(';',',', $exroutelistid));                                       
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
                    if ($routelistid != "")
                    {
                            $tmp_pl_ids = explode(',', str_replace(';',',', $routelistid));                                       
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
                    if ($exroutelistid != "")
                    {
                            $tmp_expl_ids = explode(',', str_replace(';',',', $exroutelistid));                                       
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
                            /* it is not in table yet
                            if (strpos($tmp_grp_ids, ','))
                            {
                                    $addWhereClause .= ' and h.markergroup IN ('.$tmp_grp_ids.')';
                            }
                            else
                            {
                                    $addWhereClause .= ' and h.markergroup = '.(int)$tmp_grp_ids;
                            }
                            */
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



            $query->select('h.*, c.title as category ')
                    ->from('#__zhbaidumaps_routers as h')
                    ->leftJoin('#__categories as c ON h.catid=c.id')
                    ->where('h.published=1'.$addWhereClause);

            $db->setQuery($query);        

            // Routers
            $routers = $db->loadObjectList();

            return $routers;
	}

	public static function getMarkerGroups($id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $markergrouporder) 
	{

            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
			
                $addWhereClause = "";

                if ($placemarklistid == ""
                        && $grouplistid == ""
                        && $categorylistid == "")
                {
                        $addWhereClause .= ' and m.mapid='.(int)$id;
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
                                        $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                        $addWhereClause .= ' and m.id IN ('.$tmp_pl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id = '.(int)$tmp_pl_ids;
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
                                        $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                        $addWhereClause .= ' and m.markergroup IN ('.$tmp_grp_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.markergroup = '.(int)$tmp_grp_ids;
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
                                        $addWhereClause .= ' and m.catid IN ('.$tmp_cat_ids.')';
                                }
                                else
                                {
                                        $addWhereClause .= ' and m.catid = '.(int)$tmp_cat_ids;
                                }
                        }
                }


                // Remove 'h.published=1 and m.published=1
                // because group may be disabled, but manual edit users placemark enable

                $nullDate = $db->Quote($db->getNullDate());
                $nowDate = $db->Quote(JFactory::getDate()->toSQL());
                $addWhereClause .= ' and (m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')';
                $addWhereClause .= ' and (m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')';

                $query->select('h.*, c.title as category ')
                        ->from('#__zhbaidumaps_markergroups as h')
                        ->leftJoin('#__categories as c ON h.catid=c.id')
                        ->where(' EXISTS (SELECT 1 FROM #__zhbaidumaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause.')')
                        ;

                if ((int)$markergrouporder == 0)
                {
                        $query->order('h.title');
                }
                else if ((int)$markergrouporder == 1)
                {
                        $query->order('c.title, h.ordering');
                }
                else if ((int)$markergrouporder == 10)
                {
                        $query->order('h.userorder, h.title');
                }
                else 
                {
                        $query->order('h.title');
                }

                $db->setQuery($query);        

                // MarkerGroups
                $markergroups = $db->loadObjectList();


		return $markergroups;
	}

	public static function getMarkerGroupsManage($id, 
                                                    $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, 
                                                    $markergrouporder, 
                                                    $markergroupctlmarker, 
                                                    $markergroupctlpath,
                                                    $pathlistid, $expathlistid, $grouplistpathid, $categorylistpathid) 
	{


            $db = JFactory::getDBO();

            $query = $db->getQuery(true);

            $addWhereClause = "";
            $addWhereClausePath = "";

            if ((int)$markergroupctlmarker == 1)
            {

                    if ($placemarklistid == ""
                     && $grouplistid == ""
                     && $categorylistid == "")
                    {
                            $addWhereClause .= ' and m.mapid='.(int)$id;

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
                                            $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                            $addWhereClause .= ' and m.id IN ('.$tmp_pl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id = '.(int)$tmp_pl_ids;
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
                                            $addWhereClause .= ' and m.id NOT IN ('.$tmp_expl_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.id != '.(int)$tmp_expl_ids;
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
                                            $addWhereClause .= ' and m.markergroup IN ('.$tmp_grp_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.markergroup = '.(int)$tmp_grp_ids;
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
                                            $addWhereClause .= ' and m.catid IN ('.$tmp_cat_ids.')';
                                    }
                                    else
                                    {
                                            $addWhereClause .= ' and m.catid = '.(int)$tmp_cat_ids;
                                    }
                            }
                    }
            }


            $nullDate = $db->Quote($db->getNullDate());
            $nowDate = $db->Quote(JFactory::getDate()->toSQL());

            // Remove 'h.published=1 and m.published=1
            // because group may be disabled, but manual edit users placemark enable

            if ((int)$markergroupctlmarker == 1)
            {
                    if ((int)$markergroupctlpath != 0)
                    {
                            $addWhereClause .= ' and (m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')';
                            $addWhereClause .= ' and (m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')';

                            $addWhereClausePath .= ' and (p.publish_up = ' . $nullDate . ' OR p.publish_up <= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.publish_down = ' . $nullDate . ' OR p.publish_down >= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.published = 1)';

                            // new parameters - start
                            //$addWhereClausePathPath .= ' and (p.mapid = '.(int)$id.')';

                            if ($pathlistid == ""
                                    && $grouplistpathid == ""
                                    && $categorylistpathid == ""
                                    )
                            {

                                    $addWhereClausePath .= ' and p.mapid='.(int)$id;

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
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
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
                                                    $addWhereClausePath .= ' and p.id IN ('.$tmp_pl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id = '.(int)$tmp_pl_ids;
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
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
                                            }
                                    }
                                    if ($grouplistpathid != "")
                                    {
                                            $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistpathid));                                       
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
                                                    $addWhereClausePath .= ' and p.markergroup IN ('.$tmp_grp_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup = '.(int)$tmp_grp_ids;
                                            }
                                    }
                                    if ($categorylistpathid != "")
                                    {
                                            $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistpathid));                                       
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
                                                    $addWhereClausePath .= ' and p.catid IN ('.$tmp_cat_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.catid = '.(int)$tmp_cat_ids;
                                            }
                                    }
                            }

                            // new parameters - end

                            if ((int)$markergroupctlpath == 1)
                            {
                                    $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 2)
                            {
                                    $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 3)
                            {
                                    $addWhereClausePath .= ' and ((p.path IS NOT NULL and p.path != \'\') or (p.kmllayer IS NOT NULL and p.kmllayer != \'\'))';
                            }
                            else 
                            {
                                    $addWhereClausePath .= ' and (1=2)';
                            }


                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhbaidumaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('( EXISTS (SELECT 1 FROM #__zhbaidumaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause. ')'.
                                    ' or EXISTS (SELECT 1 FROM #__zhbaidumaps_paths as p WHERE p.markergroup=h.id ' . $addWhereClausePath.'))')
                                    ;
                    }
                    else
                    {
                            $addWhereClause .= ' and (m.publish_up = ' . $nullDate . ' OR m.publish_up <= ' . $nowDate . ')';
                            $addWhereClause .= ' and (m.publish_down = ' . $nullDate . ' OR m.publish_down >= ' . $nowDate . ')';

                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhbaidumaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('EXISTS (SELECT 1 FROM #__zhbaidumaps_markers as m WHERE m.markergroup=h.id ' . $addWhereClause.')')
                                    ;
                    }
            }
            else
            {
                    if ((int)$markergroupctlpath != 0)
                    {
                            $addWhereClausePath .= ' and (p.publish_up = ' . $nullDate . ' OR p.publish_up <= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.publish_down = ' . $nullDate . ' OR p.publish_down >= ' . $nowDate . ')';
                            $addWhereClausePath .= ' and (p.published = 1)';

                            // new parameters - start
                            //$addWhereClausePathPath .= ' and (p.mapid = '.(int)$id.')';

                            if ($pathlistid == ""
                                    && $grouplistpathid == ""
                                    && $categorylistpathid == ""
                                    )
                            {

                                    $addWhereClausePath .= ' and p.mapid='.(int)$id;

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
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
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
                                                    $addWhereClausePath .= ' and p.id IN ('.$tmp_pl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id = '.(int)$tmp_pl_ids;
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
                                                    $addWhereClausePath .= ' and p.id NOT IN ('.$tmp_expl_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.id != '.(int)$tmp_expl_ids;
                                            }
                                    }
                                    if ($grouplistpathid != "")
                                    {
                                            $tmp_grp_ids = explode(',', str_replace(';',',', $grouplistpathid));                                       
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
                                                    $addWhereClausePath .= ' and p.markergroup IN ('.$tmp_grp_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.markergroup = '.(int)$tmp_grp_ids;
                                            }
                                    }
                                    if ($categorylistpathid != "")
                                    {
                                            $tmp_cat_ids = explode(',', str_replace(';',',', $categorylistpathid));                                       
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
                                                    $addWhereClausePath .= ' and p.catid IN ('.$tmp_cat_ids.')';
                                            }
                                            else
                                            {
                                                    $addWhereClausePath .= ' and p.catid = '.(int)$tmp_cat_ids;
                                            }
                                    }
                            }

                            // new parameters - end

                            if ((int)$markergroupctlpath == 1)
                            {
                                    $addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 2)
                            {
                                    $addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
                            }
                            else if ((int)$markergroupctlpath == 3)
                            {
                                    $addWhereClausePath .= ' and ((p.path IS NOT NULL and p.path != \'\') or (p.kmllayer IS NOT NULL and p.kmllayer != \'\'))';
                            }
                            else 
                            {
                                    $addWhereClausePath .= ' and (1=2)';
                            }

                            $query->select('h.*, c.title as category ')
                                    ->from('#__zhbaidumaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('EXISTS (SELECT 1 FROM #__zhbaidumaps_paths as p WHERE p.markergroup=h.id ' . $addWhereClausePath.')')
                                    ;
                    }
                    else
                    {
                            // return nothing
                            $query->select(' h.*, c.title as category ')
                                    ->from('#__zhbaidumaps_markergroups as h')
                                    ->leftJoin('#__categories as c ON h.catid=c.id')
                                    ->where('1=2')
                                    ;
                    }
            }

            if ((int)$markergrouporder == 0)
            {
                    $query->order('h.title');
            }
            else if ((int)$markergrouporder == 1)
            {
                    $query->order('c.title, h.ordering');
            }
            else if ((int)$markergrouporder == 10)
            {
                    $query->order('h.userorder, h.title');
            }
            else 
            {
                    $query->order('h.title');
            }

            $db->setQuery($query);        

            // MarkerGroups
            $markergroups_manage = $db->loadObjectList();


            return $markergroups_manage;
	}


	
	public static function getPaths($id, $pathlistid, $expathlistid, $grouplistid, $categorylistid) 
	{
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
			
            $addWhereClause = '';

            if ($pathlistid == ""
                    && $grouplistid == ""
                    && $categorylistid == ""
                    )
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
			
            $query->select('h.*, c.title as category ')
                ->from('#__zhbaidumaps_paths as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1'.$addWhereClause);
            $db->setQuery($query);        
			
			// Paths
			$paths = $db->loadObjectList();


            return $paths;
	}

	public static function getMapTypes() 
	{
            $db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query->select('h.*, c.title as category ')
                ->from('#__zhbaidumaps_maptypes as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1');
            $db->setQuery($query);        
			
            // Map Types
            $maptypes = $db->loadObjectList(); 


            return $maptypes;
	}

	


	public static function getMapAPIKey() 
	{
		// Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );
		
            $mapapikey4map = $comparams->get( 'map_map_key');

            return $mapapikey4map;
	}

	public static function getCompatibleMode() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );
		
            $mapcompatiblemode = $comparams->get( 'map_compatiblemode');

            return $mapcompatiblemode;
	}

	public static function getCompatibleModeRSF() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );
		
            $mapcompatiblemodersf = $comparams->get( 'map_compatiblemode_rsf');

            return $mapcompatiblemodersf;
	}
	

	public static function getHttpsProtocol() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );
            $httpsprotocol = $comparams->get( 'httpsprotocol');

            return $httpsprotocol;
	}
	
	public static function getLoadType() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );
            $loadtype = $comparams->get( 'loadtype');

            return $loadtype;
	}
	
	public static function getMapAPIVersion() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );

            $mapapiversion = $comparams->get( 'map_api_version');

            return $mapapiversion;
	}

	public static function getMapLicenseInfo() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );

            $licenseinfo = $comparams->get( 'licenseinfo');

            return $licenseinfo;
	}

	public static function getMapAPIType() 
	{
            // Get global params
            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );

            $mapapitype = $comparams->get( 'api_type');

            return $mapapitype;
	}
	
	
	public static function getPlacemarkTitleTag() 
	{
            // Get global params

            $app = JFactory::getApplication();
            $comparams = JComponentHelper::getParams( 'com_zhbaidumap' );

            $placemarktitletag = $comparams->get( 'placemarktitletag');

            return $placemarktitletag;
	}

	public static function getMarkerCoordinatesLatLngObject($markerId)
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
                                    return 'new BMap.Point('.$myMarker->latitude.', ' .$myMarker->longitude.')';
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
}
