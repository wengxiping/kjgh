<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
?>

<div class="typo-tools">
	<h6><?php echo JText::_('TPL_TYPO_TOOL_TITLE') ?></h6>
	<ul>
		<li data-fss="Smaller,Small,Medium,Big,Bigger">
			<a class="btn" href="#" title="<?php echo JText::_('TPL_TYPO_TOOL_FONT_SIZE_DECREASE') ?>" data-value="-1" data-target=".article" data-action="nextPrev" data-key="fs"><i class="fa fa-minus"></i></a>
			<strong>
				<b class="Smaller"><?php echo JText::_('TPL_TYPO_TOOL_SMALLER') ?></b>
				<b class="Small"><?php echo JText::_('TPL_TYPO_TOOL_SMALL') ?></b>
				<b class="Medium"><?php echo JText::_('TPL_TYPO_TOOL_MEDIUM') ?></b>
				<b class="Big"><?php echo JText::_('TPL_TYPO_TOOL_BIG') ?></b>
				<b class="Bigger"><?php echo JText::_('TPL_TYPO_TOOL_BIGGER') ?></b>
			</strong>
			<a class="btn" href="#" title="<?php echo JText::_('TPL_TYPO_TOOL_FONT_SIZE_INCREASE') ?>" data-value="+1" data-target=".article" data-action="nextPrev" data-key="fs" data-default="Medium"><i class="fa fa-plus"></i></a>
		</li>
		<li data-fonts="Default,Helvetica,Segoe,Georgia,Times" data-loop="true">
			<a class="btn" href="#" title="<?php echo JText::_('TPL_TYPO_TOOL_FONT_FAMILY_PREV') ?>" data-value="-1" data-target=".article" data-action="nextPrev" data-key="font"><i class="fa fa-chevron-left"></i></a>
			<strong>
				<b class="Default"><?php echo JText::_('TPL_TYPO_TOOL_DEFAULT') ?></b>
				<b class="Helvetica"><?php echo JText::_('TPL_TYPO_TOOL_HELVERICA') ?></b>
				<b class="Segoe"><?php echo JText::_('TPL_TYPO_TOOL_SEGOE') ?></b>
				<b class="Georgia"><?php echo JText::_('TPL_TYPO_TOOL_GEORGIA') ?></b>
				<b class="Times"><?php echo JText::_('TPL_TYPO_TOOL_TIMES') ?></b>
			</strong>
			<a class="btn" href="#" title="<?php echo JText::_('TPL_TYPO_TOOL_FONT_FAMILY_NEXT') ?>" data-value="+1" data-target=".article" data-action="nextPrev" data-key="font" data-default="Default"><i class="fa fa-chevron-right"></i></a>
		</li>
		<li class="toggle-reading">
			<a class="toggle" href="#" title="<?php echo JText::_('TPL_TYPO_TOOL_READING_MODE') ?>" data-action="onOff" data-value="reading-mode" data-default="off" data-target="html" data-key="reading-mode" data-cookie="no"> <i class="fa fa-newspaper-o visible-xs"></i><span class="hidden-xs"><?php echo JText::_('TPL_TYPO_TOOL_READING_MODE') ?><span></a>
		</li>
	</ul>
</div>