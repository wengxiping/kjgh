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
<div class="profile-details">
	<div class="profile-title">
		<?php echo $this->html('html.user', $user); ?>
	</div>
		<?php if ($this->config->get('users.layout.lastonline')) { ?>
			<div class="profile-desp">
				<?php if($user->getLastVisitDate() == '0000-00-00 00:00:00') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_USER_NEVER_LOGGED_IN');?>
				<?php } elseif (!$user->isOnline()) { ?>
					<?php echo JText::sprintf('COM_EASYSOCIAL_LAST_LOGGED_IN', $user->getLastVisitDate('lapsed'));?>
				<?php } ?>
			</div>
		<?php } ?>
	<input type="hidden" data-user-id="<?php echo $user->id; ?>" />
</div>

<div class="popbox-cover">
	<div style="background-image: url('<?php echo $user->getCover();?>'); background-position: <?php echo $user->getCoverData() ? $user->getCoverData()->getPosition() : '50% 50%';?>; background-size: cover" class="es-photo-scaled es-photo-wrap"></div>
</div>

<?php echo $this->html('avatar.user', $user, 'lg', false); ?>

<?php if ($user->hasCommunityAccess()) { ?>
<div class="popbox-info">
	<ul class="popbox-items">
		<?php if ($this->config->get('friends.enabled')) { ?>
		<li>
			<div class="popbox-item-info">
				<a href="<?php echo ESR::friends(array('userid' => $user->getAlias()));?>">
					<div class="popbox-item-text">
						<?php echo JText::_('COM_EASYSOCIAL_FRIENDS');?>
					</div>
					<div class="popbox-item-total"><?php echo $user->getTotalFriends();?></div>
				</a>
			</div>
		</li>
		<?php } ?>

		<?php if ($this->config->get('photos.enabled')) { ?>
		<li>
			<div class="popbox-item-info">
				<a href="<?php echo ESR::albums(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>">
					<div class="popbox-item-text"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ALBUMS');?></div>
					<div class="popbox-item-total">
						<?php echo $user->getTotalAlbums();?>
					</div>
				</a>
			</div>
		</li>
		<?php } ?>

		<?php if ($this->config->get('followers.enabled')) { ?>
		<li>
			<div class="popbox-item-info">
				<a href="<?php echo ESR::followers(array('userid' => $user->getAlias()));?>">
					<div class="popbox-item-text">
						<?php echo JText::_('COM_EASYSOCIAL_FOLLOWERS');?>
					</div>
					<div class="popbox-item-total"><?php echo $user->getTotalFollowers();?></div>
				</a>
			</div>
		</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>

<div class="popbox-footer">
	<div class="pull-right">
		<?php if ($user->hasCommunityAccess() && !$user->isViewer() && !$user->isBlockedBy($this->my->id)) { ?>
		<div class="o-btn-group">
			<?php echo $this->html('user.friends', $user); ?>
			<?php echo $this->html('user.subscribe', $user); ?>

			<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER)) { ?>
				<?php echo $this->html('user.conversation', $user); ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
