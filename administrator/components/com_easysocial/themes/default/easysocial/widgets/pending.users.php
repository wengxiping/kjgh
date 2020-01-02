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
?>
<?php if ($pendingUsers) { ?>
<!-- <h4><?php echo JText::_( 'COM_EASYSOCIAL_WIDGET_TITLE_PENDING_USERS' );?></h4> -->
<!-- <hr /> -->

<ul class="g-list-unstyled es-items-list" data-widget-pending-users>
	<?php if( $pendingUsers ){ ?>
		<?php foreach( $pendingUsers as $user ){ ?>
			<li data-pending-item data-id="<?php echo $user->id;?>">
				<div class="es-media">
					<div class="es-media-object">
						<a href="<?php echo $user->getPermalink();?>" class="o-avatar" target="_blank">
							<img src="<?php echo $user->getAvatar( SOCIAL_AVATAR_LARGE );?>"/>
						</a>
					</div>
					<div class="es-media-body">
						<div class="o-row">
							<div class="o-col--8">
								<div class=""><b><?php echo $user->getName();?> (<?php echo $user->username; ?>)</b></div>
								<i class="t-fs--sm"><?php echo JText::_('COM_EASYSOCIAL_REGISTERED');?> <?php echo $user->getRegistrationDate()->toLapsed();?></i>
							</div>
							<div class="o-col--4">
								<a href="javascript:void(0);" class="btn btn-sm btn-es-success-o" data-pending-approve>
									<?php echo JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'); ?>
								</a>
								<a href="javascript:void(0);" class="btn btn-sm btn-es-danger-o" data-pending-reject>
									<?php echo JText::_( 'COM_EASYSOCIAL_DECLINE_BUTTON' ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</li>

		<?php } ?>
	<?php } ?>
</ul>
<?php } ?>
