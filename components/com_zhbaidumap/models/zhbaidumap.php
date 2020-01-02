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

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * Zh BaiduMap Model
 */
class ZhBaiduMapModelZhBaiduMap extends JModelItem
{
	/**
	 * @var object item
	 */
	protected $item;
	var $markers;
	var $markergroups;
	var $mgrgrouplist;
	var $routers;
	var $paths;
	var $maptypes;
        var $mapapikey4map;
        var $mapapiversion;
        var $placemarktitletag;

	var $mapcompatiblemode;
	var $mapcompatiblemodersf;
	var $httpsprotocol;
	var $loadtype;
	var $licenseinfo;
	
	var $centerplacemarkid;
	var $centerplacemarkaction;
	var $mapzoom;
	var $mapwidth;        
	var $mapheight;
	var $externalmarkerlink;
        var $usermarkersfilter;


	var $mapid;
	var $placemarklistid;
	var $explacemarklistid;
	var $grouplistid;
	var $categorylistid;
        
        var $pathlistid;
	var $expathlistid;
	var $pathgrouplistid;
	var $pathcategorylistid;
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState() 
	{
		$app = JFactory::getApplication();
		// Get the map id
		$id = JRequest::getInt('id');
		$this->setState('map.id', $id);

		$placemarklistid = JRequest::getVar('placemarklistid');
		$this->setState('map.placemarklistid', $placemarklistid);

		$explacemarklistid = JRequest::getVar('explacemarklistid');
		$this->setState('map.explacemarklistid', $explacemarklistid);
		
		$grouplistid = JRequest::getVar('grouplistid');
		$this->setState('map.grouplistid', $grouplistid);

		$categorylistid = JRequest::getVar('categorylistid');
		$this->setState('map.categorylistid', $categorylistid);

		$centerplacemarkid = JRequest::getVar('centerplacemarkid');
		$this->setState('map.centerplacemarkid', $centerplacemarkid);

		$centerplacemarkaction = JRequest::getVar('centerplacemarkaction');
		$this->setState('map.centerplacemarkaction', $centerplacemarkaction);

		$mapzoom = JRequest::getVar('mapzoom');
		$this->setState('map.mapzoom', $mapzoom);

                $mapwidth = JRequest::getVar('mapwidth');
		$this->setState('map.mapwidth', $mapwidth);
		$mapheight = JRequest::getVar('mapheight');
		$this->setState('map.mapheight', $mapheight);
                
		
		$externalmarkerlink = JRequest::getVar('externalmarkerlink');
		$this->setState('map.externalmarkerlink', $externalmarkerlink);
                
                $usermarkersfilter = JRequest::getVar('usermarkersfilter');
		$this->setState('map.usermarkersfilter', $usermarkersfilter);
                
                $pathlistid = JRequest::getVar('pathlistid');
		$this->setState('map.pathlistid', $pathlistid);

		$expathlistid = JRequest::getVar('expathlistid');
		$this->setState('map.expathlistid', $expathlistid);
		
		$pathgrouplistid = JRequest::getVar('pathgrouplistid');
		$this->setState('map.pathgrouplistid', $pathgrouplistid);

		$pathcategorylistid = JRequest::getVar('pathcategorylistid');
		$this->setState('map.pathcategorylistid', $pathcategorylistid);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		parent::populateState();
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'MapMap', $prefix = 'ZhBaiduMapTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Get the map
	 * @return object The map to be displayed to the user
	 */
	public function getItem() 
	{
		if (!isset($this->item)) 
		{
			$id = $this->getState('map.id');

			$db = JFactory::getDBO();

            $query = $db->getQuery(true);

			$query->select('h.*, c.title as category')
				->from('#__zhbaidumaps_maps as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->where('h.id=' . (int)$id)
				->order('h.title');

            $db->setQuery($query);        
				
			if (!$this->item = $db->loadObject()) 
			{
				$this->setError($db->getError());
			}
			else
			{
				// Load the JSON string
				$params = new JRegistry;
				$params->loadString($this->item->params);
				$this->item->params = $params;

				// Merge global params with item params
				$params = clone $this->getState('params');
				$params->merge($this->item->params);
				$this->item->params = $params;
			}

		}

		return $this->item;
	}

	public function getMarkers() 
	{
            if ((int)$this->item->useajaxobject == 0)
            {
                
                $db = JFactory::getDBO();

                $query = $db->getQuery(true);

		if (!isset($this->markers)) 
		{       
			$id = $this->getState('map.id');

      
			// Create some addition filters - Begin
			$addWhereClause = '';

			// Check if placemark list defined
			$placemarklistid = $this->getState('map.placemarklistid');
			$explacemarklistid = $this->getState('map.explacemarklistid');
			$grouplistid = $this->getState('map.grouplistid');
			$categorylistid = $this->getState('map.categorylistid');

                        if ($this->getState('map.usermarkersfilter') == "")
                        {
                                $usermarkersfilter = (int)$this->item->usermarkersfilter;
                        }
                        else
                        {
                                $usermarkersfilter = (int)$this->getState('map.usermarkersfilter');
                        }
                                
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
			
			if ((int)$this->item->usermarkers == 0)
			{
				// You can not enter markers

				// You can see all published, and you can't enter markers
				
				switch ($usermarkersfilter)
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
				
				switch ($usermarkersfilter)
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
							if ((int)$currentUser->id == 0)
							{
								$addWhereClause .= ' and h.published=1';
								$currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
								$addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
							}
							else
							{
								$currentUserGroups = implode(',', $currentUser->getAuthorisedViewLevels());
								$addWhereClause .= ' and h.access IN (' . $currentUserGroups . ')';
							}
                                        break;
                                        default:
						$addWhereClause .= ' and h.published=1';
					break;					
				}
			}
			// Create some addition filters - End


				
			if ((int)$this->item->usermarkers == 0
			 && (int)$this->item->useajax != 0)
			{
					$query->select('h.id, h.markergroup, h.published, h.title, h.latitude, h.longitude, h.addresstext, h.icontype, h.baloon, '.
                                                ' h.descriptionhtml, h.hrefimagethumbnail, h.includeinlist, '.
                                                ' h.labelcontent, h.labelstyle, h.labelanchorx, h.labelanchory, '.
						' h.userprotection, h.createdbyuser, h.markercontent, h.openbaloon, h.actionbyclick,  h.hoverhtml, h.rating_value, '.
						' h.ordering,h.userorder, h.iconofsetx, h.iconofsety, g.iconofsetx as groupiconofsetx, g.iconofsety as groupiconofsety, '.
						' g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster ')
						->from('#__zhbaidumaps_markers as h')
						->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
						->where('1=1' . $addWhereClause);
			}
			else
			{
				if ((int)$this->item->usercontact == 1)
				{
					$query->select('h.*, '.
						' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.activeincluster as activeincluster, '.
						' g.iconofsetx groupiconofsetx, g.iconofsety groupiconofsety,'.
						' cn.name as contact_name, cn.address as contact_address, cn.con_position as contact_position, cn.telephone as contact_phone, cn.mobile as contact_mobile, cn.fax as contact_fax, cn.email_to as contact_email, cn.webpage as contact_webpage,'.
						' cn.suburb as contact_suburb, cn.state as contact_state, cn.country as contact_country, cn.postcode as contact_postcode ')
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
						' g.iconofsetx groupiconofsetx, g.iconofsety groupiconofsety ')
						->from('#__zhbaidumaps_markers as h')
						->leftJoin('#__categories as c ON h.catid=c.id')
						->leftJoin('#__zhbaidumaps_markergroups as g ON h.markergroup=g.id')
						->where('1=1'. $addWhereClause);
				}
			}

                        if ((int)$this->item->markerorder == 0)
                        {
                                $query->order('h.title');
                        }
                        else if ((int)$this->item->markerorder == 1)
                        {
                                $query->order('c.title, h.ordering');
                        }
                        else if ((int)$this->item->markerorder == 2)
                        {
                                $query->order('c.title desc, h.ordering');
                        }
                        else if ((int)$this->item->markerorder == 10)
                        {
                                $query->order('h.userorder, h.title');
                        }
                        else if ((int)$this->item->markerorder == 20)
                        {
                                $query->order('g.title, h.title');
                        }
                        else if ((int)$this->item->markerorder == 21)
                        {
                                $query->order('g.title desc, h.title');
                        }
                        else if ((int)$this->item->markerorder == 22)
                        {
                                $query->order('g.userorder, g.title, h.title');
                        }
                        else if ((int)$this->item->markerorder == 23)
                        {
                                $query->order('g.userorder desc, g.title, h.title');
                        }
                        else if ((int)$this->item->markerorder == 30)
                        {
                                $query->order('h.createddate, h.title');
                        }
                        else if ((int)$this->item->markerorder == 31)
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
			if (!$this->markers = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}
			

		}

            }
		return $this->markers;
	}
	
