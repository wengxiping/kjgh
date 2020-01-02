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

class EasySocialViewProfilePrivacyHelper extends EasySocial
{

	/**
	 * Get current active tab
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveTab()
	{
		$activeTab = $this->input->get('activeTab', '', 'cmd');
		return $activeTab;
	}


	/**
	 * Get the custom alerts if there is any
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getBlockedUsers()
	{
		static $blockedUsers = null;

		if (is_null($blockedUsers)) {
			// Get a list of blocked users for this user
			$blockModel = ES::model('Blocks');
			$blockedUsers = $blockModel->getBlockedUsers($this->my->id);
		}

		return $blockedUsers;
	}

	/**
	 * Get the alert filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPrivacy()
	{
		static $privacy = null;

		if (is_null($privacy)) {

			// Get user's privacy
			$privacyLib = ES::privacy($this->my->id);
			$result = $privacyLib->getData();

			$privacy = array();

			// Update the privacy data with proper properties.
			foreach ($result as $group => $items) {

				// We do not want to show field privacy rules here because it does not make sense for user to set a default value
				// Most of the fields only have 1 and it is set in Edit Profile page
				if ($group === 'field') {
					continue;
				}

				// Only display such privacy rules if photos is enabled
				if (($group == 'albums' || $group == 'photos') && !$this->config->get('photos.enabled')) {
					continue;
				}

				// Only display videos privacy if videos is enabled.
				if ($group == 'videos' && !$this->config->get('video.enabled')) {
					continue;
				}

				// Do not display badges / achievements in privacy if badges is disabled
				if ($group == 'achievements' && !$this->config->get('badges.enabled')) {
					continue;
				}

				// Do not display followers privacy item
				if ($group == 'followers' && !$this->config->get('followers.enabled')) {
					continue;
				}

				// Do not display followers privacy item if friends disabled
				if ($group == 'friends' && !$this->config->get('friends.enabled')) {
					continue;
				}

				// Do not display points privacy item
				if ($group == 'points' && !$this->config->get('points.enabled')) {
					continue;
				}

				// Do not display application privacy item
				if ($group == 'apps' && !$this->config->get('apps.browser')) {
					continue;
				}

				// Do not display polls privacy item
				if ($group == 'polls' && !$this->config->get('polls.enabled')) {
					continue;
				}

				// Initialize the result
				$privacyGroup = new stdClass();
				$privacyGroup->title = JText::_('COM_EASYSOCIAL_PRIVACY_GROUP_' . strtoupper($group));
				$privacyGroup->element = $group;
				$privacyGroup->description = JText::_('COM_EASYSOCIAL_PRIVACY_GROUP_' . strtoupper($group) . '_DESC');

				foreach ($items as &$item) {

					// Conversations rule should only appear if it is enabled.
					if (($group == 'profiles' && $item->rule == 'post.message') && !$this->config->get('conversations.enabled')) {
						$item = null;
						continue;
					}

					if (($group == 'photos' && ($item->rule == 'tagme' || $item->rule == 'tag')) && !$this->config->get('photos.tagging')) {
						$item = null;
						continue;
					}

					// if friends disabled and the default value set to 'friends', lets override it to 'member'
					if (!$this->config->get('friends.enabled') && ($item->default == SOCIAL_PRIVACY_FRIENDS_OF_FRIEND || $item->default == SOCIAL_PRIVACY_FRIEND)) {
						$item->default = SOCIAL_PRIVACY_MEMBER;
					}

					$rule = JString::str_ireplace('.', '_', $item->rule);
					$rule = strtoupper($rule);

					$groupKey = strtoupper($group);

					// Determines if this has custom
					$item->hasCustom = $item->custom ? true : false;

					// If the rule is a custom rule, we need to set the ids
					$item->customIds = array();
					$item->customUsers = array();

					if ($item->hasCustom) {
						foreach ($item->custom as $friend) {
							$item->customIds[] = $friend->user_id;

							$user = ES::user($friend->user_id);
							$item->customUsers[] = $user;
						}
					}

					// Try to find an app element that is related to the privacy type
					$app = ES::table('App');
					$appExists = $app->load(array('element' => $item->type));

					if ($appExists) {
						$app->loadLanguage();
					}

					// Go through each options to get the selected item
					$item->selected = '';

					foreach ($item->options as $option => $value) {
						if ($value) {
							$item->selected = $option;
						}

						// We need to remove "Everyone" if the site lockdown is enabled
						if ($this->config->get('general.site.lockdown.enabled') && $option == SOCIAL_PRIVACY_0) {
							unset($item->options[$option]);
						}

						// we need to remove 'friends' / 'friend of friends' if Friends disabled.
						if (!$this->config->get('friends.enabled') && ($option == SOCIAL_PRIVACY_20 || $option == SOCIAL_PRIVACY_30)) {
							unset($item->options[$option]);

							// set member as default.
							if ($value) {
								$item->options[SOCIAL_PRIVACY_10] = 1;
								$item->selected = SOCIAL_PRIVACY_10;
							}
						}


					}

					$item->groupKey = $groupKey;
					$item->label = JText::_('COM_EASYSOCIAL_PRIVACY_LABEL_' . $groupKey . '_' . $rule );
					$item->tips = JText::_('COM_EASYSOCIAL_PRIVACY_TIPS_' . $groupKey . '_' . $rule );
				}

				$privacyGroup->items = $items;
				$privacy[] = $privacyGroup;
			}

		}

		return $privacy;
	}
}
