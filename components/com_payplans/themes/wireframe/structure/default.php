<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="pp" class="pp-frontend pp-main <?php echo $view . $task . $object . $layout . $suffix; ?> <?php echo $this->isMobile() ? 'is-mobile' : 'is-desktop';?>" data-pp-structure>

	<?php echo $this->render('module', 'pp-general-top'); ?>

	<?php if ($toolbar) { ?>
		<?php echo $this->output('site/toolbar/default'); ?>
	<?php } ?>

	<?php echo $this->render('module', 'pp-general-after-toolbar'); ?>

	<?php echo PP::info()->html(); ?>

	<?php echo $this->render('module', 'pp-general-before-contents'); ?>

	<?php echo $contents; ?>

	<?php echo $this->render('module', 'pp-general-bottom'); ?>

	<div><?php echo $scripts; ?></div>

	<div data-pp-popbox-error style="display:none;"><?php echo JText::_('Unable to load tooltip content.'); ?></div>
</div>
