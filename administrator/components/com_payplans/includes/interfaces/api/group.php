<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();


/**
 * These functions are listed for Group object
 * @author Puneet Singhal
 *
 */
interface PayplansIfaceApiGroup
{
	/** 
	 * Gets the title of the group
	 * 
	 * @return string Title of the group
	 */
	public function getTitle();
	
	/**
	 * Gets the css classes which will be applied on the current group while displaying it at frontend
	 * 
	 * @return string  css class applied on the group
	 */
	public function getCssClasses();
	
	/**
	 * Gets the teaser-text of the group
	 * 
	 * @return string  Teaser-text of the group
	 */
	public function getTeasertext();
	
	/**
	 * Gets the description of the group
	 * 
	 * @return string  Description of the group
	 */
	public function getDescription();
	
	/**
	 * Gets the published status of the group
	 * 
	 * @return integer  1 when group is published
	 */
	public function getPublished();

	/**
	 * Gets the visibility status of the group
	 * 
	 * @return integer 1 when group is visible else 0
	 */
	public function getVisible();
	
	/**
	 * Gets the group identifier with which the currect group is attached as a child
	 * 
	 * @return integer Parent group identifier of the current group
	 */
	public function getParent();
}
