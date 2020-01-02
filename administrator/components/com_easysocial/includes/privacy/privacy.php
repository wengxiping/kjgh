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

require_once(dirname(__FILE__) . '/option.php');

class SocialPrivacy extends EasySocial
{
	private $target = null;
	private $type = null;

	private $data = null;
	public static $keys = array(
								'public' => SOCIAL_PRIVACY_PUBLIC,	// 0
								'member' => SOCIAL_PRIVACY_MEMBER,	// 10
								'friends_of_friend'	=> SOCIAL_PRIVACY_FRIENDS_OF_FRIEND,	// 20
								'friend' => SOCIAL_PRIVACY_FRIEND,	// 30
								'only_me' => SOCIAL_PRIVACY_ONLY_ME,	// 40
								'custom' => SOCIAL_PRIVACY_CUSTOM,	// 100
								'field' => SOCIAL_PRIVACY_FIELD	// 200
							);

	public static $icons	= array(
								'public' => 'fa fa-globe-americas',
								'member' => 'fa fa-user',	// 10
								'friends_of_friend'	=> 'fa fa-user-friends',	// 20
								'friend' => 'fa fa-user-friends',	// 30
								'only_me' => 'fa fa-lock',	// 40
								'custom' => 'fa fa-wrench',	// 100
								'field' => 'fa fa-cogs'	// 200
							);

	public static $resetMap = array(
									'story.view',
									'photos.view',
									'albums.view',
									'videos.view',
									'polls.view',
									'core.view',
									'easyblog.blog.view'
								);




	public static $userPrivacy	= array();

	public function __construct($target = '', $type = SOCIAL_PRIVACY_TYPE_USER)
	{
		$this->target = $target;
		$this->type = $type;

		parent::__construct();
	}

	/**
	 * Loads the privacy object for a particular node item.
	 *
	 * @since	3.0
	 * @access	public
	 *
	 */
	public static function factory($target = '', $type = SOCIAL_PRIVACY_TYPE_USER)
	{
		$obj = new self($target, $type);
		return $obj;
	}

