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
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<?php if ($this->lib->config->get('users.profile.sidebar') != 'hidden') { ?>
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module', 'es-profile-about-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php echo $this->lib->output('site/profile/about/stats', array('user' => $user)); ?>

		<?php echo $this->lib->render('module', 'es-profile-about-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
	<?php } ?>
</div>
