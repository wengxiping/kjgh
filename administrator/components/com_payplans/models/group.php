<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/model');

class PayplansModelGroup extends PayPlansModel
{
	public $filterMatchOpeartor = array(
									'title'		=> array('LIKE'),
									'parent'	=> array('='),
									'published'	=> array('='),
									'visible'	=> array('=')
								);

	public function __construct()
	{
		parent::__construct('group');
	}

	//XITODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	public function delete($pk=null)
	{
		if (!parent::delete($pk)) {
			$db = JFactory::getDBO();
			XiError::raiseError(500, $db->getErrorMsg());
		}

		// delete entry from plangroup table
		return PP::model('plangroup')->deleteMany(array('group_id' => $pk));
	}

	/**
	 * Initialize default states used by default
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$ordering = $this->getUserStateFromRequest('ordering', 'group_id');

		$this->setState('ordering', $ordering);
	}

	public function getItems()
	{
		$search = $this->getState('search');
		$state = $this->getState('published');
		$visible = $this->getState('visible');
		$parent = $this->getState('parent');

		$db = $this->db;

		$query = array();

		$query[] = "select a.*";
		$query[] = " from `#__payplans_group` as a";

		$wheres = array();

		if ($search) {
			$wheres[] = $db->nameQuote('a.title') . " like " . $db->Quote('%' . $search . '%');
		}

		if ($state != 'all' && $state != '') {
			$wheres[] = $db->nameQuote('a.published') . " = " . $db->Quote((int) $state);
		}

		if ($visible != 'all' && $visible != '' ) {
			$wheres[] = $db->nameQuote('a.visible') . " = " . $db->Quote((int) $visible);
		}

		if($parent){
			$wheres[] = $db->nameQuote('a.parent') . " = " . $db->Quote((int) $parent);
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		$ordering = $this->getState('ordering');
		$direction	= $this->getState('direction');

		if ($ordering) {
			$query .= " ORDER BY " . $ordering . " " . $direction;
		}

		$result	= $this->getData($query);

		return $result;

	}

	/**
	 * Retrieve groups based on given options
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getGroups($options = array())
	{
		$db = PP::db();

		$query = array();

		$query[] = "select a.*";
		$query[] = " from `#__payplans_group` as a";

		$wheres = array();

		if (isset($options['state']) && $options['state']) {
			$wheres[] = $db->nameQuote('a.published') . " = " . $db->Quote((int) $options['state']);
		}

		if (isset($options['visible']) && $options['visible']) {
			$wheres[] = $db->nameQuote('a.visible') . " = " . $db->Quote((int) $options['visible']);
		}

		if (isset($options['exclusion']) && $options['exclusion']){
			
			$exclusion = $options['exclusion'];

			if (is_array($exclusion)) {
				$exclusion = implode(',', $exclusion);
			}
			
			$wheres[] = $db->nameQuote('a.group_id') . ' NOT IN(' . $exclusion . ')';
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;

	}

	/**
	 * Saves the ordering of groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateOrdering($id, $order)
	{
		$db = PP::db();

		$query = "update `#__payplans_group` set ordering = " . $db->Quote($order);
		$query .= " where group_id = " . $db->Quote($id);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

}

class PayplansModelformGroup extends PayPlansModelform {}
