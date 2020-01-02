<?php 
//namespace modules\mod_jrealtimeanalytics\tmpl
/**
 * @package JREALTIMEANALYTICS::modules
 * @subpackage mod_jrealtimeanalytics
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;
?>
<div id="jes_mod">
	<?php 
		// Manage error conditions
		if(is_array($statsData) && $cParams->get('daily_stats', true)):
	?>
			<ul class="list-group">
				<li class="list-group-item title">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_STATS_OF_DAY', $startPeriod);?>
				</li>
				<li class="list-group-item">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_TOTAL_VISITED_PAGE', $statsData['total_visited_pages']);?>
				</li>
				<li class="list-group-item">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_TOTAL_VISITORS', $statsData['total_visitors']);?>
				</li>
				<li class="list-group-item">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_MEDIUM_VISITTIME', $statsData['medium_visit_time']);?>
				</li>
				<li class="list-group-item">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_MEDIUM_PAGE_PERUSER', $statsData['medium_page_per_user']);?>
				</li>
				
				<?php if($cParams->get('visitors_counter', 0) && !$cParams->get('visitors_counter_position', 0)):?>
					<li class="list-group-item counter">
						<?php echo JText::sprintf('COM_JREALTIME_MOD_VISITORS_COUNTER', number_format($statsData['visitors_counter'], 0 , ',', '.'));?>
					</li>
				<?php endif;?>
			</ul>
	<?php 
		elseif(!is_array($statsData)):
	?>
			<ul class="list-group">
				<li class="list-group-item title">
					<?php echo JText::sprintf('COM_JREALTIME_MOD_ANERROR_OCCURRED', $statsData);?>
				</li>
			</ul>
	<?php
		endif;
		if($cParams->get('realtime_stats')):
	?>
			<ul class="list-group">
				<li class="list-group-item title">
					<?php echo JText::_('COM_JREALTIME_MOD_STATS_REALTIME');?>
				</li>
				<li class="list-group-item">
					<?php echo JText::_('COM_JREALTIME_MOD_USERS_ONPAGE');?><span class="badge" data-bind="users_onpage"></span>
				</li>
				<li class="list-group-item">
					<?php echo JText::_('COM_JREALTIME_MOD_VISITORS');?><span class="badge" data-bind="visitors"></span>
				</li>
				<li class="list-group-item">
					<?php echo JText::_('COM_JREALTIME_MOD_USERS_LOGGED');?><span class="badge" data-bind="users_logged"></span>
				</li>
				<li class="list-group-item">
					<?php echo JText::_('COM_JREALTIME_MOD_USERS_TOTAL');?><span class="badge" data-bind="users_total"></span>
				</li>
				
				<?php if($cParams->get('visitors_counter', 0) && $cParams->get('visitors_counter_position', 0)):?>
					<li class="list-group-item counter">
						<?php echo JText::sprintf('COM_JREALTIME_MOD_VISITORS_COUNTER', number_format($statsData['visitors_counter'], 0 , ',', '.'));?>
					</li>
				<?php endif;?>
			</ul>
	<?php
		endif;
		if($cParams->get('visualmap_stats')):
	?>
		<div class="module-mapcharts"></div>
	<?php
		endif;
	?>
</div>