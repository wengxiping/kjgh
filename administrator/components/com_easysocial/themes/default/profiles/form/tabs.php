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
<ul id="userForm" class="nav nav-tabs nav-tabs-icons" data-es-form-tabs>
	<li class="tabItem<?php echo $activeTab == 'settings' ? ' active' : '';?>">
		<a data-bs-toggle="tab" href="#settings" data-item="settings">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PROFILE_GENERAL');?></span>
		</a>
	</li>
	
	<li class="tabItem<?php echo $activeTab == 'avatars' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#avatars" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="avatars">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_DEFAULT_AVATARS');?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'header' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#header" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="header">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_HEADER');?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'groups' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#groups" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="groups">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_GROUPS');?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'pages' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#pages" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="pages">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PAGES');?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'apps' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#apps" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="apps">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_APPS');?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'privacy' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#privacy" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="privacy">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PRIVACY' );?></span>
		</a>
	</li>

	<li class="tabItem<?php echo $activeTab == 'access' ? ' active' : '';?><?php echo $isNew ? ' inactive' : '';?>">
		<a href="#access" <?php echo !$isNew ? 'data-bs-toggle="tab"' : '';?> data-item="access">
			<span class="help-block"><?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_ACCESS' );?></span>
		</a>
	</li>
</ul>
