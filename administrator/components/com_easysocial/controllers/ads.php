<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerAds extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('saveAdvertiser', 'saveAdvertiser');
		$this->registerTask('applyAdvertiser', 'saveAdvertiser');
		$this->registerTask('publishAdvertiser', 'togglePublishAdvertiser');
		$this->registerTask('unpublishAdvertiser', 'togglePublishAdvertiser');
		$this->registerTask('deleteAdvertiser', 'deleteAdvertiser');
	}

	/**
	 * Removes ads from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$ad = ES::table('Ad');
			$ad->load((int) $id);
			$ad->delete();
		}

		$this->view->setMessage('COM_ES_ADS_DELETED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Removes advertiser from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function deleteAdvertiser()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$ad = ES::table('Advertiser');
			$ad->load((int) $id);
			$ad->delete();
		}

		$this->view->setMessage('COM_ES_ADS_ADVERTISERS_DELETED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Toggles the publish state for the ads
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		$task = $this->getTask();

		foreach ($ids as $id) {
			$ad = ES::table('Ad');
			$ad->load((int) $id);

			$ad->$task();
		}

		$message = 'COM_ES_ADS_PUBLISHED';

		if ($task == 'unpublish') {
			$message = 'COM_ES_ADS_UNPUBLISHED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Saves a ad from the back end
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		// Get the ad id from the request
		$id = $this->input->get('id', 0, 'int');

		$ad = ES::table('Ad');

		if ($id) {
			$ad->load($id);
		}

		$post = $this->input->post->getArray();

		if (!$post['advertiser_id']) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_ADVERTISER', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		$cover = $this->input->files->get('cover');

		if (!$ad->id && empty($cover['tmp_name'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_COVER', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		$startDate = '0000-00-00 00:00:00';
		$endDate = '0000-00-00 00:00:00';

		if ($post['enable_limit']) {
			// Get the starting and ending date
			$start = $this->input->get('start_date', '', 'default');
			$end = $this->input->get('end_date', '', 'default');

			if (empty($start) || empty($end)) {
				$this->view->setMessage('COM_ES_ADS_EMPTY_DATE', ES_ERROR);
				return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
			}

			$startDate = ES::date($start, false);
			$endDate = ES::date($end, false);

			$startDate = $startDate->toMySQL();
			$endDate = $endDate->toMySQL();
		}

		$ad->bind($post);
		$ad->created = ES::date()->toSql();
		$ad->start_date = $startDate;
		$ad->end_date = $endDate;

		$state = $ad->store();

		if (!empty($cover['tmp_name'])) {
			$state = $ad->uploadCover($cover);
		}

		if (!$state) {
			$this->view->setMessage($ad->getError(), ES_ERROR);
		}

		$this->view->setMessage('COM_ES_ADS_UPDATED_SUCCESS');
		return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
	}

	/**
	 * Saves a ad from the back end
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function saveAdvertiser()
	{
		ES::checkToken();

		// Get the ad id from the request
		$id = $this->input->get('id', 0, 'int');

		$advertiser = ES::table('Advertiser');

		if ($id) {
			$advertiser->load($id);
		}

		$post = $this->input->post->getArray();

		if (empty($post['name'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_ADVERTISER_NAME', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $advertiser);
		}

		$advertiser->bind($post);
		$advertiser->created = ES::date()->toSql();
		$state = $advertiser->store();

		$logo = $this->input->files->get('logo');

		if (!empty($logo['tmp_name'])) {
			$state = $advertiser->uploadLogo($logo);
		}

		if (!$state) {
			$this->view->setMessage($advertiser->getError(), ES_ERROR);
		}

		$this->view->setMessage('COM_ES_ADS_UPDATED_SUCCESS');
		return $this->view->call(__FUNCTION__, $this->getTask(), $advertiser);
	}

	/**
	 * Toggles the publish state for the ads
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublishAdvertiser()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		$action = str_replace('Advertiser', '', $this->getTask());

		foreach ($ids as $id) {
			$ad = ES::table('Advertiser');
			$ad->load((int) $id);

			$ad->$action();
		}

		$message = 'COM_ES_ADVERTISER_PUBLISHED';

		if ($action == 'unpublish') {
			$message = 'COM_ES_ADVERTISER_UNPUBLISHED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}
}