	public function getRouters() 
	{
		if (!isset($this->routers)) 
		{       
			$id = $this->getState('map.id');

			$db = JFactory::getDBO();

            $query = $db->getQuery(true);

			$addWhereClause = '';
			$routelistid = '';//$this->getState('map.routelistid');
			$exroutelistid = '';//$this->getState('map.exroutelistid');
			$grouplistid = '';//$this->getState('map.routegrouplistid');
			$categorylistid = '';//$this->getState('map.routecategorylistid');
			
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
				->where('h.published=1' . $addWhereClause);

            $db->setQuery($query);        
				
			// Markers
			if (!$this->routers = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}

		}

		return $this->routers;
	}

        public function getMarkerGroups() 
	{

		if (!isset($this->markergroups)) 
		{       
			$id = $this->getState('map.id');

			$db = JFactory::getDBO();

            $query = $db->getQuery(true);

			$addWhereClause = "";
						
			$placemarklistid = $this->getState('map.placemarklistid');
			$explacemarklistid = $this->getState('map.explacemarklistid');
			$grouplistid = $this->getState('map.grouplistid');
			$categorylistid = $this->getState('map.categorylistid');

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


			if ((int)$this->item->markergrouporder == 0)
			{
				$query->order('h.title');
			}
			else if ((int)$this->item->markergrouporder == 1)
			{
				$query->order('c.title, h.ordering');
			}
			else if ((int)$this->item->markergrouporder == 10)
			{
				$query->order('h.userorder, h.title');
			}
			else 
			{
				$query->order('h.title');
			}
			                        
			$db->setQuery($query);        

			// MarkerGroups
			if (!$this->markergroups = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}

		}

		return $this->markergroups;
	}


