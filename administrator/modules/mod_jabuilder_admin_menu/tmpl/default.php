<?php
/**
 * ------------------------------------------------------------------------
 * JA Builder Admin Menu Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
defined('_JEXEC') or die;
?>
<ul id="ja-builder-menu" class="nav <?php echo $disabled ? 'disabled' : ''; ?>">
	<li class="<?php echo $disabled ? 'disabled' : 'dropdown'; ?>">
		<a class="<?php echo $disabled ? 'no-dropdown' : 'dropdown-toggle'; ?>" 
			<?php echo $disabled ? '' : 'data-toggle="dropdown"'; ?>
			href="#">
			<span class="menu-title">JA Builder</span>
			<span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<li><a href="index.php?option=com_jabuilder"><?php echo JText::_('JA_BUILDER'); ?></a></li>
			<li><a href="index.php?option=com_jabuilder&view=page&layout=edit"><?php echo JText::_('JA_ADD'); ?></a></li>
			<li class="divider"><span></span></li>
			<li><a target="_blank" href="https://www.joomlart.com/ja-builder"><?php echo JText::_('JA_SITE'); ?></a></li>
			<li><a target="_blank" href="https://www.joomlart.com/documentation/joomla-templates/ja-builder"><?php echo JText::_('JA_DOCUMENT'); ?></a></li>
		</ul>
	</li>
</ul>