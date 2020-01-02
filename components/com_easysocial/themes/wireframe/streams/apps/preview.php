<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-apps">
	<div class="o-avatar o-avatar--lg o-avatar--text o-avatar--bg-2 es-app-item__avatar">
		<?php echo $app->getTextAvatar();?>
	</div>

	<div class="es-stream-embed__apps-context">
	    <div class="es-stream-embed__apps-title">
	    	<?php echo $app->_('title'); ?> <span><?php echo $app->getMeta()->version;?></span>
	    </div>
	    <b><?php echo $app->getUserDesc();?></b>
	</div>

</div>