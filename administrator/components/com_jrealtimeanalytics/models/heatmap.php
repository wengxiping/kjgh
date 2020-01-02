<?php
// namespace administrator\components\com_jrealtimeanalytics\models;
/**
 *
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 2.4
 */
class JRealtimeModelHeatmap extends JRealtimeModel {
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
		
		// PERIOD FILTER
		$where [] = $this->_db->quoteName('c.record_date') . " >= " . $this->_db->quote($this->getState('fromPeriod'));
		$where [] = $this->_db->quoteName('c.record_date') . " <= " . $this->_db->quote($this->getState('toPeriod'));
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "s.pageurl LIKE '%" . $this->state->get ( 'searchword' ) . "%'";
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
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
		
		$query = "SELECT count(c.id) AS numclicks, s.id, s.pageurl" .
				 "\n FROM #__realtimeanalytics_heatmap AS s" . 
				 "\n INNER JOIN #__realtimeanalytics_heatmap_clicks AS c" .
				 "\n ON s.id = c.heatmap_id" .
				 $whereString . 
				 "\n GROUP BY s.pageurl" .
				 $orderString;
		return $query;
	}
	
	/**
	 * Return select lists used as filter for editEntity
	 *
	 * @access public
	 * @param Object $record
	 * @return array
	 */
	public function getLists($record = null) {
		$lists = array();
	
		// Supported graph theme
		$themes = array();
		$themes[] = JHtml::_('select.option', 'Universal', 'Universal');
		$themes[] = JHtml::_('select.option', 'Aqua', 'Aqua');
		$themes[] = JHtml::_('select.option', 'Orange', 'Orange');
		$themes[] = JHtml::_('select.option', 'Pastel', 'Pastel');
		$themes[] = JHtml::_('select.option', 'Rose', 'Rose');
		$themes[] = JHtml::_('select.option', 'Softy', 'Softy');
		$themes[] = JHtml::_('select.option', 'Vivid', 'Vivid');
	
		$lists ['graphTheme'] = JHtml::_ ( 'select.genericlist', $themes, 'graphtheme', 'onchange="Joomla.submitform();"', 'value', 'text', $this->getState('graphTheme', 'Universal'));
	
		return $lists;
	}
	
	/**
	 * Return select lists used as filter for listEntities
	 *
	 * @access public
	 * @return array
	 */
	public function getFilters() {
		$filters = array();
		
		return $filters;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids        	
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		// Inner function declaration
		function dbEscaping(&$url, $key, $db) {
			$url = $db->quote($url);
		}
		
		try {
			// Check if valid array of ids passed
			if(!is_array($ids) || !count($ids)) {
				throw new JRealtimeException(JText::_('COM_JREALTIME_ERROR_DELETE_NO_RECORDS'), 'error');
			}
			
			// Transductor for the specific selector record ID to the pageurl globally applied
			$preQuery = "SELECT " . $this->_db->quoteName('pageurl') .
						"\n FROM " . $this->_db->quoteName('#__realtimeanalytics_heatmap') .
						"\n WHERE " . $this->_db->quoteName('id') . " IN (" . implode(',', $ids) . ")";
			$pageUrls = $this->_db->setQuery($preQuery)->loadColumn();
			if($this->_db->getErrorNum()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}
			array_walk($pageUrls, 'dbEscaping', $this->_db);
			
			$query = "DELETE " .
					$this->_db->quoteName('heatmap') . "," .
					$this->_db->quoteName('heatmapclicks') .
					"\n FROM #__realtimeanalytics_heatmap AS heatmap" .
					"\n LEFT JOIN #__realtimeanalytics_heatmap_clicks AS heatmapclicks" .
					"\n ON heatmap.id = heatmapclicks.heatmap_id" .
					"\n WHERE heatmap.pageurl IN (" . implode(',', $pageUrls) . ")";
			$this->_db->setQuery($query);
			if(!$this->_db->execute()) {
				throw new JRealtimeException($this->_db->getErrorMsg(), 'error');
			}
		} catch (JRealtimeException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jrealtimeException = new JRealtimeException($e->getMessage(), 'error');
			$this->setError($jrealtimeException);
			return false;
		}
		return true;
	}
}