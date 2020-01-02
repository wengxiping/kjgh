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

/**
 * Access Control is centred around the resource concept.
 * Resource : Any enitity which will be accessed by someone
 *     e.g. Profile, Photo, Video, File, Listing, Article, Category
 * Accessor : The user who is trying to access the resource
 *     Mostly logged in user
 * Owner    : The resource was created by or bleongs to owner
 *     User who owns Profile, Photo, Video, File, Listing, Article, Category etc.
 *
 *
 */
interface PayplansIfaceAppAccess
{
	// Identify the resource and return id of it.
	// if no resource then return false
	public function getResource();

	// who is trying to access to the resource
	public function getResourceAccessor();

	// Who own this resource
	public function getResourceOwner();

	// how many resource is owned by accessor
	public function getResourceCount();

	// is accessor violating this rule
	public function isViolation();

	// user is trying to violate rule, lets stop
	public function handleViolation();
}
