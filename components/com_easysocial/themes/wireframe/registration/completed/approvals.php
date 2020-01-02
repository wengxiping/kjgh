<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<h1 class="t-text--center"><?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_COMPLETED_WAITING_APPROVAL_HEADING');?></h1>

<div class="es-complete-wrap">
	<span class="es-avatar es-avatar-md es-avatar-rounded">
		<img src="<?php echo $user->getAvatar( SOCIAL_AVATAR_MEDIUM );?>" alt="<?php echo $this->html( 'string.escape' , $user->getName() );?>" />
	</span>

	<form class="es-login-form es-verify-form" action="<?php echo JRoute::_('index.php');?>" method="post">
		<p class="t-text--center">
			<?php echo JText::_('COM_EASYSOCIAL_REGISTRATION_COMPLETED_WAITING_APPROVAL_DESCRIPTION'); ?>
		</p>
	</form>
</div>
