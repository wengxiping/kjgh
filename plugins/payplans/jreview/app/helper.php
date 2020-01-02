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

require_once(__DIR__ . '/lib.php');

class PPHelperJReview extends PPHelperStandardApp
{
	/**
	 * Adds resources for jreviews
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createResources(PPSubscription $subscription, PPUser $user, $appConfiguration, $category = null)
	{
		$subscriptionId = $subscription->getId();
		$userId = $user->getId();
		$categoryId = $category ? $category : 0; 

		$this->addResource($subscriptionId, $userId, $categoryId, 'com_jreview.category', $appConfiguration->total);
		$this->addResource($subscriptionId, $userId, $categoryId, 'com_jreview.image', $appConfiguration->images);
		$this->addResource($subscriptionId, $userId, $categoryId, 'com_jreview.audio', $appConfiguration->audio);
		$this->addResource($subscriptionId, $userId, $categoryId, 'com_jreview.video', $appConfiguration->video);
		$this->addResource($subscriptionId, $userId, $categoryId, 'com_jreview.attachment', $appConfiguration->attachments);

		return true;
	}

	/**
	 * Adds resources for jreviews
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteResources(PPSubscription $subscription, PPUser $user, $appConfiguration, $category = null)
	{
		$subscriptionId = $subscription->getId();
		$userId = $user->getId();
		$categoryId = $category ? $category : 0; 

		$this->removeResource($subscriptionId, $userId, $categoryId, 'com_jreview.category', $appConfiguration->total);
		$this->removeResource($subscriptionId, $userId, $categoryId, 'com_jreview.image', $appConfiguration->images);
		$this->removeResource($subscriptionId, $userId, $categoryId, 'com_jreview.audio', $appConfiguration->audio);
		$this->removeResource($subscriptionId, $userId, $categoryId, 'com_jreview.video', $appConfiguration->video);
		$this->removeResource($subscriptionId, $userId, $categoryId, 'com_jreview.attachment', $appConfiguration->attachments);

		return true;
	}

	/**
	 * Get configuration params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppConfiguration()
	{
		$data = new stdClass();
		$data->type = $this->params->get('add_entry_in', 'any_category');
		
		$data->categories = $this->params->get('add_entry_in_category', '');
		$data->categories = !is_array($data->categories) ? array($data->categories) : $data->categories;

		$data->total = $this->params->get('no_of_submisssion', 0);
		$data->images = $this->params->get('no_of_images', 0);
		$data->audio = $this->params->get('no_of_audio', 0);
		$data->video = $this->params->get('no_of_video', 0);
		$data->attachments = $this->params->get('no_of_attachment', 0);

		return $data;
	}

	/**
	 * Publish entries from jreviews
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function publishEntries(PPSubscription $subscription, PPUser $user, $appConfiguration, $category = null)
	{
		// Insert resources
		$this->createResources($subscription, $user, $appConfiguration, $category);

		$db = PP::db();
		$query = array();

		$query[] = 'UPDATE `#__content` SET `state` = ' . $db->Quote(1);
		$query[] = 'WHERE `created_by`=' . $db->Quote($user->getId());
		$query[] = 'AND `state` = ' . $db->Quote(0);
		$query[] = 'ORDER BY `publish_up`';
		$query[] = 'LIMIT ' . $appConfiguration->total;

		$db->setQuery($query);
		return $db->Query();
	}

	/**
	 * Unpublish entries from jreviews
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function unpublishEntries(PPSubscription $subscription, PPUser $user, $appConfiguration, $category = null)
	{
		// Insert resources
		$this->deleteResources($subscription, $user, $appConfiguration, $category);

		$db = PP::db();
		$query = array();

		$query[] = 'UPDATE `#__content` SET `state` = ' . $db->Quote(0);
		$query[] = 'WHERE `created_by`=' . $db->Quote($user->getId());
		$query[] = 'AND `state` = ' . $db->Quote(1);
		$query[] = 'ORDER BY `publish_up`';
		$query[] = 'LIMIT ' . $appConfiguration->total;

		$db->setQuery($query);
		return $db->Query();
	}
}