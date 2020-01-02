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
<div class="es-actions" data-stream-actions>
	<div class="es-actions__item es-actions__item-action">
		<div class="es-actions-wrapper">
			<ul class="es-actions-list">
				<li>
					<?php echo $likes->button();?>
				</li>

				<li>
					<a href="javascript:void(0);"><?php echo $repost->button();?></a>
				</li>

				<?php if ($lib->hasPrivacy()) { ?>
				<li class="es-action-privacy">
					<?php echo $privacy->form($album->id, SOCIAL_TYPE_ALBUM, $album->uid, 'albums.view', $privacyUseHtml, null, array(), array('iconOnly' => true)); ?>
				</li>
				<?php } ?>

				<li>
					<?php echo $repost->counter(); ?>
				</li>
			</ul>
		</div>
	</div>
	<div class="es-actions__item es-actions__item-stats">
		<?php echo $likes->html(); ?>
	</div>
	<div class="es-actions__item es-actions__item-comment">
		<div class="es-comments-wrapper">
			<?php echo $comments->getHTML(array('hideEmpty' => false));?>
		</div>
	</div>
</div>