	public function getMgrGroupsList() 
	{

		/* 19.02.2013 
		   for flexible support group management 
		   and have ability to set off placemarks from group managenent 
		   markergroups changed to mgrgrouplist
		   */
	
		if (!isset($this->mgrgrouplist)) 
		{       
			$id = $this->getState('map.id');

			$db = JFactory::getDBO();

                        $query = $db->getQuery(true);

			$addWhereClause = "";
			$addWhereClausePath = "";
						
			$placemarklistid = $this->getState('map.placemarklistid');
			$explacemarklistid = $this->getState('map.explacemarklistid');
			$grouplistid = $this->getState('map.grouplistid');
			$categorylistid = $this->getState('map.categorylistid');

			// 26.06.2015 - new parameters
			$pathlistid = $this->getState('map.pathlistid');
			$expathlistid = $this->getState('map.expathlistid');
			$grouplistpathid = $this->getState('map.pathgrouplistid');
			$categorylistpathid = $this->getState('map.pathcategorylistid');
                        
			if ((int)$this->item->markergroupctlmarker == 1)
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

			if ((int)$this->item->markergroupctlmarker == 1)
			{
				if ((int)$this->item->markergroupctlpath != 0)
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
					
					if ((int)$this->item->markergroupctlpath == 1)
					{
						$addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
					}
					else if ((int)$this->item->markergroupctlpath == 2)
					{
						$addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
					}
					else if ((int)$this->item->markergroupctlpath == 3)
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
				if ((int)$this->item->markergroupctlpath != 0)
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
					
					if ((int)$this->item->markergroupctlpath == 1)
					{
						$addWhereClausePath .= ' and (p.kmllayer IS NOT NULL and p.kmllayer != \'\')';
					}
					else if ((int)$this->item->markergroupctlpath == 2)
					{
						$addWhereClausePath .= ' and (p.path IS NOT NULL and p.path != \'\')';
					}
					else if ((int)$this->item->markergroupctlpath == 3)
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
			
			if ((int)$this->item->markergrouporder == 0)
			{
				$query->order('h.title');
			}
			else if ((int)$this->item->markergrouporder == 1)
			{
				$query->order('c.title, h.ordering');
			}
			else if ((int)$this->item->markergrouporder == 10)
			{
				$query->order('h.userorder, h.title');
			}
			else 
			{
				$query->order('h.title');
			}

			$db->setQuery($query);        

			// Group List Manager
			if (!$this->mgrgrouplist = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}

		}

		return $this->mgrgrouplist;
	}
	
	public function getPaths() 
	{
            if ((int)$this->item->useajaxobject == 0)
            {
		if (!isset($this->paths)) 
		{       
			$id = $this->getState('map.id');

			$db = JFactory::getDBO();

                        $query = $db->getQuery(true);
 
			$addWhereClause = '';
			$pathlistid = $this->getState('map.pathlistid');
			$expathlistid = $this->getState('map.expathlistid');
			$grouplistid = $this->getState('map.pathgrouplistid');
			$categorylistid = $this->getState('map.pathcategorylistid');
			
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
			if (!$this->paths = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}

		}

            }
            return $this->paths;
	}

	public function getMapTypes() 
	{
		if (!isset($this->maptypes)) 
		{       
			$db = JFactory::getDBO();

            $query = $db->getQuery(true);
            $query->select('h.*, c.title as category ')
                ->from('#__zhbaidumaps_maptypes as h')
				->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1');
            $db->setQuery($query);        
			
			// Map Types
			if (!$this->maptypes = $db->loadObjectList()) 
			{
				$this->setError($db->getError());
			}

		}

		return $this->maptypes;
	}

	
	
	public function getMapAPIVersion() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $mapapiversion = $params->get( 'map_api_version', '' );
	}
	
