<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-snackbar">

	<div class="es-snackbar__cell">
		<?php echo $activeList->get('title');?>
	</div>

	<?php if ($activeList->user_id == $this->my->id) { ?>
	<div class="es-snackbar__cell">
		<div class="es-snackbar__dropdown dropdown_ t-lg-pull-right" data-list-actions data-id="<?php echo $activeList->id;?>">
			<a href="javascript:void(0);" data-bs-toggle="dropdown">
				<?php echo JText::_('COM_ES_MANAGE_PLAYLIST_BUTTON');?>&nbsp; <i class="fa fa-caret-down"></i>
			</a>

			<ul class="dropdown-menu dropdown-menu-right dropdown-menu-lists dropdown-arrow-topright">
				<li>
					<a href="javascript:void(0);" data-add>
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_ADD');?>
					</a>
				</li>

				<li>
					<a href="<?php echo ESR::audios(array('layout' => 'playlistform', 'listId' => $activeList->id));?>">
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_EDIT');?>
					</a>
				</li>
				</li>
				<li>
					<a href="javascript:void(0);" data-delete>
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_DELETE');?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php } ?>

</div>