	/**
	 * Given a privacy value in string, convert it back to integer.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function toValue($key)
	{
		$key = JString::strtolower($key);
		$value = 0;

		if (array_key_exists($key , self::$keys)) {
			$value 	= self::$keys[$key];
		}

		return $value;
	}

	/**
	 * Given a privacy value in integer, convert it back to a string identifier.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getResetMap($type = 'user')
	{
		$map = self::$resetMap;

		if ($type == 'all') {
			$model = ES::model('Privacy');
			$commands = $model->getAllRulesCommand();

			if ($commands) {
				$map = $commands;
			}
		}

		return $map;
	}

	/**
	 * Given a privacy value in integer, convert it back to a string identifier.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public static function toKey($value = '0')
	{
		return self::getKey($value);
	}

	/**
	 * Given a privacy value in integer, convert it back to a string identifier.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public static function getKey($value)
	{
		$rkey = 'public';

		if (self::$keys) {
			foreach (self::$keys as $key => $kval) {
				if ($kval == $value) {
					$rkey = $key;
					break;
				}
			}
		}

		return $rkey;
	}

	/**
	 * Retrieves the raw data of the privacy object.
	 *
	 * Example:
	 * <code>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getData()
	{
		if (!$this->data) {
			$this->data = $this->getPrivacyData();
		}

		return $this->data;
	}

	/**
	 * Retrieves an object's privacy.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getPrivacyData()
	{
		static $items = array();

		if (!$this->target || !$this->type) {
			return false;
		}

		$key = $this->target . $this->type;

		if (!isset($items[$key])) {
			$model = ES::model('Privacy');
			$items[$key] = $model->getData($this->target, $this->type);
		}

		return $items[$key];
	}


	/**
	 * add privacy on object
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function add($rule, $uid, $utype, $pvalue, $userId = null, $custom = '', $field = '')
	{
		// lets get the privacy id based on the $rule.
		$rules = explode('.', $rule);

		$element = array_shift($rules);
		$rule = implode('.', $rules);

		$model = ES::model('Privacy');
		$privacyId = $model->getPrivacyId($element, $rule, true);

		if (is_numeric($pvalue)) {
			$pvalue = $this->toKey($pvalue);
		}

		if (is_null($userId) || !$userId) {
			$userId = $this->target;
		}

		// if still empty, then we will just use the current logged in user id.
		if (is_null($userId) || !$userId) {
			$userId = $this->my->id;
		}

		$state = $model->update($userId, $privacyId, $uid, $utype, $pvalue, $custom, $field);
		return $state;
	}

	/**
	 * Retrieves an object's privacy.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getOption($uid, $type = '', $ownerId = '', $command = null)
	{
		// When type and owner id isn't specified, we assume that we are retrieving user's privacy.
		if (!$type && !$ownerId) {
			$type = SOCIAL_TYPE_USER;
			$ownerId = $uid;
		}

		if ($type == SOCIAL_TYPE_USER) {
			$option = new SocialPrivacyOption();
			$option->type = $type;
			$option->uid = $uid;
			$option->user_id = empty($ownerId) ? $uid : $ownerId;
			return $option;
		}

		// Retrieve object's privacy
		$model = ES::model('Privacy');
		$pItem = $model->getPrivacyItem($uid, $type, $ownerId, $command);

		$option = new SocialPrivacyOption();
		$option->id = $pItem->id;
		$option->default = $pItem->default;
		$option->option = $pItem->option;
		$option->uid = $pItem->uid;
		$option->type = $pItem->type;
		$option->user_id = $pItem->user_id;
		$option->value = $pItem->value;
		$option->custom = $pItem->custom;
		$option->field = $pItem->field;
		$option->pid = $pItem->pid;
		$option->editable = $pItem->editable;
		$option->override = false;

		return $option;
	}

	/**
	 * Generates the privacy form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form($uid, $type, $ownerId, $command = null, $isHtml = false, $streamId = null, $editOverride = array(), $displayOptions = array())
	{
		$config = ES::config();

		if (!$config->get('privacy.enabled')) {
			return '';
		}

		// Normalize the display options
		$iconOnly = $this->normalize($displayOptions, 'iconOnly', false);
		$linkStyle = $this->normalize($displayOptions, 'linkStyle', '');

		// Get a list of privacy options
		$item = $this->getOption($uid, $type, $ownerId, $command);

		// List of custom privacy user ids.
		$customPrivacyUserIds = array();

		// Preload users from custom notifications
		if (count($item->custom) > 0) {

			foreach ($item->custom as $customPrivacy) {
				$customPrivacyUserIds[] = $customPrivacy->user_id;
			}

			if ($customPrivacyUserIds) {
				ES::user($customPrivacyUserIds);
			}
		}

		// Should we check if the current user has the edit privacy override ability?
		$override = isset($editOverride['override']) ? $editOverride['override'] : null;
		$overrideValue = isset($editOverride['value']) ? $editOverride['value'] : null;

		if ($this->my->id && $editOverride && $override && $overrideValue) {
			$item->editable = $overrideValue;
			$item->override = true;
		}

		if ($this->my->isSiteAdmin() && $item->user_id != $this->my->id) {
			// we allow admin to change the privacy on other users items. #1593
			$item->editable = true;
			$item->override = true;
		}

		// lets override the value if friends disabled
		if (!$this->config->get('friends.enabled')) {
			if ($item->value == SOCIAL_PRIVACY_FRIENDS_OF_FRIEND || $item->value == SOCIAL_PRIVACY_FRIEND) {
				$item->value = SOCIAL_PRIVACY_MEMBER;
			}
		}

		// lets ovrride the value if custom fields for privacy disabled.
		if (!$this->config->get('users.privacy.field')) {
			if ($item->value == SOCIAL_PRIVACY_FIELD) {
				$item->value = SOCIAL_PRIVACY_MEMBER;
			}
		}

		// Get the privacy key
		$key = $this->toKey($item->value);

		// Set the tooltip text
		$languageKey = strtoupper($key);
		$tooltips = JText::_('COM_EASYSOCIAL_PRIVACY_TOOLTIPS_SHARED_WITH_' . $languageKey, true);

		// Get the icon for the rule
		$icon = $this->getIconClass($key);

		$defaultCustom = implode(',', $customPrivacyUserIds);

		// Get the default selected label
		$defaultLabel = JText::_('COM_EASYSOCIAL_PRIVACY_OPTION_' . $languageKey);
		$defaultKey = '';

		// We do not want to display "Public" if the site is under lockdown mode
		if ($this->config->get('general.site.lockdown.enabled')) {
			unset($item->option['public']);
		}

		// remove friends and 'friend of friends' privacy if Friends system disabled.
		if (!$this->config->get('friends.enabled')) {
			unset($item->option['friend']);
			unset($item->option['friends_of_friend']);
		}

		// remove custom field privacy if custom fields usage on privacy disabled.
		if (! $this->config->get('users.privacy.field')) {
			unset($item->option['field']);
		}

		// Format the options now
		$options = array();

		foreach ($item->option as $optionKey => $optionValue) {
			$option = new stdClass();
			$option->key = $optionKey;
			$option->value = $optionValue;
			$option->icon = $this->getIconClass($optionKey);
			$option->active = $option->value ? true : false;
			$option->label = JText::_('COM_EASYSOCIAL_PRIVACY_OPTION_' . strtoupper($option->key));

			$options[] = $option;

			if ($option->active) {
				$defaultKey = $option->key;
			}
		}

		$item->options = $options;

		$theme = ES::themes();
		$theme->set('linkStyle', $linkStyle);
		$theme->set('iconOnly', $iconOnly);
		$theme->set('item', $item);
		$theme->set('isHtml' , $isHtml);
		$theme->set('tooltips', $tooltips);
		$theme->set('streamid', $streamId);
		$theme->set('icon', $icon);
		$theme->set('defaultLabel', $defaultLabel);
		$theme->set('defaultKey', $defaultKey);
		$theme->set('defaultCustom', $defaultCustom);

		$output = $theme->output('site/privacy/form');

		return $output;
	}

	/**
	 * Retrieve the icon for a given privacy key
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function getIconClass($key = 'public')
	{
		if (!$key) {
			$key = 'public';
		}

		return self::$icons[$key];
	}

	public function getValue($key, $rule)
	{
		$data = $this->getData();

		// default to core.view
		// Test if the rule even exist first.
		if (! isset($data[$key][$rule])) {
			$key = 'core';
			$rule = 'view';
		}

		$check = $data[$key][$rule];

		if (empty($check)) {
			// no privacy at all ?!
			// just return 0
			return 0;
		}

		$options 	= (array) $data[$key][$rule]->options;

		// We only want to get the items that are checked.
		// Since the options value only contains 0 or 1.
		$value = '';

		$firstOption = '';
		if (in_array('1', $options)) {
			$options = array_flip($options);
			$firstOption = $options[1];
		} else {
			$firstOption = array_shift($options);
		}

		$selected = $this->toValue($firstOption);

		$customData = $data[$key][$rule]->custom;
		if ($customData) {
			$value = array($selected, $customData);
		} else {
			$value = $selected;
		}

		return $value;
	}

	/**
	 * Validates a certain action againts list of objects
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function validate($keys, $uid, $utype = '', $ucreatorid = '', $debug = false)
	{
		$rules = explode('.', $keys);
		$key = array_shift($rules);
		$rule = implode('.', $rules);

		// If privacy has been disabled, allow access
		if (!$this->config->get('privacy.enabled')) {
			return true;
		}

		// This is to test if the current viewer is a site admin because site admin is always
		// allowed to view anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		$targetUser = ES::user($this->target);

		// If owner, always allow.
		if ($targetUser->id && $targetUser->id == $ucreatorid) {
			return true;
		}

		$element = $this->getOption($uid, $utype, $ucreatorid, $keys);

		if (empty($element)) {
			return false;
		}

		if ($element->type == SOCIAL_TYPE_USER) {
			// this mean we check again user's privacy setting.
			$targetPrivacy = ES::privacy($element->user_id);
			$targetValue = $targetPrivacy->getValue($key, $rule);

			$data = array();

			if (is_array($targetValue)) {
				$data[0] = $targetValue[0];
				$data[1] = $targetValue[1];
			} else {
				$data[0] = $targetValue;
				$data[1] = null;
			}

			return $this->check($this->target, $element->user_id, $data[0], $data[1], null);
		} else {
			//this mean we cheak again app's object privacy
			return $this->check($this->target, $element->user_id, $element->value, $element->custom, $element->field);
		}
	}

	/**
	 * perform the actual checking
	 *
	 * @since	3.0
	 * @access	public
	 */

