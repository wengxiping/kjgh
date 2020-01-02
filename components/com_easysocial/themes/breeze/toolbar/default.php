<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-navbar t-lg-mb--lg" data-es-toolbar>
	<div class="es-navbar__body">

		<?php if ($dashboard) { ?>
		<a class="es-navbar__footer-toggle" href="<?php echo ESR::dashboard();?>">
			<i class="fa fa-home"></i>
		</a>
		<?php } ?>

		<nav class="o-nav es-navbar__o-nav">

			<?php if ($this->my->id) { ?>

				<?php if ($friends) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link <?php echo $newRequests > 0 ? ' has-new' : '';?>"
						data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS', true);?>" data-placement="top"
						data-notifications data-type="friends"
						data-popbox="module://easysocial/friends/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-friends"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="4"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
					>
						<i class="fa fa-users"></i>
						<span class="es-navbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS');?></span>
						<span class="es-navbar__link-bubble" data-counter><?php echo $newRequests;?></span>
					</a>
				</div>
				<?php } ?>

				<?php if ($conversations) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link <?php echo $newConversations > 0 ? ' has-new' : '';?>"
						data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_RECENT_CONVERSATIONS' , true );?>" data-placement="top"
						data-notifications data-type="conversations"
						data-popbox="module://easysocial/conversations/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-conversations"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="4"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
						data-popbox-view="<?php echo $view; ?>"
					>
						<i class="fa fa-envelope"></i>
						<span class="es-navbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS');?></span>
						<span class="es-navbar__link-bubble" data-counter><?php echo $newConversations;?></span>
					</a>

				</div>
				<?php } ?>

				<?php if ($notifications) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);"
						class="o-nav__link es-navbar__icon-link <?php echo $newNotifications > 0 ? ' has-new' : '';?>"
						data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_RECENT_NOTIFICATIONS' , true );?>"
						data-placement="top"
						data-es-provide="tooltip"

						data-popbox="module://easysocial/notifications/popbox"
						data-popbox-toggle="click"
						data-popbox-type="navbar-notifications"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="4"
						data-popbox-position="bottom-right"
						data-popbox-collision="<?php echo $popboxCollision;?>"
						data-autoread="<?php echo $this->config->get('notifications.system.autoread');?>"
						data-user-id="<?php echo $this->my->id;?>"
						data-notifications data-type="system"
					>
						<i class="fa fa-globe"></i>
						<span class="es-navbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_NOTIFICATIONS');?></span>
						<span class="es-navbar__link-bubble" data-counter><?php echo $newNotifications;?></span>
					</a>
				</div>
				<?php } ?>
			<?php } ?>

			<?php if ($this->my->guest && ($login)) { ?>
			<div class="o-nav__item">
				<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link"
					data-popbox data-popbox-id="es"
					data-popbox-type="navbar-signin"
					data-popbox-toggle="click"
					data-popbox-component="popbox--navbar"
					data-popbox-offset="4"
					data-popbox-position="bottom-right"
					data-popbox-target="[data-toolbar-login-dropdown]"
				>
					<i class="fa fa-lock"></i>
					<span class="es-navbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_LOGIN');?></span>
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
			</div>
			<?php } ?>

			<?php if ($this->isMobile()) { ?>
				<?php if ($search) { ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link" data-es-toolbar-search-toggle><i class="fa fa-search"></i></a>
				</div>
				<?php } ?>

				<div class="o-nav__item">
					<a class="es-navbar__footer-toggle" href="javascript:void(0);" data-es-toolbar-toggle>
						<i class="fa fa-bars"></i>
					</a>
				</div>
			<?php } ?>

			<?php if (!$this->my->guest && $profile && !$this->isMobile()){ ?>
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__item es-navbar__icon-link"
						data-toolbar-profile
						data-popbox data-popbox-id="es"
						data-popbox-component="popbox--navbar"
						data-popbox-type="navbar-profile"
						data-popbox-toggle="click"
						data-popbox-position="bottom-right"
						data-popbox-target="[data-es-toolbar-profile-dropdown]"
						data-popbox-offset="4">
						<i class="fa fa-cog"></i>
						<span class="es-navbar__link-text"><?php echo JText::_('COM_EASYSOCIAL_MORE_SETTINGS');?></span>
					</a>

					<div class="t-hidden" data-es-toolbar-profile-dropdown>
						<div class="popbox-dropdown">
							<div class="popbox-dropdown__hd">
								<div class="o-media o-media--rev">
									<div class="o-media__body o-media__body--text-overflow">
										<a class="es-user-name" href="<?php echo $this->my->getPermalink();?>"><?php echo $this->my->getName();?></a>
									</div>

									<div class="o-media__image">
										<?php echo $this->html('avatar.user', $this->my, 'sm', false, false);?>
									</div>
								</div>
							</div>

							<div class="popbox-dropdown__bd">
								<div class="popbox-dropdown-nav">
									<?php if ($this->my->hasCommunityAccess()) { ?>
										<div class="popbox-dropdown-nav__item ">
											<span class="popbox-dropdown-nav__link">
												<div class="o-flag">
													<div class="o-flag__image o-flag--top">
														<i class="popbox-dropdown-nav__icon fa fa-user"></i>
													</div>
													<div class="o-flag__body">
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
													</div>
												</div>
											</span>
										</div>

										<?php if (!$this->isMobile()) { ?>
										<div class="popbox-dropdown-nav__item ">
											<span class="popbox-dropdown-nav__link">
												<div class="o-flag">
													<div class="o-flag__image o-flag--top">
														<i class="popbox-dropdown-nav__icon fa fa-search"></i>
													</div>
													<div class="o-flag__body">
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
													</div>
												</div>
											</span>
										</div>
										<?php } ?>

									<?php } ?>

									<div class="popbox-dropdown-nav__item ">
										<span class="popbox-dropdown-nav__link">
											<div class="o-flag">
												<div class="o-flag__image o-flag--top">
													<i class="popbox-dropdown-nav__icon fa fa-edit"></i>
												</div>
												<div class="o-flag__body">
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
												</div>
											</div>
										</span>
									</div>

									<?php if ($this->my->isSiteAdmin() || $this->my->getAccess()->get('pendings.manage')) { ?>
									<div class="popbox-dropdown-nav__item ">
										<span class="popbox-dropdown-nav__link">
											<div class="o-flag">
												<div class="o-flag__image o-flag--top">
													<i class="popbox-dropdown-nav__icon fa fa-cog"></i>
												</div>
												<div class="o-flag__body">
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
												</div>
											</div>
										</span>
									</div>
									<?php } ?>


									<div class="popbox-dropdown-nav__item " data-es-logout>
										<div class="popbox-dropdown-nav__item">
											<a href="javascript:void(0);" class="popbox-dropdown-nav__link" data-es-logout-button>
												<div class="o-flag">
													<div class="o-flag__image o-flag--top">
														<i class="popbox-dropdown-nav__icon fa fa-power-off"></i>
													</div>
													<div class="o-flag__body">
														<div class="popbox-dropdown-nav__name"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT');?></div>
														<ol class="g-list-inline g-list-inline--delimited popbox-dropdown-nav__meta-lists">
															<li>
																<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_LOGOUT_INFO');?>
															</li>
														</ol>
													</div>
												</div>
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
		</nav>

		<?php if ($search) { ?>
			<?php echo $this->includeTemplate('site/toolbar/search'); ?>
		<?php } ?>
	</div>

	<div class="es-navbar__footer" data-es-toolbar-menu>
		<div class="o-row">
			<ol class="g-list-inline g-list-inline--dashed es-navbar__footer-submenu">
				<?php if ($dashboard) { ?>
				<li class="<?php echo $highlight == 'dashboard' ? 'is-active' : '';?> is-home">
					<a href="<?php echo ESR::dashboard();?>" class="es-navbar__footer-link">
						<i class="fa fa-home"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_HOME'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->my->id) { ?>
					<?php if ($this->isMobile()) { ?>
						<li>
							<a href="<?php echo ESR::profile(array('layout' => 'edit'));?>" class="es-navbar__footer-link">
								<i class="far fa-edit"></i>
								<span><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_EDIT_PROFILE'); ?></span>
							</a>
						</li>

						<?php if ($showVerificationLink) { ?>
						<li>
							<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>" class="es-navbar__footer-link">
								<i class="far fa-check-circle"></i>
								<span><?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?></span>
							</a>
						</li>
						<?php } ?>

					<?php } ?>

					<li class="<?php echo $highlight == 'profile' ? 'is-active' : '';?>">
						<a href="<?php echo $this->my->getPermalink();?>" class="es-navbar__footer-link">
							<i class="fa fa-user"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE'); ?>
							</span>
						</a>
					</li>
				<?php } ?>

				<?php if ($this->config->get('pages.enabled')) { ?>
				<li class="<?php echo $highlight == 'pages' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::pages();?>" class="es-navbar__footer-link">
						<i class="fa fa-columns"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PAGES'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('groups.enabled')) { ?>
				<li class="<?php echo $highlight == 'groups' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::groups();?>" class="es-navbar__footer-link">
						<i class="fa fa-users"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_GROUPS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('events.enabled')) { ?>
				<li class="<?php echo $highlight == 'events' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::events();?>" class="es-navbar__footer-link">
						<i class="fa fa-calendar"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_EVENTS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->my->id) { ?>

					<?php if ($this->config->get('friends.enabled')) { ?>
					<li class="<?php echo $highlight == 'friends' ? 'is-active' : '';?>">
						<a href="<?php echo ESR::friends();?>" class="es-navbar__footer-link">
							<i class="fa fa-users"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIENDS'); ?>
							</span>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->config->get('friends.invites.enabled') && $this->isMobile()) { ?>
					<li>
						<a href="<?php echo ESR::friends(array('layout' => 'invite'));?>" class="es-navbar__footer-link">
							<i class="fa fa-envelope-o"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_INVITE_FRIENDS'); ?>
							</span>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->config->get('followers.enabled')) { ?>
					<li class="<?php echo $highlight == 'followers' ? 'is-active' : '';?>">
						<a href="<?php echo ESR::followers();?>" class="es-navbar__footer-link">
							<i class="fa fa-users"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FOLLOWERS'); ?>
							</span>
						</a>
					</li>
					<?php } ?>
				<?php } ?>

				<?php if ($this->config->get('video.enabled')) { ?>
				<li class="<?php echo $highlight == 'videos' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::videos();?>" class="es-navbar__footer-link">
						<i class="fa fa-film"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_VIDEOS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('audio.enabled')) { ?>
				<li class="<?php echo $highlight == 'audios' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::audios();?>" class="es-navbar__footer-link">
						<i class="fa fa-music"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_AUDIOS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('photos.enabled')) { ?>
				<li class="<?php echo $highlight == 'albums' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::albums();?>"  class="es-navbar__footer-link">
						<i class="far fa-images"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PHOTOS');?>
						</span>
					</a>
				</li>
				<?php } ?>

				<li class="<?php echo $highlight == 'users' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::users();?>" class="es-navbar__footer-link">
						<i class="fa fa-users"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PEOPLE');?>
						</span>
					</a>
				</li>

				<?php if ($this->config->get('polls.enabled')) { ?>
				<li class="<?php echo $highlight == 'polls' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::polls();?>" class="es-navbar__footer-link">
						<i class="fa fa-chart-bar"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_POLLS');?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->isMobile() && $this->my->id) { ?>
				<li class="<?php echo $view == 'conversations' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::conversations();?>" class="es-navbar__footer-link">
						<i class="fa fa-comment"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS');?>
						</span>
					</a>
				</li>

				<li class="<?php echo $view == 'conversations' ? 'is-active' : '';?>" data-es-logout>
					<a href="javascript:void(0);" class="es-navbar__footer-link" data-es-logout-button>
						<i class="fa fa-power-off"></i>
						<span>
							<?php echo JText::_('Logout');?>
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
				</li>
				<?php } ?>
			</ol>

		</div>

	</div>
</div>
