<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="<?php echo !$comments && $hideEmpty ? ' t-hidden' : '';?>"
	data-es-comments
	data-group="<?php echo $group; ?>"
	data-element="<?php echo $element; ?>"
	data-verb="<?php echo $verb; ?>"
	data-uid="<?php echo $uid; ?>"
	data-count="<?php echo $count; ?>"
	data-total="<?php echo $total; ?>"
	data-url="<?php echo empty($url) ? '' : $url; ?>"
	data-streamid="<?php echo empty($streamid) ? '' : $streamid; ?>"
	data-timestamp="<?php echo ES::date()->toUnix();?>"
	data-clusterid="<?php echo empty($clusterId) ? '' : $clusterId; ?>"
>
	<?php if ($this->access->allowed('comments.read')) { ?>
		<?php if ($total > $count) { ?>
			<div class="es-comments-control" data-comments-control>
				<div class="es-comments-control__load" data-comments-load class="es-comments-load">
					<a class="es-comments-control__link" data-comments-load-loadMore href="javascript:void(0);">
						<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_LOAD_MORE'); ?>

						<div class="es-comments-control__stats" data-comments-stats><?php echo JText::sprintf('COM_EASYSOCIAL_COMMENTS_LOADED_OF_TOTAL', '<i data-visible>' . $count . '</i>', '<i data-total>' . $total . '</i>'); ?></div>
					</a>
				</div>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if ($comments || ($this->access->allowed('comments.add') && $this->my->id)) { ?>
	<div class="es-comments-wrap">
		<ul class="es-comments" data-comments-list>
		<?php if ($this->access->allowed('comments.read') && $comments) { ?>
			<?php foreach ($comments as $comment) { ?>
				<?php echo $comment->renderHTML(array('deleteable' => $deleteable)); ?>
			<?php } ?>
		<?php } ?>
		</ul>

		<?php if (!$hideForm && $this->access->allowed('comments.add') && $this->my->id) { ?>
		<div class="es-comments-form" data-comments-form>
			<div class="es-form">
				<div class="o-alert o-alert--dismissible o-alert--warning t-hidden" data-comment-error>
					<button type="button" class="o-alert__close" data-comment-error-dismiss>×</button>
					<span data-comment-error-message></span>
				</div>
				<div data-comments-editor>
					<div data-uploader-form>
						<div class="mentions">
							<div data-mentions-overlay data-default=""></div>
							<textarea class="o-form-control" row="1" name="message"
								data-mentions-textarea
								data-default=""
								data-initial="0"
								data-comments-form-input
								placeholder="<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_FORM_PLACEHOLDER' , true);?>"></textarea>

							<b class="es-form-attach">
								<?php if ($this->config->get('comments.attachments.enabled')) { ?>
									<label class="es-input-photo fa fa-camera" for="input-file" data-uploader-browse>&nbsp;</label>
								<?php } ?>
								<?php if ($this->config->get('comments.smileys')) { ?>
									<?php echo ES::smileys()->html();?>
								<?php } ?>
							</b>
						</div>

						<div class="attachments clearfix" data-comment-attachment-queue></div>
					</div>
				</div>
			</div>

			<div class="es-comments-form__footer">
				<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm"  data-comments-form-submit><?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_SUBMIT'); ?>
				</a>
			</div>

		</div>
		<?php } ?>
	</div>
	<?php } ?>

	<div class="t-hidden" data-comment-attachment-template>
		<div class="figure" data-comment-attachment-item>
			<div class="attachment" data-comment-attachment-background>
				<div class="upload-progress">
					<div class="progress progress-striped active">
						<div class="upload-progress-bar progress-bar progress-bar-info" style="width: 0%;" data-comment-attachment-progress-bar></div>
					</div>
				</div>
				<a href="javascript:void(0);" class="attachment-cancel fa fa-times" data-comment-attachment-remove></a>
			</div>
		</div>
	</div>

</div>
