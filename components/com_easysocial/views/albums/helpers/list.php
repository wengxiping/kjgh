<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewAlbumsListHelper extends EasySocial
{
	/**
	 * Determine if the view is All type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isViewAll()
	{
		// Check if the current request is made for the current logged in user or another user.
		$uid = $this->getUid();
		$type = $this->getType();

		// When someone tries to view all albums
		if (is_null($uid) && is_null($type)) {

			$layout = $this->getLayout();

			if (!$layout || ($layout && $layout == 'all')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the current layout is for favourite
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isViewFavourite()
	{
		$uid = $this->getUid();
		$type = $this->getType();

		if (is_null($uid) && is_null($type)) {
			$layout = $this->getLayout();

			if ($layout == 'favourite') {
				return true;
			}
		}

		return false;
	}

	public function isViewItem()
	{
		if ($this->isViewAll() || $this->isViewFavourite()) {
			return false;
		}

		return true;
	}

	/**
	 * Get the layout type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLayout()
	{
		return $this->input->get('layout', '', 'default');
	}

	/**
	 * Retrieve albums library
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAlbumsLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {

			if ($this->isViewAll() || $this->isViewFavourite()) {
				$lib = ES::albums(ES::user()->id, SOCIAL_TYPE_USER);
			} else {

				// Check if the current request is made for the current logged in user or another user.
				$uid = $this->getUid();
				$type = $this->getType();

				$lib = ES::albums($uid, $type);
			}
		}

		return $lib;
	}

	/**
	 * Retrieve uid if available
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUid()
	{
		return $this->input->get('uid', null, 'int');
	}

	/**
	 * Retrieve the type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->input->get('type', null, 'cmd');
	}

	/**
	 * Get the filter type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilter()
	{
		if ($this->isViewAll()) {
			return 'all';
		}

		if ($this->isViewFavourite()) {
			return 'favourite';
		}

		$filter = $this->input->get('filter', '', 'default');

		return $filter;
	}
}
