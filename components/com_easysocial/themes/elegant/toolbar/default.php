<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-navbar" data-es-toolbar>
	<ul class="es-nav pull-left fd-cf g-list-unstyled">
		<li class="dropdown_">
			<a data-toolbar-toggle="" data-bs-toggle="dropdown" class="es-nav-dropdown-toggle dropdown-toggle_" href="javascript:void(0);">
				<i class="fa fa-th"></i>
			</a>
			<div role="menu" class="es-nav-dropdown for-guide dropdown-menu">

				<?php if ($dashboard) { ?>
				<div class="<?php echo $view == 'dashboard' ? 'is-active' : '';?> is-home">
					<a href="<?php echo ESR::dashboard();?>">
						<i class="fa fa-home"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_HOME'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('photos.enabled')) { ?>
				<div class="<?php echo $view == 'albums' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::albums();?>">
						<i class="far fa-images"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PHOTOS');?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('video.enabled')) { ?>
				<div class="<?php echo $view == 'videos' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::videos();?>">
						<i class="fa fa-film"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_VIDEOS'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('audio.enabled')) { ?>
				<div class="<?php echo $view == 'audios' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::audios();?>">
						<i class="fa fa-music"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_AUDIOS'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('groups.enabled')) { ?>
				<div class="<?php echo $view == 'groups' || ($view == 'apps' && $type == 'group') ? 'is-active' : '';?>">
					<a href="<?php echo ESR::groups();?>">
						<i class="fa fa-users"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_GROUPS'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('events.enabled')) { ?>
				<div class="<?php echo $view == 'events' || ($view == 'apps' && $type == 'event') ? 'is-active' : '';?>">
					<a href="<?php echo ESR::events();?>">
						<i class="fa fa-calendar"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_EVENTS'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('pages.enabled')) { ?>
				<div class="<?php echo $view == 'pages' || ($view == 'apps' && $type == 'page') ? 'is-active' : '';?>">
					<a href="<?php echo ESR::pages();?>">
						<i class="fa fa-columns"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PAGES'); ?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('badges.enabled') && $this->my->id) { ?>
				<div>
					<a href="<?php echo ESR::badges(array('layout' => 'achievements'));?>">
						<i class="fa fa-trophy"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACHIEVEMENTS');?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('points.enabled') && $this->my->id) { ?>
				<div>
					<a href="<?php echo ESR::points(array('layout' => 'history' , 'userid' => $this->my->getAlias()));?>">
						<i class="fa fa-chart-bar"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_POINTS_HISTORY');?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('conversations.enabled') && $this->my->id) { ?>
				<div>
					<a href="<?php echo ESR::conversations();?>">
						<i class="fa fa-comments"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_CONVERSATIONS');?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('polls.enabled') && $this->my->id) { ?>
				<div>
					<a href="<?php echo ESR::polls();?>">
						<i class="fa fa-chart-bar"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_POLLS');?>
						</b>
					</a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('apps.browser') && $this->my->id) { ?>
				<div>
					<a href="<?php echo ESR::apps();?>">
						<i class="fa fa-puzzle-piece"></i>
						<b>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_APPS');?>
						</b>
					</a>
				</div>
				<?php } ?>

			</div>

		</li>
	</ul>
	<ul class="es-nav g-list-unstyled pull-right">

		<?php if ($search) { ?>
			<li class="ed-nav-toggle-search-wrap">
				<a data-elegant-toggle-search="" class="es-nav-toggle-search" href="javascript:void(0);">
					<i class="fa fa-search"></i>
				</a>
			</li>
		<?php } ?>

		<?php if ($this->my->id) { ?>
			<?php if ($notifications) { ?>
			<li
				data-notifications data-type="system"
				data-user-id=""
				data-autoread=""
				data-popbox-collision="none"
				data-popbox-position="bottom-right"
				data-popbox-toggle="click"
				data-popbox="module://easysocial/notifications/popbox"
				data-popbox-type="navbar-notifications"
				data-popbox-component="popbox--navbar"
				class="toolbarItem <?php echo $newNotifications > 0 ? ' has-new' : '';?>"
				>
				<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_RECENT_NOTIFICATIONS' , true );?>" href="javascript:void(0);">
					<i class="fa fa-globe"></i>
					<span data-counter class="label label-notification"><?php echo $newNotifications;?>
					</span>
				</a>
			</li>
			<?php } ?>
			<?php if ($conversations) { ?>
			<li
				data-notifications data-type="conversations"
				data-popbox-collision="none"
				data-popbox-position="bottom-right"
				data-popbox-toggle="click"
				data-popbox="module://easysocial/conversations/popbox"
				data-popbox-type="navbar-conversations"
				data-popbox-component="popbox--navbar"
				data-popbox-view="<?php echo $view; ?>"
				class="toolbarItem <?php echo $newConversations > 0 ? ' has-new' : '';?>"
				>
				<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_RECENT_CONVERSATIONS' , true );?>" href="javascript:void(0);">
					<i class="fa fa-comments"></i>
					<span data-counter class="label label-notification"><?php echo $newConversations;?></span>
				</a>
			</li>
			<?php } ?>
			<?php if ($friends) { ?>
			<li
				data-notifications data-type="friends"
				data-popbox-collision="none"
				data-popbox-position="bottom-right"
				data-popbox-toggle="click"
				data-popbox="module://easysocial/friends/popbox"
				data-popbox-type="navbar-friends"
				data-popbox-component="popbox--navbar"
				class="toolbarItem <?php echo $newRequests > 0 ? ' has-new' : '';?>"
				>
				<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS', true);?>" class="loadRequestsButton" href="javascript:void(0);">
					<i class="fa fa-users"></i>
					<span data-counter class="label label-notification"><?php echo $newRequests;?></span>
				</a>
			</li>
			<?php } ?>
		<?php } ?>

		<?php if ($this->my->id) { ?>
		<li data-toolbar-profile="" class="toolbarItem toolbar-profile dropdown_">
			<a data-toolbar-toggle="" data-bs-toggle="dropdown" class="es-nav-dropdown-toggle dropdown-toggle_" href="#">
				<i class="fa fa-cog"></i>
			</a>
			<div data-toolbar-profile-dropdown="" role="menu" class="es-nav-dropdown for-menu dropdown-menu">
				<a style="background-image:url(<?php echo $this->my->getCover();?>)" class="es-nav-dropdown-cover" href="<?php echo $this->my->getPermalink();?>">
					<div class="row-table">
						<div class="col-cell cell-thumb">
							<img src="<?php echo $this->my->getAvatar();?>" />
						</div>
						<div class="col-cell cell-bio">
							<div class="cell-name text-overflow"><?php echo $this->my->getName();?></div>
							<div class="t-fs--sm">
								<?php if ($this->config->get('points.enabled')){ ?>
								<span>
									<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_POINTS', $this->my->getPoints()), $this->my->getPoints()); ?>
								</span>
								<?php } ?>

								<span>
									<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GENERIC_FRIENDS', $this->my->getTotalFriends()), $this->my->getTotalFriends()); ?>
								</span>
							</div>
						</div>
					</div>
				</a>

				<?php if ($this->my->id) { ?>
					<?php if ($showVerificationLink) { ?>
					<div>
						<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>">
							<?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?>
						</a>
					</div>

					<?php } ?>
					<?php if ($this->config->get('friends.enabled')) { ?>
					<div>
						<a href="<?php echo ESR::friends();?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIENDS'); ?>
						</a>
					</div>
					<?php } ?>

					<?php if ($this->config->get('friends.invites.enabled')) { ?>
					<div>
						<a href="<?php echo ESR::friends(array('layout' => 'invite'));?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_INVITE_FRIENDS');?>
						</a>
					</div>
					<?php } ?>

					<?php if ($this->config->get('followers.enabled')) { ?>
					<div class="<?php echo $view == 'followers' ? 'is-active' : '';?>">
						<a href="<?php echo ESR::followers();?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FOLLOWERS'); ?>
						</a>
					</div>
					<?php } ?>
				<?php } ?>

				<?php if ($this->config->get('general.layout.toolbarbrowse')) { ?>
				<div>
					<a href="<?php echo ESR::users();?>">
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_BROWSE_USERS');?>
					</a>
				</div>
				<?php } ?>
				<div>
					<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ADVANCED_SEARCH');?></a>
				</div>

				<?php if ($this->config->get('points.enabled')) { ?>
				<div>
					<a href="<?php echo ESR::leaderboard();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_LEADERBOARD');?></a>
				</div>
				<?php } ?>

				<?php if ($this->config->get('apps.browser')) { ?>
				<div>
					<a href="<?php echo ESR::apps();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_APPS');?></a>
				</div>
				<?php } ?>

				<hr>

				<div>
					<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>">
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ACCOUNT_SETTINGS');?>
					</a>
				</div>

				<?php if ($this->my->hasCommunityAccess()) { ?>
					<?php if ($this->config->get('privacy.enabled')) { ?>
					<div>
						<a href="<?php echo ESR::profile(array('layout' => 'editPrivacy'));?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PRIVACY_SETTINGS');?>
						</a>
					</div>
					<?php } ?>
					<div>
						<a href="<?php echo ESR::profile(array('layout' => 'editNotifications'));?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_NOTIFICATION_SETTINGS');?>
						</a>
					</div>
					<?php if ($this->config->get('activity.logs.enabled')) { ?>
					<div>
						<a href="<?php echo ESR::activities();?>">
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES');?>
						</a>
					</div>
					<?php } ?>
				<?php } ?>

				<?php if ($this->my->isSiteAdmin() || $this->my->getAccess()->get('pendings.manage')) { ?>
				<hr>

				<div>
					<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));?>">
						<?php echo JText::_('COM_ES_TOOLBAR_PENDING_EVENTS');?>
					</a>
				</div>
				<div>
					<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'group'));?>">
						<?php echo JText::_('COM_ES_TOOLBAR_PENDING_GROUPS');?>
					</a>
				</div>
				<div>
					<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'page'));?>">
						<?php echo JText::_('COM_ES_TOOLBAR_PENDING_PAGES');?>
					</a>
				</div>
				<?php } ?>

				<hr />
				<div data-es-logout>
					<a data-es-logout-button class="logout-link" href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT');?></a>
					<form class="logout-form" action="<?php echo JRoute::_('index.php');?>" data-es-logout-form method="post">
						<input type="hidden" name="return" value="<?php echo $logoutReturn;?>" />
						<input type="hidden" name="option" value="com_easysocial" />
						<input type="hidden" name="controller" value="account" />
						<input type="hidden" name="task" value="logout" />
						<input type="hidden" name="view" value="" />
						<?php echo $this->html('form.token'); ?>
					</form>
				</div>
			</div>
		</li>
		<?php } ?>

		<?php if ($this->my->guest && ($login)) { ?>
		<li class="toolbarItem toolbar-login">
			<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link"
				data-popbox data-popbox-id="es" data-popbox-type="navbar-signin" data-popbox-toggle="click" data-popbox-component="popbox--navbar"
				data-popbox-offset="4" data-popbox-position="bottom-right" data-popbox-target="[data-toolbar-login-dropdown]">
				<i class="fa fa-lock"></i>
			</a>

			<div class="t-hidden" class="toobar-profile-popbox" data-toolbar-login-dropdown>

				<div class="popbox-dropdown">
					<div class="popbox-dropdown__hd">
						<div class="o-flag o-flag--rev">
							<div class="o-flag__body">
								<div class="popbox-dropdown__title"><?php echo JText::_('COM_EASYSOCIAL_SIGN_IN');?></div>

								<?php if ($showRegistrations) { ?>
								<div class="popbox-dropdown__meta">
									<?php echo JText::sprintf('COM_EASYSOCIAL_NEW_PLEASE_REGISTER', '<a href="' . ESR::registration(array('profile_id' => '0')) . '">' . JText::_('COM_EASYSOCIAL_TOOLBAR_CREATE_ACCOUNT_NOW') . '</a>'); ?>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="popbox-dropdown__bd">
						<form action="<?php echo JRoute::_('index.php');?>" method="post" class="popbox-dropdown-signin">
							<div class="o-form-group">
								<input name="username" type="text" autocomplete="off" class="o-form-control" id="es-username"
									placeholder="<?php echo $usernamePlaceholder; ?>"
								/>
							</div>
							<div class="o-form-group">
								<input name="password" type="password" class="o-form-control" id="es-password" autocomplete="off"  placeholder="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PASSWORD');?>" />
							</div>

							<?php if ($this->config->get('general.site.twofactor')) { ?>
							<div class="o-form-group">
								<label for="es-secretkey"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SECRET');?>:</label>
								<input type="text" autocomplete="off" name="secretkey" class="form-control" id="es-secretkey">
							</div>
							<?php } ?>
							<div class="o-row">
								<div class="o-col o-col--8">
									<div class="o-checkbox o-checkbox--sm">
										<input type="checkbox" id="es-remember" name="remember" />
										<label for="es-remember"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_REMEMBER_ME');?></label>
									</div>
								</div>
								<div class="o-col">
									<button class="btn btn-es-primary t-lg-pull-right"><?php echo JText::_('COM_EASYSOCIAL_LOGIN_BUTTON');?></button>
								</div>
							</div>

							<?php if ($sso->hasSocialButtons()) { ?>
							<div class="popbox-dropdown__social t-lg-mt--md">
								<?php foreach ($sso->getSocialButtons() as $socialButton) { ?>
								<div class="t-text--center t-lg-mt--md">
									<?php echo $socialButton; ?>
								</div>
								<?php } ?>
							</div>
							<?php } ?>

							<input type="hidden" name="option" value="com_easysocial" />
							<input type="hidden" name="controller" value="account" />
							<input type="hidden" name="task" value="login" />
							<input type="hidden" name="return" value="<?php echo $loginReturn;?>" />
							<?php echo $this->html( 'form.token' );?>
						</form>
					</div>

					<div class="popbox-dropdown__ft">
						<ul class="g-list-inline g-list-inline--dashed t-text--center">
							<?php if (!$this->config->get('registrations.emailasusername')) { ?>
							<li>
								<a href="<?php echo ESR::account(array('layout' => 'forgetUsername'));?>" class="popbox-dropdown__note">
									<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_FORGOT_USERNAME');?>
								</a>
							</li>
							<?php } ?>
							<li>
								<a href="<?php echo ESR::account(array('layout' => 'forgetPassword'));?>" class="popbox-dropdown__note">
									<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_FORGOT_PASSWORD');?>
								</a>
							</li>
						</ul>

					</div>
				</div>


			</div>
		</li>
		<?php } ?>
	</ul>

	<?php if ($search) { ?>
		<?php echo $this->includeTemplate('site/toolbar/search'); ?>
	<?php } ?>
</div>
