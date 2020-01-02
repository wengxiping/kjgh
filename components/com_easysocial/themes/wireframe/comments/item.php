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
<li class="es-comment<?php echo ($comment->getReports() && $this->my->isSiteAdmin()) ? ' has-report' : ''; ?>" data-comment-item data-id="<?php echo $comment->id; ?>" data-child="<?php echo $comment->child; ?>">
	<div class="o-media o-media--top">
		<div class="o-media__image">
			<?php echo $this->html('avatar.' . $author->getType(), $author, 'default', false, false); ?>
		</div>

		<div class="o-media__body">
			<div data-comment-wrapper>
				<div>
					<?php echo $this->html('html.user', $author); ?>
				</div>

				<?php if ($this->my->id && ($this->access->allowed('comments.report') || $comment->canEdit() || ($deleteable || $comment->canDelete()))) { ?>
				<div class="es-comment-actions" data-comment-actions>
					<div class="dropdown_">
						<a href="javascript:void(0);" class="es-comment-actions-toggle" data-bs-toggle="dropdown">
							<i class="i-chevron i-chevron--down"></i>
						</a>

						<ul class="dropdown-menu dropdown-menu-right">
							<?php if ($this->access->allowed('comments.report')) { ?>
							<li>
								<?php echo ES::reports()->getForm('com_easysocial', 'comments', $comment->id, JText::sprintf('COM_EASYSOCIAL_COMMENTS_REPORT_ITEM_TITLE' , $author->getName()), JText::_( 'COM_EASYSOCIAL_COMMENTS_REPORT_ITEM' ), '' , JText::_( 'COM_EASYSOCIAL_COMMENTS_REPORT_TEXT' ) , $comment->getPermalink()); ?>
							</li>
							<?php } ?>

							<?php if ($comment->canEdit()) { ?>
							<li class="divider"></li>
							<li class="btn-comment-edit" data-edit>
								<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_EDIT'); ?></a>
							</li>
							<?php } ?>

							<?php if ($deleteable || $comment->canDelete()) { ?>
							<li class="btn-comment-delete" data-delete>
								<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_DELETE'); ?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>

				<div class="es-comment-content" data-comment-content><?php echo $comment->getComment(); ?></div>

				<?php if ($attachments && $this->config->get('comments.attachments.enabled')) { ?>
				<div class="es-comment-attachments <?php echo count($attachments) > 1 ? ' is-multiple' : ''; ?>">
					<?php foreach ($attachments as $attachment) { ?>
					<div class="es-comment-attachments__item" data-comment-attachment-item>
						<?php if ($attachment->user_id == $this->my->id || $this->my->isSiteAdmin()) { ?>
						<b href="javascript:void(0);" class="es-comment-attachment-remove" data-comment-attachment-delete data-id="<?php echo $attachment->id;?>"></b>
						<?php } ?>

						<a href="<?php echo $attachment->getURI();?>" target="_blank" style="background-image: url('<?php echo $attachment->getURI();?>')"
							data-title="<?php echo $this->html('string.escape', $attachment->name);?>"
							data-lightbox="comment-<?php echo $comment->id;?>-<?php echo $identifier; ?>"
						>
							<i class="fa fa-search"></i>
						</a>
					</div>
					<?php } ?>
				</div>
				<?php } ?>

				<div class="es-comment-item-meta">
					<div class="es-comment-item-meta__item">
						<div class="es-comment-item-like-wrap">
							<div class="es-comment-item-like" data-comment-likes>
								<?php echo $likes->button(); ?>
							</div>
						</div>
					</div>

					<?php echo $likes->html(); ?>

					<div class="es-comment-item-meta__item">
						<div class="es-comment-item-date">
							<?php if ($comment->getPermalink()) { ?>
								<a href="<?php echo $comment->getPermalink(); ?>" title="<?php echo $comment->getDate(false); ?>"><?php echo $comment->getDate(); ?></a>
							<?php } else { ?>
								<?php echo $comment->getDate(); ?>
							<?php } ?>
						</div>
					</div>

					<div class="es-comment-item-meta__item">
						<div class="es-comment-reported">
							<i class="fa fa-exclamation-triangle"></i>
								<span><?php echo JText::_('COM_ES_REPORTED');?></span>
						</div>
					</div>
				</div>
			</div>

			<div data-comments-item-loadReplies class="es-comment-item-loadreply" <?php if( !$comment->hasChild() ) { ?>style="display: none;"<?php } ?>>
				<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_COMMENTS_VIEW_REPLIES');?></a>
			</div>

			<div data-comment-editor style="display: none;"></div>
		</div>
	</div>
</li>