	public function getMapAPIKey() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $mapapikey4map = $params->get( 'map_map_key', '' );
	}

	public function getCompatibleMode() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $mapcompatiblemode = $params->get( 'map_compatiblemode', '' );
	}

	public function getCompatibleModeRSF() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $mapcompatiblemodersf = $params->get( 'map_compatiblemode_rsf', '' );
	}
	
	public function getHttpsProtocol() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $httpsprotocol = $params->get( 'httpsprotocol', '' );
	}
	
	public function getLicenseInfo() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $licenseinfo = $params->get( 'licenseinfo', '' );
	}
	
	public function getLoadType() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $loadtype = $params->get( 'loadtype', '' );
	}


	public function getCenterPlacemarkId() 
	{
		$centerplacemarkid = $this->getState('map.centerplacemarkid');
		return $centerplacemarkid;
	}
	
	public function getCenterPlacemarkAction() 
	{
		$centerplacemarkaction = $this->getState('map.centerplacemarkaction');
		return $centerplacemarkaction;
	}

	public function getMapZoom() 
	{
		$mapzoom = $this->getState('map.mapzoom');
		return $mapzoom;
	}

	public function getExternalMarkerLink() 
	{
		$externalmarkerlink = $this->getState('map.externalmarkerlink');
		return $externalmarkerlink;
	}

        public function getPlacemarkTitleTag() 
	{
		// Get global params
		$app = JFactory::getApplication();
		$params = $app->getParams();

		return $placemarktitletag = $params->get( 'placemarktitletag', '' );
	}
	
        public function getMapWidth() 
	{
		$mapwidth = $this->getState('map.mapwidth');
		return $mapwidth;
	}
	public function getMapHeight() 
	{
		$mapheight = $this->getState('map.mapheight');
		return $mapheight;
	}

	public function getMapID() 
	{
		$mapid = $this->getState('map.id');
		return $mapid;
	}
	
	public function getPlacemarkListID() 
	{
		$placemarklistid = str_replace(',',';', $this->getState('map.placemarklistid'));
		return $placemarklistid;
	}

	public function getExPlacemarkListID() 
	{
		$explacemarklistid = str_replace(',',';', $this->getState('map.explacemarklistid'));
		return $explacemarklistid;
	}

	public function getGroupListID() 
	{
		$grouplistid = str_replace(',',';', $this->getState('map.grouplistid'));
		return $grouplistid;
	}

	public function getCategoryListID() 
	{
		$categorylistid = str_replace(',',';', $this->getState('map.categorylistid'));
		return $categorylistid;
	}

	public function getPathListID() 
	{
		$pathlistid = str_replace(',',';', $this->getState('map.pathlistid'));
		return $pathlistid;
	}

	public function getExPathListID() 
	{
		$expathlistid = str_replace(',',';', $this->getState('map.expathlistid'));
		return $expathlistid;
	}

	public function getPathGroupListID() 
	{
		$pathgrouplistid = str_replace(',',';', $this->getState('map.pathgrouplistid'));
		return $pathgrouplistid;
	}

	public function getPathCategoryListID() 
	{
		$pathcategorylistid = str_replace(',',';', $this->getState('map.pathcategorylistid'));
		return $pathcategorylistid;
	}
	        
}
