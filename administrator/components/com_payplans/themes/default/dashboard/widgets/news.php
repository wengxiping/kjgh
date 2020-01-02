<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Payplans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="db-panel">
	<div class="db-panel__hd">
		<div class="db-panel__hd-title"><?php echo JText::_('COM_PP_DASHBOARD_NEWS_HEADING'); ?></div>
		<div class="db-panel__hd-text"><?php echo JText::_('COM_PP_DASHBOARD_NEWS_SUBHEADING'); ?></div>
	</div>
	<div class="db-panel__bd">
		<div class="db-panel-news is-loading" data-dashboard-news>
			<div class="hide" data-news-templates>
				<a href="javascript:void(0);" class="db-panel-news__item" target="_blank" data-news-permalink>
					<img class="db-panel-news__image" data-news-image style="width:120px;float:right;padding: 3px;margin: 10px 20px;border:1px solid #d7d7d7;" />
					<div class="db-panel-news__title" data-news-title></div>
					<div class="db-panel-news__meta" data-news-meta></div>
					<div class="db-panel-news__desc" data-news-content></div>
				</a>
				<hr style="clear:both;" />
			</div>
			<div class="o-loader"></div>
		</div>
	</div>
</div>