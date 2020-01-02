<?php
/** 
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
?>
<div class="accordion-group">
	<div class="accordion-heading opened">
		<div class="accordion-toggle noaccordion">
			<h4><span class="icon-pencil"></span><?php echo JText::_( 'COM_JREALTIME_EVENT_SETTINGS' ); ?></h4>
		</div>
	</div>
	
	<div id="minpagesparams" class="accordion-body collapse in">
		<div class="accordion-inner">
			<div class="control-group">
				<div class="control-label">
					<label for="mintime_mins" class="hasTooltip" title="<?php echo JText::_('COM_JREALTIME_MINTIME_DESC');?>" ><?php echo JText::_('COM_JREALTIME_MINTIME');?></label>
				</div>
				<div class="controls">
					<div class="input-prepend active">
						<span class="add-on"><span class="icon-clock"></span> <?php echo JText::_('COM_JREALTIME_MINTIME_MINUTES' ); ?>:</span>
						<input type="number" min="0" max="60" name="params[mintime_mins]" id="mintime_mins" data-validation="required numbers range" style="width:40px;" value="<?php echo $this->record->params->get('mintime_mins', '00');?>" />
					</div>
					
					<div class="input-prepend active">
						<span class="add-on"><span class="icon-clock"></span> <?php echo JText::_('COM_JREALTIME_MINTIME_SECONDS' ); ?>:</span>
						<input  type="number" min="0" max="60" name="params[mintime_secs]" id="mintime_secs" data-validation="required numbers range" style="width:40px;" value="<?php echo $this->record->params->get('mintime_secs', '00');?>" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