	private function check($my_id, $target_id, $target_privacy, $target_privacy_custom = null,  $target_privacy_field = null)
	{
		$isValid 	= false;
		$config = ES::config();

		if ($my_id && $my_id == $target_id) {
			return true;
		}

		// here we need to reset the privacy to member if the rule is friends / friends_of_frinds and friends system disabled.
		// @394
		if (!$config->get('friends.enabled') && ($target_privacy == SOCIAL_PRIVACY_FRIENDS_OF_FRIEND || $target_privacy == SOCIAL_PRIVACY_FRIEND)) {
			$target_privacy = SOCIAL_PRIVACY_MEMBER;
		}

		// if custom fields usage on privacy disabled, lets override the value to members.
		if (!$config->get('users.privacy.field') && $target_privacy == SOCIAL_PRIVACY_FIELD) {
			$target_privacy = SOCIAL_PRIVACY_MEMBER;
		}

		switch($target_privacy) {
			// Public privacy simply means that everything is valid :)
			case SOCIAL_PRIVACY_PUBLIC:
				$isValid = true;

				break;

			// Member privacy simply means that the viewer needs to be a logged in user.
			case SOCIAL_PRIVACY_MEMBER:
				$isValid = $my_id > 0;

				break;

			// Friends of friend basically means that the user needs to be at least a 2nd level friends.
			case SOCIAL_PRIVACY_FRIENDS_OF_FRIEND:

				if ($my_id == $target_id) {
					$isValid = true;
					break;
				}

				$friendsModel = ES::model('Friends');
				$isValid = $friendsModel->isFriendsOfFriends($target_id , $my_id);

				break;

			// The viewer needs to be a friend with the target.
			case SOCIAL_PRIVACY_FRIEND:

				if ($my_id == $target_id) {
					$isValid = true;
					break;
				}

				$friendsModel = ES::model('Friends');
				$isValid = $friendsModel->isFriends($target_id , $my_id);
				break;

			// Only viewable by the target
			case SOCIAL_PRIVACY_ONLY_ME:

				$isValid = $my_id == $target_id;

				break;

			// Custom privacy values here.
			case SOCIAL_PRIVACY_CUSTOM:

				if ($my_id == $target_id) {
					$isValid = true;
					break;
				}

				$customData = $target_privacy_custom;

				if (empty($customData)) {
					$isValid = false;
					break;

				}

				foreach ($customData as $item) {
					if ($item->user_id == $my_id) {
						$isValid = true;
						break;
					}
				}

				break;

			// Field privacy values here.
			case SOCIAL_PRIVACY_FIELD:

				if ($my_id == $target_id) {
					$isValid = true;
					break;
				}

				$fieldData = $target_privacy_field;

				if (empty($fieldData)) {
					$isValid = false;
					break;
				}

				// preparing the field data.
				$data = array();
				foreach ($fieldData as $item) {

					$key = $item->unique_key;
					$options = $item->options;

					foreach ($options as $opt) {
						if ($opt->selected) {
							$data[$key][] = $opt->value;
						}
					}
				}

				if (! $data) {
					$isValid = false;
					break;
				}

				$privacyModel = ES::model('Privacy');
				$isValid = $privacyModel->hasPrivacyCustomFieldValue($data, $my_id);

				break;

			default:
				break;
		}

		return $isValid;

	}

}
