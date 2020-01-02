<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php foreach ($users as $user) { ?>
	<?php echo $this->render('module', 'es-users-between-user'); ?>

	<?php echo $this->html('listing.user', $user); ?>
<?php } ?>

<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_USERS_NO_USERS_HERE', 'fa-users'); ?>

<?php if ($pagination) { ?>
<div class="es-pagination-footer" data-es-users-pagination>
	<?php echo $pagination->getListFooter('site');?>
</div>
<?php } ?>
