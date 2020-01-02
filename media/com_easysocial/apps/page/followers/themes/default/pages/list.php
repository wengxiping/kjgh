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
<div class="es-list" data-result>
	<?php if ($users) { ?>
		<?php foreach ($users as $user) { ?>
		<div class="es-list__item">

			<div class="es-list-item es-island is-featured" data-item data-id="<?php echo $user->id;?>" data-return="<?php echo $returnUrl;?>">

				<div class="es-list-item__media">
					<?php echo $this->html('avatar.user', $user, 'md', false, true); ?>
				</div>

				<div class="es-list-item__context">
					<div class="es-list-item__hd">
						<div class="es-list-item__content">

							<div class="es-list-item__title">
								<?php echo $this->html('html.user', $user); ?>
							</div>

							<div class="es-list-item__meta">
								<ol class="g-list-inline g-list-inline--delimited">
									<li>
										<?php if ($page->isOwner($user->id)) { ?>
										<span class="o-label o-label--danger-o"><?php echo JText::_('APP_PAGE_FOLLOWERS_OWNER'); ?></span>
										<?php } ?>

										<?php if ($page->isAdmin($user->id) && !$page->isOwner($user->id)) { ?>
										<span class="o-label o-label--success-o"><?php echo JText::_('APP_PAGE_FOLLOWERS_ADMIN'); ?></span>
										<?php } ?>

										<?php if (!$page->isAdmin($user->id) && $user->isSiteAdmin()) { ?>
										<span class="o-label o-label--info-o"><?php echo JText::_('APP_PAGE_FOLLOWERS_MODERATOR'); ?></span>
										<?php } ?>

										<?php if ($page->isMember($user->id) && !$page->isAdmin($user->id) && !$page->isOwner($user->id) && !$user->isSiteAdmin()) { ?>
										<span class="o-label o-label--clean-o"><?php echo JText::_('APP_PAGE_FOLLOWERS_FOLLOWER'); ?></span>
										<?php } ?>

										<?php if ($page->isPendingMember($user->id)) { ?>
										<span class="o-label o-label--warning-o label-pending"><?php echo JText::_('APP_PAGE_FOLLOWERS_PENDING');?></span>
										<?php } ?>

										<?php if ($page->isPendingInvitationApproval($user->id)) { ?>
										<span class="o-label o-label--default-o label-pending-invitation"><?php echo JText::_('APP_PAGE_FOLLOWERS_INVITED');?></span>
										<?php } ?>
									</li>

									<?php if ($page->isPendingInvitationApproval($user->id)) { ?>
									<li data-breadcrumb="&#183;">
										<?php echo JText::sprintf('APP_PAGE_FOLLOWERS_INVITED_BY', $this->html('html.user', $page->getInvitor($user->id)->id, true), $page->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
									</li>
									<?php } ?>

									<?php if ($page->isMember($user->id) && !$page->isInvited($user->id)) { ?>
									<li data-breadcrumb="&#183;">
										<?php echo JText::sprintf('APP_PAGE_FOLLOWERS_LIKED', $page->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
									</li>
									<?php } ?>

									<?php if ($page->isPendingMember($user->id)) { ?>
									<li data-breadcrumb="&#183;">
										<?php echo JText::sprintf('APP_PAGE_FOLLOWERS_REQUESTED', $page->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
									</li>
									<?php } ?>
								</ol>
							</div>
						</div>
						<div class="es-list-item__state">
							<div class="es-label-state es-label-state--featured" data-original-title="Featured" data-es-provide="tooltip">
								<i class="es-label-state__icon"></i>
							</div>
						</div>

						<div class="es-list-item__action">
							<div role="toolbar" class="btn-toolbar t-lg-mt--sm">
								<?php echo $this->html('user.conversation', $user); ?>

								<?php echo $this->html('user.clusterActions', $user, $page); ?>
							</div>
						</div>
					</div>

				</div>

			</div>

		</div>
		<?php } ?>
	<?php } ?>

</div>
<?php echo $this->html('html.emptyBlock', $emptyText, 'fa-users'); ?>

<?php if ($pagination) { ?>
<div class="es-pagination-footer">
	<?php echo $pagination->getListFooter('site');?>
</div>
<?php } ?>
