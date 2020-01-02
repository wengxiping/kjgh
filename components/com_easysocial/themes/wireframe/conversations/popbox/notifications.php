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
<div class="popbox-dropdown">
	<div class="popbox-dropdown__hd">
		<div class="o-flag o-flag--rev">
			<div class="o-flag__body">
				<div class="popbox-dropdown__title t-lg-pull-left"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS'); ?></div>

				<div class="t-lg-pull-right">
					<ol class="g-list-inline g-list-inline--dashed">
						<?php if ($this->access->allowed('conversations.create')) { ?>
						<li>
							<a href="<?php echo ESR::conversations(array('layout' => 'compose'));?>" class="popbox-dropdown__note pull-left">
								<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS_COMPOSE');?>
							</a>
						</li>
						<?php } ?>

						<li>
							<a href="<?php echo ESR::conversations();?>" class="popbox-dropdown__note pull-right">
								<?php echo JText::_('COM_ES_VIEW_ALL'); ?>
							</a>
						</li>
					</ol>
				</div>
			</div>
		</div>
	</div>


	<div class="popbox-dropdown__bd">
		<?php if ($conversations) { ?>
			<div class="popbox-dropdown-nav">
			<?php foreach ($conversations as $conversation) { ?>
				<div class="popbox-dropdown-nav__item <?php echo $conversation->isNew() ? 'is-unread' : ''; ?>">
					<?php if (ES::conversekit()->exists($view)) { ?>
					<a href="javascript:void(0);" class="popbox-dropdown-nav__link" data-ck-chat data-conversation-id="<?php echo $conversation->id;?>">
					<?php } else { ?>
					<a href="<?php echo $conversation->getPermalink();?>" class="popbox-dropdown-nav__link">
					<?php } ?>
						<div class="o-flag">
							<div class="o-flag__image o-flag--top">
								<?php echo $conversation->getAvatar();?>
							</div>

							<div class="o-flag__body">
								<div class="popbox-dropdown-nav__post">
									<?php if ($conversation->getLastMessage()) { ?>

										<div class="object-title">
											<b><?php echo $conversation->getTitle(); ?></b>
										</div>

										<div class="object-content t-fs--sm">
											<?php echo $this->loadTemplate('site/conversations/popbox/' . $conversation->getLastMessageType(), array('conversation' => $conversation)); ?>
										</div>

										<div class="object-timestamp t-text--muted t-fs--sm">
											<i class="far fa-clock"></i>&nbsp; <?php echo $conversation->getLastRepliedDate()->toLapsed();?>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</a>
				</div>
			<?php } ?>
			</div>
		<?php } else { ?>
		<div class="t-text--muted is-empty">
			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_TOOLBAR_CONVERSATIONS_NO_CONVERSATIONS_YET', 'fa-envelope', false, false); ?>
		</div>
		<?php } ?>
	</div>
</div>

