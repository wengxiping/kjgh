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
<div id="es" class="mod-es mod-es-users <?php echo $lib->getSuffix();?>">
	<?php if ($birthdays->today) { ?>
	<div>
		<span class="label label-info"><?php echo JText::_('APP_BIRTHDAYS_TODAY'); ?></span>
		<br />
		<div class="t-lg-mt--sm o-flag-list">
			<?php foreach ($birthdays->today as $item) { ?>
				<?php require(JModuleHelper::getLayoutPath('mod_easysocial_upcoming_birthday', 'default_item')); ?>
			<?php } ?>
		</div>
	</div>
	<?php } ?>

	<?php if ($birthdays->others) { ?>
	<div class="t-lg-mt--md">
		<span class="t-fs--sm"><b><?php echo JText::sprintf('APP_BIRTHDAYS_NEXT_OTHER_DAYS', '7');?></b></span>
		<br />
		<div class="o-flag-list">
			<?php foreach ($birthdays->others as $item) { ?>
				<?php require(JModuleHelper::getLayoutPath('mod_easysocial_upcoming_birthday', 'default_item')); ?>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
