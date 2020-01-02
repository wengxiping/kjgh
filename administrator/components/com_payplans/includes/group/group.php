<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(PP_LIB . '/abstract.php');

class PPGroup extends PPAbstract
{
	public static function factory($id)
	{
		return new self($id);
	}

	public function reset($option = array())
	{
		$this->table->group_id = 0;
		$this->table->title = '';
		$this->table->published = 1;
		$this->table->parent = 0;
		$this->table->description = '';
		$this->table->ordering = 0;
		$this->table->visible = 1;
		$this->table->params = null;
		$this->_plans = array();
		$this->params = null;
		return $this;
	}

	/**
	 * @return PayplansPlan
	 * @param string $dummy is added just for removing warning with development mode(XiLib::getInstance is having 4 parameters)
	 */
	static public function getInstance($id=0, $type=null, $bindData=null, $dummy=null)
	{
		return parent::getInstance('group',$id, $type, $bindData);
	}

	/**
	 * Override parent's bind behavior
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function bind($data = array(), $ignore=array())
	{
		if (is_object($data)) {
			$data = (array) ($data);
		}

		parent::bind($data, $ignore=array());

		if(isset($data['plans'])){
			$this->_plans = $data['plans'];
		}

		return $this;
	}

	/**
	 * Save group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function save()
	{
		parent::save();

		$this->savePlans();

		return $this;
	}

	/**
	 * Retrieves the title of the group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns plans assigned to this group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPlans()
	{
		return PP::model('Plangroup')->getGroupPlans($this->getId());
	}


	/**
	 * Determines if the group is published
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPublished()
	{
		return $this->published;
	}

	/**
	 * Retrieve visible column
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getVisible()
	{
		return $this->visible;
	}

	/**
	 * Gets the description of the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getDescription($descriptionFormat = false)
	{
		return $this->description;
	}

	/**
	 * Gets the Teaser Text of the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getTeasertext()
	{
		return $this->getParams()->get('teasertext','');
	}

	/**
	 * Gets the Css class of the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getCssClasses()
	{
		return $this->getParams()->get('css_class','');
	}

	/**
	 * Gets the badge title of the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgeTitle()
	{
		return $this->getParams()->get('badgeTitle','');
	}

	/**
	 * Gets the Plan highlighter of the group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPlanHighlighter()
	{
		return $this->getParams()->get('planHighlighter','');
	}

	/**
	 * Gets the badge background color
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgeBackgroundColor()
	{
		return $this->getParams()->get('badgebackgroundcolor','');
	}

	/**
	 * Gets the badge position
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgePosition()
	{
		$mapping = array('top-left' => 'left', 'top-center' => 'center', 'top-right' => 'right');
		$position = $this->getParams()->get('badgePosition', '');

		// Legacy naming
		if (isset($mapping[$position])) {
			return $mapping[$position];
		}

		return $position;
	}

	/**
	 * Gets the badge visibility
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgeVisible()
	{
		return $this->getParams()->get('badgeVisible','');
	}

	/**
	 * Gets the badge title color
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgeTitleColor()
	{
		return $this->getParams()->get('badgeTitleColor','');
	}

	/**
	 * Get parent group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Determine if the group has badge
	 *
	 * @since	4.0.11
	 * @access	public
	 */
	public function hasBadge()
	{
		return $this->getBadgeVisible();
	}

	/**
	 * Save plans for this group
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function savePlans()
	{
		// Delete all plans assigned to this group
		$this->deletePlans();

		// Assigned new plans to this group
		$data['group_id'] = $this->getId();

		if (empty($this->_plans)) {
			return;
		}

		$model = PP::model('plangroup');

		foreach($this->_plans as $plan){
			$data['plan_id'] = $plan;
			$model->save($data);
		}

		return;
	}

	/**
	 * Remove assigned plans
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function deletePlans()
	{
		$model = PP::model('plangroup');

		// Delete all plans assigned to this group
		$model->deleteMany(array('group_id' => $this->getId()));
	}

	public function delete()
	{
		// We have to delete all assigned plans
		$this->deletePlans();

		parent::delete();
	}

}
