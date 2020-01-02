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

<div class="es-snackbar">
	<h1 class="es-snackbar__title">
	<?php if ($filter == 'mine') { ?>
		<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_YOUR_APPS'); ?>
	<?php } else { ?>
		<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_BROWSE_APPS'); ?>
	<?php } ?>
	</h1>
</div>

<?php if ($apps) { ?>
<div class="<?php echo $this->isMobile() ? 'es-list' : 'es-cards es-cards--3';?>" data-list>
	<?php foreach ($apps as $app) { ?>
		<?php echo $this->html('listing.app', $app, array('style' => $this->isMobile() ? 'listing' : 'card')); ?>
	<?php } ?>
</div>
<?php } ?>

