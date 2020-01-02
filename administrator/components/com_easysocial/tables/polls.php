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

ES::import('admin:/tables/table');

class SocialTablePolls extends SocialTable
{
	public $id = null;
	public $element = null;
	public $uid = null;
	public $title = null;
	public $multiple = null;
	public $cluster_id = null;
	public $locked = null;
	public $created = null;
	public $created_by = null;
	public $expiry_date = null;

	public function __construct($db)
	{
		parent::__construct('#__social_polls', 'id', $db);
	}

	/**
	 * Determines if this poll allows multiple choices
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMultiple()
	{
		return $this->multiple;
	}

	/**
	 * Determines if the user has voted before
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isVoted($userId)
	{
		$model = ES::model('Polls');
		return $model->isVoted($this->id, $userId);
	}

	/**
	 * Determines if the poll has already expired
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasExpired()
	{
		if (!$this->hasExpirationDate()) {
			return false;
		}

		// Check if it has already expired
		$current = ES::date()->toSql();

		if ($current >= $this->expiry_date) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the polls has an expiration date
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasExpirationDate()
	{
		if (!$this->expiry_date || $this->expiry_date == '0000-00-00 00:00:00') {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the expiry date of the poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getExpiryDate()
	{
		$date = ES::date($this->expiry_date);

		return $date;
	}

	/**
	 * Retrieves the total of votes made on this poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalVotes()
	{
		static $count = array();

		if (!isset($count[$this->id])) {
			$model = ES::model('Polls');
			$count[$this->id] = $model->getTotalVotes($this->id);
		}

		return $count[$this->id];
	}

	/**
	 * Retrieves the author of the poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAuthor()
	{
		$cluster = $this->getCluster();

		// Special case for Page
		if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE) {
			return $cluster;
		}

		$author = ES::user($this->created_by);

		return $author;
	}

	/**
	 * Retrieves the cluster of the poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCluster()
	{
		if (!$this->cluster_id) {
			return false;
		}

		return ES::cluster($this->cluster_id);
	}

	/**
	 * Generates the permalink to the poll item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false)
	{
		$permalink = ESR::stream(array('layout' => 'item', 'id' => $this->uid, 'external' => $external), $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves all poll options for the poll
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItems()
	{
		$model = ES::model('Polls');
		$items = $model->getItems($this->id);

		return $items;
	}

	/**
	 * Overrides the standard table deletion behavior
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {

			// Get poll items and delete them
			$model = ES::model('Polls');
			$items = $model->getItems($this->id);

			// Delete the poll items for this poll
			if ($items) {
				foreach ($items as $item) {
					$pollItem = ES::table('PollsItems');
					$pollItem->load($item->id);

					$pollItem->delete();
				}
			}

			// Deduct points from the creator of the poll
			$userId = $this->created_by;

			if ($userId) {
				ES::points()->assign('polls.remove', 'com_easysocial', $userId);
			}

			// Delete stream item
			$model = ES::model('Polls');
			$model->deletePollStreams($this->id);

			// Delete any reactions related to the poll
			$likesModel = ES::model('Likes');
			$likesModel->delete($this->id, 'polls.user.create');
		}

		return $state;
	}

	public function updateStreamPrivacy($streamId)
	{

		$privacy = FD::table('Privacy');
		$state = $privacy->load(array('type'=>'polls', 'rule'=>'view'));

		if ($state) {
			$model = ES::model('Polls');
			$model->updateStreamPrivacy($streamId, $privacy->id);
		}

		return true;
	}

	/**
	 * Overrides the standard table deletion behavior
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function canVote()
	{
		$my = ES::user();
		$access = $my->getAccess();
		if (!$access->allowed('polls.vote')) {
			return false;
		}

		// cluster polls
		if ($this->cluster_id) {
			$table = ES::table('cluster');
			$table->load($this->cluster_id);

			$cluster = ES::cluster($table->cluster_type, $table->id);

			if (!$cluster->isMember()) {
				return false;
			}
		}

		// user polls
		if (!$this->cluster_id) {
			$my = ES::user();

			//let check if user can view this polls or not.
			$privacy = $my->getPrivacy();
			if (!$privacy->validate('polls.view', $this->id, SOCIAL_TYPE_POLLS, $this->created_by)) {
				return false;
			}
		}

		return true;
	}


}
