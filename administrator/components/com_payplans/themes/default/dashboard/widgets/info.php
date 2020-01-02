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
<div class="db-panel">
	<div class="db-panel__hd">
		<div class="db-panel__hd-title"><?php echo JText::_('PayPlans Information'); ?></div>
		<div class="db-panel__hd-text"><?php echo JText::sprintf('COM_PP_INSTALLED_VERSION', PP::getLocalVersion()); ?></div>
	</div>

	<div class="db-panel__bd">
		<div class="db-panel-item db-panel-item--borderless is-loading" data-version-check>
			<div class="db-panel-item__indicator">
				<div class="db-panel-item-icon">
					<i class="fas fa-cloud"></i>
				</div>
			</div>
			<div class="db-panel-item__content">
				<div class="db-panel-item__title"><?php echo JText::_('COM_PP_VERSION_CHECKING'); ?></div>
				<div class="db-panel-item__desc"><?php echo JText::_('COM_PP_VERSION_CHECKING_DESC'); ?></div>
			</div>
			<div class="o-loader"></div>
		</div>

		<div class="db-panel-item db-panel-item--borderless t-hidden" data-version-info>
			<div class="db-panel-item__indicator">
				<div class="db-panel-item-icon" data-version-icon>
					<i class="fas fa-download t-hidden" data-version-outdated></i>
					<i class="fas fa-thumbs-up t-hidden" data-version-updated></i>
				</div>
			</div>
			<div class="db-panel-item__content t-hidden" data-version-outdated>
				<div class="db-panel-item__title"><?php echo JText::_('COM_PP_VERSION_REQUIRE_UPDATING'); ?></div>
				<div class="db-panel-item__desc"><?php echo JText::_('COM_PP_LATEST_VERSION'); ?> : <span data-latest-version></span></span></div>
			</div>
			<div class="db-panel-item__content t-hidden" data-version-updated>
				<div class="db-panel-item__title"><?php echo JText::_('COM_PP_VERSION_UP_TO_DATE'); ?></div>
				<div class="db-panel-item__desc"><?php echo JText::_('COM_PP_LATEST_VERSION'); ?> : <span data-latest-version></span></span></div>
			</div>
			<div class="db-panel-item__action t-hidden" data-version-update-button>
				<a href="<?php echo JURI::root();?>administrator/index.php?option=com_payplans&task=system.upgrade" class="btn btn-pp-primary"><?php echo JText::_('COM_PP_UPDATE_NOW'); ?></a>
			</div>
		</div>

		<div class="db-panel-item">
			<div class="db-panel-item__indicator">
				<a href="https://stackideas.com/forums" target="_blank" class="db-panel-item-icon">
					<i class="fas fa-life-ring"></i>
				</a>
			</div>
			<div class="db-panel-item__content">
				<a href="https://stackideas.com/forums" target="_blank" class="db-panel-item__title"><?php echo JText::_('COM_PP_SUPPORT'); ?></a>
				<div class="db-panel-item__desc"><?php echo JText::_('COM_PP_SUPPORT_DESC'); ?></div>
			</div>
		</div>

		<div class="db-panel-item">
			<div class="db-panel-item__indicator">
				<a href="https://stackideas.com/docs/payplans" target="_blank" class="db-panel-item-icon">
					<i class="fas fa-book"></i>	
				</a>
			</div>
			<div class="db-panel-item__content">
				<a href="https://stackideas.com/docs/payplans" target="_blank" class="db-panel-item__title"><?php echo JText::_('COM_PP_DOCUMENTATION'); ?></a>
				<div class="db-panel-item__desc"><?php echo JText::_('COM_PP_DOCUMENTATION_DESC'); ?></div>
			</div>
		</div>
	</div>
</div>