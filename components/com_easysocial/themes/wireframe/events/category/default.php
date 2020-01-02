<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-events-category" data-events-category>

	<?php echo $this->html('header.eventcategory', $category); ?>

	<div class="es-container t-lg-mt--xl">

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/events/category/mobile'); ?>
		<?php } ?>

		<div class="es-content">
			<div class="es-snackbar">
				<h1 class="es-snackbar__title"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_RECENT_UPDATES'); ?></h1>
			</div>

			<div class="es-content-wrap" data-es-event-item-content>
				<?php echo $stream->html();?>
			</div>
		</div>
	</div>
</div>
