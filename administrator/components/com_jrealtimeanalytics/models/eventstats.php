<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Sources model responsibilities
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
interface IEventstatsModel {
	/**
	 * Return a list of subrecords for event details report
	 * 
	 * @access public
	 * @param int
	 * @return array
	 */
	public function getEventDetails($eventID);
}

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelEventstats extends JRealtimeModel implements IEventstatsModel {
	/**
	 * Records query result set
	 *
	 * @access private
	 * @var Object[]
	 */
	private $records;
	
	/**
	 * Build list entities query
	 *
	 * @access protected
	 * @return string
	 */
	protected function buildListQuery() {
		// WHERE
		$where = array ();
		$order = array();
		$whereString = null;
		$orderString = null;
		// STATE FILTER
		if ($filter_state = $this->state->get ( 'state' )) {
			if ($filter_state == 'P') {
				$where [] = 's.published = 1';
			} else if ($filter_state == 'U') {
				$where [] = 's.published = 0';
			}
		}
		
		// TYPE FILTER
		if ($this->state->get ( 'type' )) {
			$where [] = "s.type = " . $this->_db->quote ( $this->state->get ( 'type' ) );
		}
		
		// CATID FILTER
		if ($this->state->get ( 'catid' )) {
			$where [] = "s.catid = " . $this->_db->quote ( $this->state->get ( 'catid' ) );
		}
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "s.name LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%");
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if($this->state->get ( 'order' ) == 's.ordering' ) {
			$order[] = 'cat.title ';
		}
		
		if ($this->state->get ( 'order' )) {
			$order[] = $this->state->get ( 'order' );
		}
		
		if(count($order)) {
			$orderString = "\n ORDER BY " . implode ( ", ", $order );
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= " " . $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*, u.name AS editor, cat.title AS cat_title," .
				 "\n COUNT(t.event_id) AS event_occurrences" . 
				 "\n FROM #__realtimeanalytics_eventstats AS s" . 
				 "\n LEFT JOIN #__realtimeanalytics_eventstats_track AS t" .
				 "\n ON t.event_id = s.id AND t.session_id IN (SELECT session_id_person FROM #__realtimeanalytics_serverstats)" .
				 "\n LEFT JOIN #__realtimeanalytics_categories AS cat" .
				 "\n ON cat.id = s.catid AND cat.id != 1" .
				 "\n LEFT JOIN #__users AS u" . 
				 "\n ON s.checked_out = u.id" . 
				 $whereString . 
				 "\n GROUP BY s.id" .
				 $orderString;
		return $query;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$filters ['state'] = JHtml::_ ( 'grid.state', $this->getState ( 'state' ) );
		
		$datasourceTypes = array ();
		$datasourceTypes [] = JHtml::_ ( 'select.option', null, JText::_ ( 'COM_JREALTIME' ) );
		$datasourceTypes [] = JHtml::_ ( 'select.option', 'user', JText::_ ( 'COM_JREALTIME' ) );
		$datasourceTypes [] = JHtml::_ ( 'select.option', 'menu', JText::_ ( 'COM_JREALTIME' ) );
		$datasourceTypes [] = JHtml::_ ( 'select.option', 'content', JText::_ ( 'COM_JREALTIME' ) );
		$filters ['type'] = JHtml::_ ( 'select.genericlist', $datasourceTypes, 'filter_type', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState ( 'type' ) );
		
		$categories = JRealtimeHtmlCategories::getOptions(true);
		$filters['categories'] = JHtml::_('select.genericlist', $categories, 'filter_catid', 'class="inputbox" onchange=document.adminForm.submit();', 'value', 'text', $this->getState ( 'catid' ));
		
		return $filters;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record        	
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array ();
		// Grid states
		$lists ['published'] = JHtml::_ ( 'select.booleanlist', 'published', null, $record->published );
		
		// Goal states
		$lists ['hasgoal'] = JHtml::_ ( 'select.booleanlist', 'hasgoal', null, $record->hasgoal );
		
		// Notification states
		$notificationTypes [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'JNO' ) );
		$notificationTypes [] = JHtml::_ ( 'select.option', '1', JText::_ ( 'COM_JREALTIME_EVENT_INCREMENT' ) );
		$notificationTypes [] = JHtml::_ ( 'select.option', '2', JText::_ ( 'COM_JREALTIME_EVENT_GOAL_REACHED' ) );
		$lists ['hasgoalnotification'] = JHtml::_ ( 'select.radiolist', $notificationTypes, 'goal_notification', '', 'value', 'text', $record->goal_notification );
		
		$eventTypes = array ();
		$eventTypes [] = JHtml::_ ( 'select.option', null, JText::_ ( 'COM_JREALTIME_SELECT_EVENT_TYPE' ) );
		$eventTypes [] = JHtml::_ ( 'select.option', 'viewurl', JText::_ ( 'COM_JREALTIME_VIEWURL' ) );
		$eventTypes [] = JHtml::_ ( 'select.option', 'minpages', JText::_ ( 'COM_JREALTIME_MINPAGES' ) );
		$eventTypes [] = JHtml::_ ( 'select.option', 'mintime', JText::_ ( 'COM_JREALTIME_MINTIME' ) );
		$eventTypes [] = JHtml::_ ( 'select.option', 'mintimeonpage', JText::_ ( 'COM_JREALTIME_MINTIMEONPAGE' ) );
		$lists ['type'] = JHtml::_ ( 'select.genericlist', $eventTypes, 'type', 'data-validation="required"', 'value', 'text', $record->type );

		$menuOptions = JRealtimeHtmlMenu::getOptions();
		$lists['menupage']	= JHtml::_('select.genericlist', $menuOptions, 'params[menupage]', 'class="inputbox" data-switcher="1"', 'value', 'text', $record->params->get('menupage', null), 'menupage' );
		
		$trackMode = array(
				JHtml::_('select.option',  'menupage', JText::_( 'COM_JREALTIME_MENU_PAGE_TRACK' ) ),
				JHtml::_('select.option',  'urlpage', JText::_( 'COM_JREALTIME_URL_PAGE_TRACK' ) )
		);
		$lists ['viewtype'] = JHtml::_('select.radiolist', $trackMode, 'params[viewtype]', '', 'value', 'text', $record->params->get('viewtype', 'menupage'), 'params_viewtype_');
		
		$categories = JRealtimeHtmlCategories::getOptions();
		$lists['categories'] = JHtml::_('select.genericlist', $categories, 'catid', 'class="inputbox"', 'value', 'text', $record->catid);
		
		return $lists;
	}
	
	/**
	 * Return a list of subrecords for event details report
	 *
	 * @access public
	 * @param int
	 * @return array
	 */
	public function getEventDetails($eventID) {
		$query = "SELECT track.*, stats.customer_name, stats.geolocation, stats.ip, stats.browser, stats.os" .
				 "\n FROM " . $this->_db->quoteName('#__realtimeanalytics_eventstats_track') . " AS track" .
				 "\n INNER JOIN " . $this->_db->quoteName( '#__realtimeanalytics_serverstats') . " AS stats" .
				 "\n ON track.session_id = stats.session_id_person" .
				 "\n WHERE " . $this->_db->quoteName('event_id') . " = " . (int)($eventID) .
				 "\n GROUP BY " . $this->_db->quoteName('event_id') . "," .
								  $this->_db->quoteName('session_id') . "," .
								  $this->_db->quoteName('eventdate') .
				 "\n ORDER BY " . $this->_db->quoteName('eventdate') . " DESC";
		
		$this->_db->setQuery ( $query );
		try {
			$result = $this->_db->loadObjectList ();
			if ($this->_db->getErrorNum ()) {
				throw new JRealtimeException ( JText::sprintf ( 'COM_JREALTIME_ERROR_RECORDS', $this->_db->getErrorMsg () ), 'error' );
			}
		} catch ( JRealtimeException $e ) {
			$this->app->enqueueMessage ( $e->getMessage (), $e->getErrorLevel () );
			$result = array ();
		} catch ( Exception $e ) {
			$jRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
			$this->app->enqueueMessage ( $jRealtimeException->getMessage (), $jRealtimeException->getErrorLevel () );
			$result = array ();
		}
		return $result;
	}
}