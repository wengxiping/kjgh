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
<div class="es-container">
	<div class="es-content">
		<div data-notifications-list class="es-noti-list-wrap es-island <?php echo !$items ? ' is-empty' : '';?>">
			<?php if ($items){ ?>
				<div class="es-noti-hd">
					<div class="o-row t-lg-mb--lg">
						<div class="o-col">
							<div class="es-noti-hd__title">
								<?php echo JText::_('COM_EASYSOCIAL_NOTIFICATIONS_TITLE'); ?>
								<?php if ($total > 0) { ?>
								<span class="es-noti-hd__badge"><?php echo $total; ?></span>
								<?php } ?>
							</div>
						</div>

						<div class="o-col">
							<div class="es-noti-hd__action">
								<button class="btn btn-es-primary-o btn-sm" href="javascript:void(0);" data-notification-all-read>
									<?php echo JText::_('COM_EASYSOCIAL_MARK_ALL_READ');?>
								</button>
								<button class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-notification-all-clear>
									<?php echo JText::_('COM_EASYSOCIAL_CLEAR_ITEMS');?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<?php echo $this->loadTemplate('site/notifications/default/item', array('items' => $items)); ?>

				<a href="javascript:void(0);" class="btn btn-es-default-o btn-loadmore btn-sm btn-block"
				   data-notification-loadmore-btn
				   data-startlimit="<?php echo $limit;?>"
				   <?php echo !$pagination ? 'style="display:none;"' : ''; ?>
				><?php echo JText::_('COM_EASYSOCIAL_NOTIFICATIONS_LOAD_MORE');?></a>

			<?php } ?>

			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_NOTIFICATIONS_NO_NOTIFICATIONS', 'fa-bell'); ?>
		</div>		
	</div>
</div>

