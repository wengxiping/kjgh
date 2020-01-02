<?php 
/** 
 * @package JREALTIMEANALYTICS::OVERVIEW::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage overlook
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td class="right">
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-color-palette"></span> <?php echo JText::_('COM_JREALTIME_GRAPH_THEME' ); ?>:</span>
					<?php echo $this->lists['graphTheme'];?> 
				</div>
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-health"></span> <?php echo JText::_('COM_JREALTIME_GRAPH_TYPE' ); ?>:</span>
					<?php echo $this->lists['graphType'];?> 
				</div>
				<div class="clearer"></div>
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_YEAR' ); ?>:</span>
					<?php echo $this->lists['statsYear'];?> 
				</div>
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_MONTH_PERIOD' ); ?>:</span>
					<?php echo $this->lists['statsMonth'];?> 
				</div>
			</td>
		</tr>
	</table>

	<div>
		<img src="<?php echo JUri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_overview.png' . $this->nocache;?>" />
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="overlook.display" />
</form>