<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class SocialToolbar extends EasySocial
{
	public static function factory()
	{
		$toolbar = new self();

		return $toolbar;
	}

	/**
	 * Deprecated. Use FRoute::getRedirectionUrl($menuId)
	 *
	 * @deprecated 1.3.21
	 * @access	public
	 */
	public function getRedirectionUrl($menuId)
	{
		return FRoute::getRedirectionUrl($menuId);
	}

	/**
	 * Renders the HTML block for the notification bar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function render($options = array())
	{
		$theme = ES::themes();

		// Show toolbar for guest?
		if (!$this->config->get('general.layout.toolbarguests') && $this->my->guest) {
			return;
		}

		// Do not show toolbar when in lockdown mode
		if ($this->config->get('general.site.lockdown.enabled') && $this->my->guest) {
			return;
		}

		// Default options
		$newConversations = false;
		$newRequests = false;
		$newNotifications = false;
		$facebook = false;

		// Display counter related stuffs for logged in user and user that has access to the community
		if ($this->my->id && $this->my->hasCommunityAccess()) {

			// Get a list of new conversations
			$newConversations = $this->my->getTotalNewConversations();

			if ($newConversations && $newConversations >= 99) {
				$newConversations = '99+';
			}

			// Get total pending request count
			$newRequests = 0;
			if ($this->config->get('friends.enabled')) {
				$newRequests = $this->my->getTotalFriendRequests();

				if ($newRequests && $newRequests >= 99) {
					$newRequests = '99+';
				}
			}

			// Get new system notifications
			$newNotifications = $this->my->getTotalNewNotifications();

			if ($newNotifications && $newNotifications >= 99) {
				$newNotifications = '99+';
			}
		}

		// Only render facebook codes if user is not logged in
		if ($this->my->guest) {
			$facebook = FD::oauth('Facebook');
		}

		// Get login redirection url
		$loginMenu = $this->config->get('general.site.login');
		$loginReturn = base64_encode(ESR::getCurrentURI());

		if ($loginMenu != 'null') {
			$loginReturn = ESR::getMenuLink($loginMenu);
			$loginReturn = base64_encode($loginReturn);
		}

		// Get logout redirection url
		$logoutMenu = $this->config->get('general.site.logout');
		$logoutReturn = FRoute::getMenuLink($logoutMenu);
		$logoutReturn = base64_encode($logoutReturn);

		// Determines if there's any force display options passed in arguments
		$forceOption = isset($options['forceoption']) ? $options['forceoption'] : false;

		// Default this two is enabled.
		$friends = isset($options['friends']) ? $options['friends'] : true;

		// friends.enabled supercede the options.
		if (! $this->config->get('friends.enabled')) {
			$friends = false;
		}

		$notifications = isset($options['notifications']) ? $options['notifications'] : true;

		// Get other options from arguments
		$dashboard = isset($options['dashboard']) ? $options['dashboard'] : true;
		$conversations = isset($options['conversations']) ? $options['conversations'] : $this->config->get('conversations.enabled', true);
		$search = isset($options['search']) ? $options['search'] : false;
		$login = isset($options['login']) ? $options['login'] : true;
		$profile = isset($options['profile']) ? $options['profile'] : true;

		// Get current theme
		$currentTheme = $theme->getCurrentTheme();

		// Allow caller to determine the popbox position
		$defaultPopboxPosition = $currentTheme == 'elegant' ? 'bottom-right' : 'bottom-left';
		$defaultPopboxPosition = $this->doc->getDirection() == 'rtl' ? 'bottom-right' : $defaultPopboxPosition;
		$popboxPosition = isset($options['modulePopboxPosition']) ? $options['modulePopboxPosition'] : $defaultPopboxPosition;

		// Allow caller to determine the popbox collision
		$defaultPopboxCollision = 'none';
		$popboxCollision = isset($options['modulePopboxCollision']) ? $options['modulePopboxCollision'] : $defaultPopboxCollision;

		// Get template settings
		$template = $theme->getConfig();

		// Should we enforce the arguments that is passed in?
		if (!$forceOption) {

			$conversations = $this->config->get('conversations.enabled') || $conversations;
			$search = ($this->config->get('general.layout.toolbarsearch') && (!$this->my->guest || ($this->my->guest && $this->config->get('general.layout.toolbarsearchguests')))) || $search;
		}

		// If the user doesn't have access to the community we need to enforce specific options here
		if (!$this->my->hasCommunityAccess()) {
			$friends = false;
			$conversations = false;
			$notifications = false;
			$dashboard = false;
			$search = false;
		}

		$filters = array();

		if ($search) {
			$searchLib = ES::search();
			$filters = $searchLib->getFilters();
		}

		// Get the current request variables
		$userId = $this->input->get('id', 0, 'int');
		$view = $this->input->get('view', '', 'cmd');
		$layout = $this->input->get('layout', '', 'cmd');
		$type = $this->input->get('type', '', 'cmd');

		$showRegistrations = true;

		if (!$this->config->get('registrations.enabled') || ($this->config->get('general.site.lockdown.enabled') && !$this->config->get('general.site.lockdown.registration'))) {
			$showRegistrations = false;
		}

		$verification = ES::verification();
		$showVerificationLink = false;

		if ($verification->canRequest()) {
			$showVerificationLink = true;
		}

		$sso = ES::sso();

		// Get highlighted menu
		$highlight = $this->getNavHightlight();

		$theme->set('highlight', $highlight);
		$theme->set('sso', $sso);
		$theme->set('showRegistrations', $showRegistrations);
		$theme->set('filters', $filters);
		$theme->set('newConversations', $newConversations);
		$theme->set('newRequests', $newRequests);
		$theme->set('newNotifications', $newNotifications);
		$theme->set('facebook', $facebook);
		$theme->set('userId', $userId);
		$theme->set('view', $view);
		$theme->set('type', $type);
		$theme->set('layout', $layout);
		$theme->set('login', $login);
		$theme->set('profile', $profile);
		$theme->set('search', $search);
		$theme->set('dashboard', $dashboard);
		$theme->set('friends', $friends);
		$theme->set('conversations', $conversations);
		$theme->set('notifications', $notifications);
		$theme->set('loginReturn', $loginReturn);
		$theme->set('logoutReturn', $logoutReturn);
		$theme->set('popboxPosition', $popboxPosition);
		$theme->set('popboxCollision', $popboxCollision);
		$theme->set('showVerificationLink', $showVerificationLink);

		$usernamePlaceholder = $this->config->get('general.site.loginemail') ? 'COM_EASYSOCIAL_TOOLBAR_LOGIN_NAME_OR_EMAIL' : 'COM_EASYSOCIAL_TOOLBAR_LOGIN_NAME';
		if ($this->config->get('registrations.emailasusername')) {
			$usernamePlaceholder = 'COM_EASYSOCIAL_TOOLBAR_EMAIL';
		}

		$theme->set('usernamePlaceholder', JText::_($usernamePlaceholder));

		$output = $theme->output('site/toolbar/default');

		return $output;
	}

	/**
	 * Determine the item that we should highlight based on the current viewed page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getNavHightlight()
	{
		// Get the current request variables
		$view = $this->input->get('view', '', 'cmd');
		$layout = $this->input->get('layout', '', 'cmd');
		$type = $this->input->get('type', '', 'cmd');

		// Default
		$highlight = $view;

		$userId = $this->input->get('id', 0, 'int');
		$useProfileNav = true;
		$useGroupNav = true;
		$useEventNav = true;
		$usePageNav = true;

		if ($view != 'profile') {
			$useProfileNav = false;
			$mediaViews = array('albums', 'videos', 'audios');

			if (in_array($view, $mediaViews) && $type == 'user') {
				$userId = $this->input->get('uid', 0, 'int');
			} else {
				$userId = $this->input->get('userid', 0, 'int');
			}

			if ($userId) {
				$useProfileNav = true;
			}
		}

		if ($view != 'groups') {
			$useGroupNav = false;

			if ($type == 'group') {
				$useGroupNav = true;
			}
		}

		if ($view != 'pages') {
			$usePageNav = false;

			if ($type == 'page') {
				$usePageNav = true;
			}
		}

		if ($view != 'events') {
			$useEventNav = false;

			if ($type == 'event') {
				$useEventNav = true;
			}
		}

		if ($view == 'groups' || $useGroupNav) {
			$highlight = 'groups';
		}

		if ($view == 'pages' || $usePageNav) {
			$highlight = 'pages';
		}

		if ($view == 'events' || $useEventNav) {

			if (!$useGroupNav && !$usePageNav) {
				$highlight = 'events';
			}
		}

		if ($view == 'profile' || $useProfileNav) {
			$highlight = '';

			if (!$userId || ($userId && $this->my->id == $userId) || ($useProfileNav && $this->my->id == $userId)) {
				$highlight = 'profile';
			}
		}

		if ($view == 'friends' && $layout == 'invite') {
			$highlight = '';
		}

		return $highlight;
	}
}