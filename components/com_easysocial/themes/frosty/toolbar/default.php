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
<div class="navbar es-toolbar <?php echo ( ( !$this->my->id && ( $login ) ) || ( $this->my->id && ( $profile ) ) ) ? ' has-sides' : '';?>" data-es-toolbar>
	<div class="es-toolbar-avatar">
		<ul class="g-list-inline es-toolbar__nav">

			<?php if (!$this->my->guest && $profile ){ ?>
			<li class="toolbarItem toolbar-profile" >
				<a href="javascript:void(0);" class="dropdown-toggle_ login-link loginLink"
					data-toolbar-profile
					data-popbox data-popbox-id="es"
					data-popbox-component="popbox--navbar"
					data-popbox-type="navbar-profile"
					data-popbox-toggle="click"
					data-popbox-position="bottom-left"
					data-popbox-target="[data-es-toolbar-profile-dropdown]"
					data-popbox-offset="-1"
				>
					<span class="es-avatar">
					<img alt="<?php echo $this->my->getName();?>" src="<?php echo $this->my->getAvatar();?>">
					</span>
					<b class="caret"></b>
				</a>
				<div class="t-hidden" data-es-toolbar-profile-dropdown>
					<div class="popbox-dropdown">
						<div class="popbox-dropdown__bd">
							<div class="popbox-dropdown-nav">

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
														<a href="<?php echo ESR::profile();?>"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_VIEW_YOUR_PROFILE');?></a>
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

								<?php if ($this->my->hasCommunityAccess()) { ?>
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
															<a href="<?php echo ESR::users();?>">
																<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_BROWSE_USERS');?>
															</a>
														</li>
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
			</li>
			<?php } ?>

			<?php if ($this->my->guest && ($login)) { ?>
			<li class="toolbarItem toolbar-login">
				<a href="javascript:void(0);" class="dropdown-toggle_ login-link loginLink"
					data-popbox data-popbox-id="es" data-popbox-type="navbar-signin" data-popbox-toggle="click" data-popbox-component="popbox--navbar"
					data-popbox-offset="-1" data-popbox-position="bottom-left" data-popbox-target="[data-toolbar-login-dropdown]">
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
	</div>
	<div class="navbar-inner">
		<div class="es-toolbar-wrap">
			<ul class="g-list-inline es-toolbar__nav">

				<?php if ($this->my->id) { ?>
					<?php if ($friends) { ?>
					<li
						data-notifications data-type="friends"
						data-popbox-collision="none"
						data-popbox-position="bottom-left"
						data-popbox-toggle="click"
						data-popbox="module://easysocial/friends/popbox"
						data-popbox-type="navbar-friends"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="-1"
						class="toolbarItem <?php echo $newRequests > 0 ? ' has-new' : '';?>"
						>
						<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS', true);?>" class="loadRequestsButton" href="javascript:void(0);">
							<i class="fa fa-users"></i>
							<span class="visible-phone">"<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS', true);?></span>
							<span data-counter class="label label-notification"><?php echo $newRequests;?></span>
						</a>
					</li>
					<?php } ?>
					<?php if ($conversations) { ?>
					<li
						data-notifications data-type="conversations"
						data-popbox-collision="none"
						data-popbox-position="bottom-left"
						data-popbox-toggle="click"
						data-popbox="module://easysocial/conversations/popbox"
						data-popbox-type="navbar-conversations"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="-1"
						data-popbox-view="<?php echo $view; ?>"
						class="toolbarItem <?php echo $newConversations > 0 ? ' has-new' : '';?>"
						>
						<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_RECENT_CONVERSATIONS' , true );?>" href="javascript:void(0);">
							<i class="fa fa-envelope"></i>
							<span class="visible-phone"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_RECENT_CONVERSATIONS' , true );?></span>
							<span data-counter class="label label-notification"><?php echo $newConversations;?></span>
						</a>
					</li>
					<?php } ?>
					<?php if ($notifications) { ?>
					<li
						data-notifications data-type="system"
						data-user-id=""
						data-autoread=""
						data-popbox-collision="none"
						data-popbox-position="bottom-left"
						data-popbox-toggle="click"
						data-popbox="module://easysocial/notifications/popbox"
						data-popbox-type="navbar-notifications"
						data-popbox-component="popbox--navbar"
						data-popbox-offset="-1"
						class="toolbarItem <?php echo $newNotifications > 0 ? ' has-new' : '';?>"
						>
						<a data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_RECENT_NOTIFICATIONS' , true );?>" href="javascript:void(0);">
							<i class="fa fa-globe"></i>
							<span class="visible-phone"><?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_RECENT_NOTIFICATIONS' , true );?></span>
							<span data-counter class="label label-notification"><?php echo $newNotifications;?>
							</span>
						</a>
					</li>
					<?php } ?>
				<?php } ?>


			</ul>

			<?php if ($this->isMobile()) { ?>
			<ul class="g-list-inline es-toolbar__nav es-toolbar__nav--mobile">
				<?php if ($search) { ?>
				<li>
					<a href="javascript:void(0);" class="o-nav__link es-navbar__icon-link" data-es-toolbar-search-toggle=""><i class="fa fa-search"></i></a>
				</li>
				<?php } ?>

				<li>
					<a class="es-navbar__footer-toggle" href="javascript:void(0);" data-es-toolbar-toggle="">
					<i class="fa fa-bars"></i>
					</a>
				</li>
			</ul>
			<?php } ?>

			<?php if ($search) { ?>
			<div class="o-navbar-search  <?php echo $this->isMobile() ? 't-hidden' : '';?>" data-toolbar-search>
				<i class="fa fa-search"></i>
				<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-navbar__search-form">

					<input type="text" name="q" class="o-navbar-search__query" autocomplete="off" data-nav-search-input placeholder="<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SEARCH', true);?>" />

					<?php if ($filters) { ?>
					<div class="o-navbar-search__filter dropdown pull-right" data-filters>

						<a href="javascript:void(0);" class="dropdown-toggle_" data-bs-toggle="dropdown" data-filter-button>
							<i class="fa fa-cog es-navbar__search-filter-icon"></i>
						</a>

						<ul class="dropdown-menu dropdown-menu-right o-navbar-search__dropdown" data-filters-wrapper>
							<li>
								<div class="o-navbar-search__filter-header">
									<div><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESC');?></div>
								</div>

								<ol class="g-list-inline g-list-inline--delimited">
									<li>
										<a href="javascript:void(0);" data-filter="select"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_SELECT_ALL'); ?></a>
									</li>
									<li data-breadcrumb="|">
										<a href="javascript:void(0);" data-filter="deselect"><?php echo JText::_('COM_EASYSOCIAL_SEARCH_FILTER_DESELECT_ALL'); ?></a>
									</li>
								</ol>
							</li>

							<?php foreach ($filters as $filter) { ?>
							<li class="o-navbar-search__filter-item">
								<div class="o-checkbox">
									<input id="search-type-<?php echo $filter->id;?>" type="checkbox" name="filtertypes[]" value="<?php echo $filter->alias; ?>" data-search-filtertypes />
									<label for="search-type-<?php echo $filter->id;?>">
										<?php echo $filter->displayTitle;?>
									</label>
								</div>
							</li>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>

					<?php echo $this->html('form.itemid', ESR::getItemId('search')); ?>
					<input type="hidden" name="controller" value="search" />
					<input type="hidden" name="task" value="query" />
					<input type="hidden" name="option" value="com_easysocial" />
					<input type="hidden" name="<?php echo FD::token();?>" value="1" />
				</form>
			</div>
			<?php } ?>

		</div>
	</div>
</div>
<div class="es-mainnav-wrap">

	<div class="frosty-mainnav" data-mainnav-content>

		<div class="o-row">
			<ol class="g-list-inline es-mainnav fd-cf">
				<?php if ($dashboard) { ?>
				<li class="<?php echo $view == 'dashboard' ? 'is-active' : '';?> is-home">
					<a href="<?php echo ESR::dashboard();?>" class="es-navbar__footer-link">
						<i class="fa fa-home"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_HOME'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->my->id) { ?>


					<li class="<?php echo $view == 'profile' && !$userId ? 'is-active' : '';?>">
						<a href="<?php echo ESR::profile();?>" class="es-navbar__footer-link">
							<i class="fa fa-user"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE'); ?>
							</span>
						</a>
					</li>
				<?php } ?>

				<?php if ($this->config->get('pages.enabled')) { ?>
				<li class="<?php echo $view == 'pages' || ($view == 'apps' && $type == 'page') ? 'is-active' : '';?>">
					<a href="<?php echo ESR::pages();?>" class="es-navbar__footer-link">
						<i class="fa fa-columns"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PAGES'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('groups.enabled')) { ?>
				<li class="<?php echo $view == 'groups' || ($view == 'apps' && $type == 'group') ? 'is-active' : '';?>">
					<a href="<?php echo ESR::groups();?>" class="es-navbar__footer-link">
						<i class="fa fa-users"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_GROUPS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('events.enabled')) { ?>
				<li class="<?php echo $view == 'events' || ($view == 'apps' && $type == 'event') ? 'is-active' : '';?>">
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
					<li class="<?php echo $view == 'friends' && $layout != 'invite' ? 'is-active' : '';?>">
						<a href="<?php echo ESR::friends();?>" class="es-navbar__footer-link">
							<i class="fa fa-users"></i>
							<span>
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIENDS'); ?>
							</span>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->config->get('followers.enabled')) { ?>
					<li class="<?php echo $view == 'followers' ? 'is-active' : '';?>">
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
				<li class="<?php echo $view == 'videos' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::videos();?>" class="es-navbar__footer-link">
						<i class="fa fa-film"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_VIDEOS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('audio.enabled')) { ?>
				<li class="<?php echo $view == 'audios' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::audios();?>" class="es-navbar__footer-link">
						<i class="fa fa-music"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_AUDIOS'); ?>
						</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($this->config->get('photos.enabled')) { ?>
				<li class="<?php echo $view == 'albums' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::albums();?>"  class="es-navbar__footer-link">
						<i class="far fa-images"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PHOTOS');?>
						</span>
					</a>
				</li>
				<?php } ?>

				<li class="<?php echo $view == 'users' ? 'is-active' : '';?>">
					<a href="<?php echo ESR::users();?>" class="es-navbar__footer-link">
						<i class="fa fa-users"></i>
						<span>
							<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PEOPLE');?>
						</span>
					</a>
				</li>

				<?php if ($this->config->get('polls.enabled')) { ?>
				<li class="<?php echo $view == 'polls' ? 'is-active' : '';?>">
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
				<?php } ?>
			</ol>

		</div>


	</div>
</div>
