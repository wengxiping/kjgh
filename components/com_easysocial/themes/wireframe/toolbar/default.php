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
?>
<div class="es-toolbar t-lg-mb--lg" data-es-toolbar>
	<div class="es-toolbar__item es-toolbar__item--home">
		<nav class="o-nav es-toolbar__o-nav">
			<?php if ($dashboard) { ?>
			<div class="o-nav__item <?php echo $highlight == 'dashboard' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::dashboard();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-home"></i>
				</a>
			</div>
			<?php } ?>
		</nav>
	</div>

	<div class="es-toolbar__item es-toolbar__item--home-submenu" data-es-toolbar-menu>
		<div class="o-nav es-toolbar__o-nav">

			<?php if ($this->my->id) { ?>

				<?php if ($this->isMobile() || $this->isTablet()) { ?>
					<div class="o-nav__item">
						<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>" class="o-nav__link es-toolbar__link">
							<i class="fa fa-user-edit t-sm-visible"></i>
							<span><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'); ?></span>
						</a>
					</div>

					<?php if ($showVerificationLink) { ?>
					<div class="o-nav__item">
						<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>" class="o-nav__link es-toolbar__link">
							<i class="far fa-check-circle t-sm-visible"></i>
							<span><?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?></span>
						</a>
					</div>
					<?php } ?>

				<?php } ?>

				<div class="o-nav__item <?php echo $highlight == 'profile' ? 'is-active' : '';?>">
					<a href="<?php echo $this->my->getPermalink();?>" class="o-nav__link es-toolbar__link">
						<i class="fa fa-user t-sm-visible"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE'); ?>
						</span>
					</a>
				</div>
			<?php } ?>

			<?php if ($this->config->get('pages.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'pages' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::pages();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-briefcase t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PAGES'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if ($this->config->get('groups.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'groups' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::groups();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-users t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_GROUPS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if ($this->config->get('events.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'events' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::events();?>" class="o-nav__link es-toolbar__link">
					<i class="far fa-calendar-alt t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_EVENTS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if ($this->config->get('video.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'videos' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::videos();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-play t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_VIDEOS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if ($this->config->get('audio.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'audios' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::audios();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-music t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_AUDIOS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if ($this->config->get('photos.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'albums' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::albums();?>" class="o-nav__link es-toolbar__link">
					<i class="far fa-image t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PHOTOS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<div class="o-nav__item <?php echo $highlight == 'users' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::users();?>" class="o-nav__link es-toolbar__link">
					<i class="fa fa-user-friends t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PEOPLE'); ?>
					</span>
				</a>
			</div>

			<?php if ($this->config->get('polls.enabled')) { ?>
			<div class="o-nav__item <?php echo $highlight == 'polls' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::polls();?>" class="o-nav__link es-toolbar__link">
					<i class="far fa-chart-bar t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_POLLS'); ?>
					</span>
				</a>
			</div>
			<?php } ?>

			<?php if (($this->isMobile() || $this->isTablet()) && $this->my->id) { ?>

			<div class="o-nav__item <?php echo $view == 'conversations' ? 'is-active' : '';?>">
				<a href="<?php echo ESR::conversations();?>" class="o-nav__link es-toolbar__link">
					<i class="far fa-envelope t-sm-visible"></i>
					<span>
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS'); ?>
					</span>
				</a>
			</div>

			<div class="o-nav__item " data-es-logout>
				<a href="javascript:void(0);" class="o-nav__link es-toolbar__link" data-es-logout-button>
					<i class="fa fa-power-off t-sm-visible"></i>
					<span>
						<?php echo JText::_('Logout'); ?>
					</span>
				</a>
				<form class="logout-form" action="<?php echo JRoute::_('index.php');?>" data-es-logout-form method="post">
					<input type="hidden" name="return" value="<?php echo $logoutReturn;?>" />
					<input type="hidden" name="option" value="com_easysocial" />
					<input type="hidden" name="controller" value="account" />
					<input type="hidden" name="task" value="logout" />
					<input type="hidden" name="view" value="" />
					<?php echo $this->html('form.token'); ?>
				</form>
			</div>

			<?php } ?>

		</div>

	</div>

	<?php if ($search) { ?>
		<?php echo $this->includeTemplate('site/toolbar/search'); ?>
	<?php } ?>

	<div class="es-toolbar__item es-toolbar__item--action">
		<nav class="o-nav es-toolbar__o-nav">

			<?php if ($search) { ?>
			<div class="o-nav__item">
				<a href="javascript:void(0);" class="o-nav__link es-toolbar__link es-toolbar__link--search" data-es-toolbar-search-toggle><i class="fa fa-search"></i></a>
			</div>
			<?php } ?>

			<?php if ($this->my->id) { ?>
				<?php if ($friends) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link es-toolbar__link <?php echo $newRequests > 0 ? ' has-new' : '';?>"
						data-notifications data-type="friends"
						data-popbox="module://easysocial/friends/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-friends"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="9"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
					>
						<i class="fa fa-user-friends"></i>
						<span class="es-toolbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS');?></span>
						<span class="es-toolbar__link-bubble" data-counter><?php echo $newRequests;?></span>
					</a>
				</div>
				<?php } ?>

				<?php if ($conversations) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link es-toolbar__link <?php echo $newConversations > 0 ? ' has-new' : '';?>"
						data-notifications data-type="conversations"
						data-popbox="module://easysocial/conversations/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-conversations"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="9"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
						data-popbox-view="<?php echo $view; ?>"
					>
						<i class="fa fa-envelope"></i>
						<span class="es-toolbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS');?></span>
						<span class="es-toolbar__link-bubble" data-counter><?php echo $newConversations;?></span>
					</a>

				</div>
				<?php } ?>

				<?php if ($notifications) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);"
						class="o-nav__link es-toolbar__link <?php echo $newNotifications > 0 ? ' has-new' : '';?>"
						data-popbox="module://easysocial/notifications/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-notifications"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="9"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
						data-autoread="<?php echo $this->config->get('notifications.system.autoread');?>"
						data-user-id="<?php echo $this->my->id;?>"
						data-notifications data-type="system"
					>
						<i class="fa fa-bell"></i>
						<span class="es-toolbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_NOTIFICATIONS');?></span>
						<span class="es-toolbar__link-bubble" data-counter><?php echo $newNotifications;?></span>
					</a>
				</div>
				<?php } ?>
			<?php } ?>



			<?php if ($this->my->guest && ($login)) { ?>
			<div class="o-nav__item">
				<a href="javascript:void(0);" class="o-nav__link es-toolbar__link"
					data-popbox data-popbox-id="es"
					data-popbox-type="navbar-signin"
					data-popbox-toggle="click"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="9"
					data-popbox-position="bottom-right"
					data-popbox-target="[data-toolbar-login-dropdown-2]"
				>
					<i class="fa fa-lock"></i>
				</a>

				<div class="t-hidden" class="toobar-profile-popbox" data-toolbar-login-dropdown-2>

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

								<?php echo $this->html('form.floatinglabel', $usernamePlaceholder, 'username'); ?>

								<?php echo $this->html('form.floatinglabel', 'COM_EASYSOCIAL_TOOLBAR_PASSWORD', 'password', 'password'); ?>

								<?php if ($this->config->get('general.site.twofactor')) { ?>
								<div class="o-form-group">
									<label for="es-secretkey"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SECRET');?>:</label>
									<input type="text" autocomplete="off" name="secretkey" class="form-control" id="es-secretkey" />
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
			</div>
			<?php } ?>

			<?php if (!$this->my->guest && $profile && !$this->isMobile()){ ?>
			<div class="o-nav__item is-signin dropdown_">
				<a href="javascript:void(0);" class="o-nav__link es-toolbar__link has-avatar dropdown-toggle_" data-bs-toggle="dropdown">
					<div class="es-toolbar__avatar">
						<div class="o-avatar o-avatar--sm <?php echo $this->config->get('layout.avatar.style') == 'rounded' ? 'o-avatar--rounded' : '';?>">
							<img src="<?php echo $this->my->getAvatar();?>" title="<?php echo $this->html('string.escape', $this->my->getName());?>" />
						</div>
					</div>
				</a>
				<div class="es-toolbar__dropdown-menu es-toolbar__dropdown-menu--action dropdown-menu bottom-right
					<?php echo ES::easyblog()->hasToolbar() || ES::easydiscuss()->hasToolbar() ? 't-width--66' : '';?>
					<?php echo !ES::easyblog()->hasToolbar() && !ES::easydiscuss()->hasToolbar() ? 't-width--33' : '';?>
					" data-es-toolbar-dropdown>
					<div class="arrow"></div>
					<div class="es-toolbar-profile">
						<?php
						$cover = $this->my->getCoverData();
						?>
						<div class="es-toolbar-profile__hd with-cover">
							<div class="es-toolbar-profile-cover" style="background-image:url('<?php echo $this->my->getCover();?>'); background-repeat: no-repeat; background-position: <?php echo $cover->getPosition();?>; background-size: cover;">
							</div>
							<div class="es-toolbar-profile-info">
								<div class="o-media o-media--rev">
									<div class="o-media__body o-media__body--text-overflow">
										<a class="es-user-name" href="<?php echo $this->my->getPermalink();?>"><?php echo $this->my->getName();?></a>


										<?php if ($this->config->get('users.layout.profiletitle')) { ?>
										<div class="es-toolbar-profile-meta">
											<div class="es-toolbar-profile-meta__item">
												<a href="<?php echo $this->my->getProfile()->getPermalink();?>" >
													<i class="fa fa-shield-alt"></i>&nbsp; <?php echo $this->my->getProfile()->get('title');?>
												</a>
											</div>
										</div>
										<?php } ?>

									</div>

									<div class="o-media__image">
										<?php echo $this->html('avatar.user', $this->my, 'sm', false, false);?>
									</div>
								</div>

								<?php
								$badges = $this->my->getBadges();

								if ($badges) {
								?>
								<div class="es-toolbar-profile-badges">
									<?php foreach ($badges as $badge) { ?>
									<a href="<?php echo $badge->getPermalink();?>">
										<img src="<?php echo $badge->getAvatar();?>" alt="<?php echo $this->html('string.escape', $badge->getTitle());?>" width="20" height="20" />
									</a>
									<?php } ?>
								</div>
								<?php } ?>
							</div>
						</div>

						<div class="es-toolbar-profile__ft">
							<?php
							if (ES::easyblog()->exists() || ES::easydiscuss()->exists()) {
								$ebToolbar = ES::easyblog()->toolbar();
								$edToolbar = ES::easydiscuss()->toolbar();

								if ($ebToolbar || $edToolbar) {
							?>
								<div class="es-toolbar-dropdown-nav">
									<?php if ($ebToolbar) { echo $ebToolbar; } ?>
									<?php if ($edToolbar) { echo $edToolbar; } ?>
								</div>
								<?php } ?>
							<?php } ?>

							<div class="es-toolbar-dropdown-nav">
								<?php if ($this->my->hasCommunityAccess()) { ?>
									<div class="es-toolbar-dropdown-nav__item ">
										<span class="es-toolbar-dropdown-nav__link">

											<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_HEADING_ACCOUNT');?></div>
											<ol class="g-list-unstyled es-toolbar-dropdown-nav__meta-lists">
												<li>
													<a href="<?php echo $this->my->getPermalink();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_VIEW_YOUR_PROFILE');?></a>
												</li>

												<?php if ($this->config->get('friends.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::friends();?>">
														<?php echo JText::_('COM_ES_MY_FRIENDS'); ?>
													</a>
												</li>
												<?php } ?>

												<?php if ($this->config->get('followers.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::followers();?>">
														<?php echo JText::_('COM_ES_MY_FOLLOWERS'); ?>
													</a>
												</li>
												<?php } ?>

												<?php if ($showVerificationLink) { ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>"><?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?></a>
												</li>
												<?php } ?>

												<?php if (!$this->isMobile()) { ?>
													<?php if ($this->config->get('friends.invites.enabled')) { ?>
													<li>
														<a href="<?php echo ESR::friends(array('layout' => 'invite'));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_INVITE_FRIENDS');?>
														</a>
													</li>
													<?php } ?>


													<?php if ($this->config->get('badges.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::badges(array('layout' => 'achievements'));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACHIEVEMENTS');?>
														</a>
													</li>
													<?php } ?>

													<?php if ($this->config->get('points.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::points(array('layout' => 'history' , 'userid' => $this->my->getAlias()));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_POINTS_HISTORY');?>
														</a>
													</li>
													<?php } ?>

													<?php if ($this->config->get('conversations.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::conversations();?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_CONVERSATIONS');?>
														</a>
													</li>
													<?php } ?>
												<?php } ?>
											</ol>
										</span>
									</div>

									<?php if (!$this->isMobile()) { ?>
									<div class="es-toolbar-dropdown-nav__item ">
										<span class="es-toolbar-dropdown-nav__link">

											<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_DISCOVER');?></div>
											<ol class="g-list-unstyled es-toolbar-dropdown-nav__meta-lists">
												<li>
													<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ADVANCED_SEARCH');?></a>
												</li>

												<?php if ($this->config->get('points.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::leaderboard();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_LEADERBOARD');?></a>
												</li>
												<?php } ?>

												<?php if ($this->config->get('apps.browser')) { ?>
												<li>
													<a href="<?php echo ESR::apps();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_APPS');?></a>
												</li>
												<?php } ?>
											</ol>
										</span>
									</div>
									<?php } ?>

								<?php } ?>

								<div class="es-toolbar-dropdown-nav__item ">
									<span class="es-toolbar-dropdown-nav__link">
										<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_HEADING_PREFERENCES');?></div>
										<ol class="g-list-unstyled es-toolbar-dropdown-nav__meta-lists">

											<li>
												<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>">
													<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ACCOUNT_SETTINGS');?>
												</a>
											</li>

											<?php if ($this->my->hasCommunityAccess()) { ?>
												<?php if ($this->config->get('privacy.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'editPrivacy'));?>">
														<?php echo JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY');?>
													</a>
												</li>
												<?php } ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'editNotifications'));?>">
														<?php echo JText::_('COM_EASYSOCIAL_MANAGE_ALERTS');?>
													</a>
												</li>
												<?php if ($this->config->get('activity.logs.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::activities();?>">
														<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES');?>
													</a>
												</li>
												<?php } ?>
											<?php } ?>

										</ol>
									</span>
								</div>

								<?php if ($this->config->get('sharer.users')) { ?>
								<div class="es-toolbar-dropdown-nav__item ">
									<span class="es-toolbar-dropdown-nav__link">
										<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_ES_TOOLBAR_HEADING_SOCIAL_PLUGINS');?></div>
										<ol class="g-list-unstyled es-toolbar-dropdown-nav__meta-lists">
											<li>
												<a href="<?php echo ESR::sharer(array('layout' => 'embed'));?>">
													<?php echo JText::_('COM_ES_SHARE_BUTTON');?>
												</a>
											</li>
										</ol>
									</span>
								</div>
								<?php } ?>

								<?php if (($this->my->isSiteAdmin() || $this->my->getAccess()->get('pendings.manage')) && ($this->config->get('pages.enabled') || $this->config->get('groups.enabled') || $this->config->get('events.enabled'))) { ?>
								<div class="es-toolbar-dropdown-nav__item ">
									<span class="es-toolbar-dropdown-nav__link">
										<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_ES_TOOLBAR_PROFILE_HEADING_MANAGE');?></div>
										<ol class="g-list-unstyled es-toolbar-dropdown-nav__meta-lists">
											<?php if ($this->config->get('events.enabled')) { ?>
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_EVENTS');?>
												</a>
											</li>
											<?php } ?>

											<?php if ($this->config->get('groups.enabled')) { ?>
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'group'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_GROUPS');?>
												</a>
											</li>
											<?php } ?>

											<?php if ($this->config->get('pages.enabled')) { ?>
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'page'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_PAGES');?>
												</a>
											</li>
											<?php } ?>
										</ol>
									</span>
								</div>
								<?php } ?>


								<div class="es-toolbar-dropdown-nav__item " data-es-logout>
									<div class="es-toolbar-dropdown-nav__item">
										<a href="javascript:void(0);" class="es-toolbar-dropdown-nav__link" data-es-logout-button>

											<div class="es-toolbar-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT');?></div>
											<ol class="g-list-inline g-list-inline--delimited es-toolbar-dropdown-nav__meta-lists">
												<li>
													<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT_INFO');?>
												</li>
											</ol>
										</a>
									</div>
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
						</div>
					</div>
				</div>

				<div class="t-hidden" data-es-toolbar-profile-dropdown-2>
					<div class="popbox-dropdown">
						<div class="popbox-dropdown__hd">
							<div class="es-toolbar-profile-cover" style="background-image:url('<?php echo $this->my->getCover();?>'); background-repeat: no-repeat; background-position: 0% 0%; background-size: cover;">
							</div>
							<div class="es-toolbar-profile-info">
								<div class="o-media o-media--rev">
									<div class="o-media__body o-media__body--text-overflow">
										<a class="es-user-name" href="<?php echo $this->my->getPermalink();?>"><?php echo $this->my->getName();?></a>
									</div>

									<div class="o-media__image">
										<?php echo $this->html('avatar.user', $this->my, 'sm', false, false);?>
									</div>
								</div>
							</div>
						</div>

						<div class="popbox-dropdown__bd">
							<div class="popbox-dropdown-nav">
								<?php if ($this->my->hasCommunityAccess()) { ?>
									<div class="popbox-dropdown-nav__item ">
										<span class="popbox-dropdown-nav__link">

											<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_HEADING_ACCOUNT');?></div>
											<ol class="g-list-unstyled popbox-dropdown-nav__meta-lists">
												<li>
													<a href="<?php echo $this->my->getPermalink();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_VIEW_YOUR_PROFILE');?></a>
												</li>

												<?php if ($showVerificationLink) { ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>"><?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?></a>
												</li>
												<?php } ?>

												<?php if (!$this->isMobile()) { ?>
													<?php if ($this->config->get('friends.invites.enabled')) { ?>
													<li>
														<a href="<?php echo ESR::friends(array('layout' => 'invite'));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_INVITE_FRIENDS');?>
														</a>
													</li>
													<?php } ?>


													<?php if ($this->config->get('badges.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::badges(array('layout' => 'achievements'));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACHIEVEMENTS');?>
														</a>
													</li>
													<?php } ?>

													<?php if ($this->config->get('points.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::points(array('layout' => 'history' , 'userid' => $this->my->getAlias()));?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_POINTS_HISTORY');?>
														</a>
													</li>
													<?php } ?>

													<?php if ($this->config->get('conversations.enabled')){ ?>
													<li>
														<a href="<?php echo ESR::conversations();?>">
															<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_CONVERSATIONS');?>
														</a>
													</li>
													<?php } ?>
												<?php } ?>
											</ol>
										</span>
									</div>

									<?php if (!$this->isMobile()) { ?>
									<div class="popbox-dropdown-nav__item ">
										<span class="popbox-dropdown-nav__link">

											<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_DISCOVER');?></div>
											<ol class="g-list-unstyled popbox-dropdown-nav__meta-lists">
												<li>
													<a href="<?php echo ESR::search(array('layout' => 'advanced'));?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ADVANCED_SEARCH');?></a>
												</li>

												<?php if ($this->config->get('points.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::leaderboard();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_LEADERBOARD');?></a>
												</li>
												<?php } ?>

												<?php if ($this->config->get('apps.browser')) { ?>
												<li>
													<a href="<?php echo ESR::apps();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_APPS');?></a>
												</li>
												<?php } ?>
											</ol>
										</span>
									</div>
									<?php } ?>

								<?php } ?>

								<div class="popbox-dropdown-nav__item ">
									<span class="popbox-dropdown-nav__link">
										<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_HEADING_PREFERENCES');?></div>
										<ol class="g-list-unstyled popbox-dropdown-nav__meta-lists">

											<li>
												<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>">
													<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ACCOUNT_SETTINGS');?>
												</a>
											</li>

											<?php if ($this->my->hasCommunityAccess()) { ?>
												<?php if ($this->config->get('privacy.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'editPrivacy'));?>">
														<?php echo JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY');?>
													</a>
												</li>
												<?php } ?>
												<li>
													<a href="<?php echo ESR::profile(array('layout' => 'editNotifications'));?>">
														<?php echo JText::_('COM_EASYSOCIAL_MANAGE_ALERTS');?>
													</a>
												</li>
												<?php if ($this->config->get('activity.logs.enabled')) { ?>
												<li>
													<a href="<?php echo ESR::activities();?>">
														<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES');?>
													</a>
												</li>
												<?php } ?>
											<?php } ?>

										</ol>
									</span>
								</div>

								<?php if ($this->my->isSiteAdmin() || $this->my->getAccess()->get('pendings.manage')) { ?>
								<div class="popbox-dropdown-nav__item ">
									<span class="popbox-dropdown-nav__link">
										<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_ES_TOOLBAR_PROFILE_HEADING_MANAGE');?></div>
										<ol class="g-list-unstyled popbox-dropdown-nav__meta-lists">
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'event'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_EVENTS');?>
												</a>
											</li>
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'group'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_GROUPS');?>
												</a>
											</li>
											<li>
												<a href="<?php echo ESR::manage(array('layout' => 'clusters', 'filter' => 'page'));?>">
													<?php echo JText::_('COM_ES_TOOLBAR_PENDING_PAGES');?>
												</a>
											</li>
										</ol>
									</span>
								</div>
								<?php } ?>


								<div class="popbox-dropdown-nav__item " data-es-logout>
									<div class="popbox-dropdown-nav__item">
										<a href="javascript:void(0);" class="popbox-dropdown-nav__link" data-es-logout-button>

											<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT');?></div>
											<ol class="g-list-inline g-list-inline--delimited popbox-dropdown-nav__meta-lists">
												<li>
													<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT_INFO');?>
												</li>
											</ol>
										</a>
									</div>
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
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php if ($this->isMobile() || $this->isTablet()) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link es-toolbar__link" href="javascript:void(0);" data-es-toolbar-toggle>
					<i class="fa fa-bars"></i>
				</a>
			</div>
			<?php } ?>
		</nav>
	</div>
</div>




