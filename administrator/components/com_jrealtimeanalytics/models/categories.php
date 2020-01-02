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
 * Sources model concrete implementation <<testable_behavior>>
 *
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage models
 * @since 1.0
 */
class JRealtimeModelCategories extends JRealtimeModel {
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
		$whereString = null;
		$orderString = null;
		
		$where [] = "s.parent_id > 0";
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
		
		// TEXT FILTER
		if ($this->state->get ( 'searchword' )) {
			$where [] = "s.title LIKE " . $this->_db->quote("%" . $this->state->get ( 'searchword' ) . "%");
		}
		
		if (count ( $where )) {
			$whereString = "\n WHERE " . implode ( "\n AND ", $where );
		}
		
		// ORDERBY
		if ($this->state->get ( 'order' )) {
			$orderString = "\n ORDER BY " . $this->state->get ( 'order' ) . " ";
		}
		
		// ORDERDIR
		if ($this->state->get ( 'order_dir' )) {
			$orderString .= $this->state->get ( 'order_dir' );
		}
		
		$query = "SELECT s.*, u.name AS editor" . 
				 "\n FROM #__realtimeanalytics_categories AS s" . 
				 "\n LEFT JOIN #__users AS u" . 
				 "\n ON s.checked_out = u.id" . 
				 $whereString . 
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
		
		$categories = JRealtimeHtmlCategories::getOptions(false, $record->id);
		$lists['categories'] = JHtml::_('select.genericlist', $categories, 'parent_id', 'class="inputbox"', 'value', 'text', $record->parent_id);
		
		return $lists;
	}
	
	/**
	 * Delete entity
	 *
	 * @param array $ids
	 * @access public
	 * @return boolean
	 */
	public function deleteEntity($ids) {
		$table = $this->getTable ();
	
		// Ciclo su ogni entity da cancellare
		if (is_array ( $ids ) && count ( $ids )) {
			foreach ( $ids as $id ) {
				try {
					if (! $table->load ( $id )) {
						continue;
					}
					if (! $table->delete ( $id )) {
						throw new JRealtimeException ( $table->getError (), 'error' );
					}
					// Only if table supports ordering
					if (property_exists ( $table, 'ordering' )) {
						$table->reorder ();
					}
				} catch ( JRealtimeException $e ) {
					$this->setError ( $e );
					return false;
				} catch ( Exception $e ) {
					$JRealtimeException = new JRealtimeException ( $e->getMessage (), 'error' );
					$this->setError ( $JRealtimeException );
					return false;
				}
			}
		}
	
		return true;
	}
}