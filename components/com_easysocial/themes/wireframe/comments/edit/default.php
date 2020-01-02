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
<div class="es-story-text">
	<div class="es-comments-form es-story-textbox mentions-textfield" data-story-textbox>
		<div class="mentions">
			<div data-mentions-overlay><?php echo $overlay; ?></div>
			<textarea class="es-story-textfield o-form-control" name="content" data-comment-input data-story-textField data-mentions-textarea data-initial="0"><?php echo $comment; ?></textarea>
		</div>

		<?php if ($this->config->get('comments.smileys')) { ?>
		<b class="es-form-attach">
			<?php echo ES::smileys()->html();?>
		</b>
		<?php } ?>

	</div>
</div>

<div class="t-lg-mt--md">
	<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm t-lg-pull-right" data-save>
		<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_SAVE');?>
	</a>

	<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm t-lg-pull-right t-lg-mr--sm" data-cancel>
		<?php echo JText::_('COM_ES_CANCEL');?>
	</a>

</div>
